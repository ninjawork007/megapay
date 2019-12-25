<?php

namespace App\Http\Controllers\Users;

use App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Fee;
use App\Models\FeesLimit;
use App\Models\Preference;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;

class MoneyTransferController extends Controller
{
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    //Send Money - check Processed By
    public function checkProcessedBy()
    {
        $processedBy = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        // dd($processedBy);
        return response()->json([
            'status'      => true,
            'processedBy' => $processedBy,
        ]);
    }

    //Send Money - Email/Phone validation
    public function transferEmailOrPhoneValidate(Request $request)
    {
        // dd($request->all());

        $phoneRegex = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $request->receiver);
        if ($phoneRegex)
        {
            $user = User::where(['id' => auth()->user()->id])->first(['formattedPhone']);
            // dd($user);
            if (empty($user->formattedPhone))
            {
                return response()->json([
                    'status'  => 404,
                    'message' => __("Please set your phone number first!"),
                ]);
            }
        }
        else
        {
            if ($request->receiver == auth()->user()->email || $request->receiver == auth()->user()->formattedPhone)
            {
                return response()->json([
                    'status'  => true,
                    'message' => __("You Cannot Send Money To Yourself!"),
                ]);
            }
        }
    }

    public function create(Request $request)
    {
        //set the session for validating the action
        setActionSession();

        if (!$_POST)
        {
            $data['menu']    = 'send_receive';
            $data['submenu'] = 'send';

            /*Check Whether Currency is Activated in feesLimit*/
            $data['walletList'] = Wallet::where(['user_id' => auth()->user()->id])
                ->whereHas('active_currency', function ($q)
            {
                    $q->whereHas('fees_limit', function ($query)
                {
                        $query->where('transaction_type_id', Transferred)->where('has_transaction', 'Yes')->select('currency_id', 'has_transaction');
                    });
                })
                ->with(['active_currency:id,code', 'active_currency.fees_limit:id,currency_id']) //Optimized by parvez - for pm v2.3
                ->get(['id', 'currency_id', 'is_default']);
            // dd($data['walletList']);

            return view('user_dashboard.moneytransfer.create', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());

            $rules = array(
                'amount'   => 'required|numeric',
                'receiver' => 'required',
                'note'     => 'required',
            );

            $fieldNames = array(
                'amount'   => __("Amount"),
                'receiver' => __("Recipient"),
                'note'     => __("Note"),
            );

            //instantiating message array
            $messages = [
                //
            ];

            // backend Validation - starts
            if ($request->sendMoneyProcessedBy == 'email')
            {
                //check if valid email
                $rules['receiver'] = 'required|email';
            }
            elseif ($request->sendMoneyProcessedBy == 'phone')
            {
                //check if valid phone
                $myStr = explode('+', $request->receiver);
                if ($request->receiver[0] != "+" || !is_numeric($myStr[1]))
                {
                    return back()->withErrors(__("Please enter valid phone (ex: +12015550123)"))->withInput();
                }
            }
            elseif ($request->sendMoneyProcessedBy == 'email_or_phone')
            {
                $myStr = explode('+', $request->receiver);
                //valid number is not entered
                if ($request->receiver[0] != "+" || !is_numeric($myStr[1]))
                {
                    //check if valid email or phone
                    $rules['receiver'] = 'required|email';
                    $messages          = [
                        'email' => __("Please enter valid email (ex: user@gmail.com) or phone (ex: +12015550123)"),
                    ];
                }
            }

            //Own Email or phone validation
            $transferEmailOrPhoneValidate = $this->transferEmailOrPhoneValidate($request);
            if ($transferEmailOrPhoneValidate)
            {
                if ($transferEmailOrPhoneValidate->getData()->status == true || $transferEmailOrPhoneValidate->getData()->status == 404)
                {
                    return back()->withErrors(__($transferEmailOrPhoneValidate->getData()->message))->withInput();
                }
            }

            //Amount Limit Check validation
            $request['wallet_id']           = $request->wallet;
            $request['transaction_type_id'] = Transferred;
            $amountLimitCheck               = $this->amountLimitCheck($request);
            // dd($amountLimitCheck->getData());
            if ($amountLimitCheck->getData()->success->status == 200)
            {
                if ($amountLimitCheck->getData()->success->totalAmount > $amountLimitCheck->getData()->success->balance)
                {
                    return back()->withErrors(__("Not have enough balance !"))->withInput();
                }
            }
            else
            {
                return back()->withErrors(__($amountLimitCheck->getData()->success->message))->withInput();
            }
            //backend validation ends

            $validator = Validator::make($request->all(), $rules, $messages);
            $validator->setAttributeNames($fieldNames);
            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                //Validation passed
                $wallet                          = Wallet::with(['currency:id,symbol'])->where(['id' => $request->wallet, 'user_id' => auth()->user()->id])->first(['currency_id', 'balance']);
                $request['currency_id']          = $wallet->currency->id;
                $request['currSymbol']           = $wallet->currency->symbol;
                $request['totalAmount']          = $request->amount + $request->fee;
                $request['sendMoneyProcessedBy'] = $request->sendMoneyProcessedBy;
                session(['transInfo' => $request->all()]);
                $data['transInfo'] = $request->all();
            }
            return view('user_dashboard.moneytransfer.confirmation', $data);
        }
    }

    //Send Money - Amount Limit Check
    public function amountLimitCheck(Request $request)
    {
        $amount      = $request->amount;
        $wallet_id   = $request->wallet_id;
        $user_id     = Auth::user()->id;
        $wallet      = Wallet::where(['id' => $wallet_id, 'user_id' => $user_id])->first(['currency_id', 'balance']);
        $currency_id = $wallet->currency_id;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $currency_id])->first(['max_limit', 'min_limit', 'charge_percentage', 'charge_fixed']);

        //Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['message'] = __('Minimum amount ') . $feesDetails->min_limit;
                $success['status']  = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        else
        {
            if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
            {
                $success['message'] = __('Minimum amount ') . $feesDetails->min_limit . __(' and Maximum amount ') . $feesDetails->max_limit;
                $success['status']  = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        //Code for Amount Limit ends here

        //Code for Fees Limit Starts here
        if (empty($feesDetails))
        {
            $feesPercentage            = 0;
            $feesFixed                 = 0;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesPercentage;
            $success['fFees']          = $feesFixed;
            $success['pFeesHtml']      = formatNumber($feesPercentage);
            $success['fFeesHtml']      = formatNumber($feesFixed);
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = $wallet->balance;
        }
        else
        {
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage);
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = $wallet->balance;
        }
        //Code for Fees Limit Ends here
        return response()->json(['success' => $success]);
    }

    //Send Money - Confirm
    public function sendMoneyConfirm(Request $request)
    {

        //initializing session
        actionSessionCheck();

        try
        {
            \DB::beginTransaction();

            $sessionValue            = Session::get('transInfo');
            $total_with_fee          = $sessionValue['amount'] + $sessionValue['fee'];
            $feesDetails             = FeesLimit::where(['transaction_type_id' => Transferred, 'currency_id' => $sessionValue['currency_id']])->first(['charge_percentage', 'charge_fixed']);
            $p_calc                  = $sessionValue['amount'] * (@$feesDetails->charge_percentage / 100);
            $processedBy             = $sessionValue['sendMoneyProcessedBy'];
            $request_wallet_currency = $sessionValue['currency_id'];
            $unique_code             = unique_code();
            $emailFilterValidate     = filter_var(trim($sessionValue['receiver']), FILTER_VALIDATE_EMAIL);
            $phoneRegex              = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
                trim($sessionValue['receiver']));

            if ($emailFilterValidate)
            {
                $userInfo = User::where(['email' => trim($sessionValue['receiver'])])->first();
            }
            elseif ($phoneRegex)
            {
                $userInfo = User::where(['formattedPhone' => trim($sessionValue['receiver'])])->first(); // fetching receiver id
            }

            //Save to transfer table - starts
            $transfer              = new Transfer();
            $transfer->sender_id   = Auth::user()->id;
            $transfer->receiver_id = isset($userInfo) ? $userInfo->id : null;
            $transfer->currency_id = $request_wallet_currency;
            $transfer->uuid        = $unique_code;
            $transfer->fee         = $sessionValue['fee'];
            $transfer->amount      = $sessionValue['amount'];
            $transfer->note        = $sessionValue['note'];
            if ($emailFilterValidate)
            {
                $transfer->email = $sessionValue['receiver'];
            }
            elseif ($phoneRegex)
            {
                // dd($sessionValue['receiver']);
                $transfer->phone = $sessionValue['receiver'];
            }

            if (isset($transfer->receiver_id))
            {
                $transfer->status = 'Success';
            }
            else
            {
                $transfer->status = 'Pending';
            }
            // dd($transfer);
            $transfer->save();
            //Save to transfer table - ends

            //Sender Transaction save starts here
            $sender_t                           = new Transaction();
            $sender_t->currency_id              = $request_wallet_currency;
            $sender_t->user_id                  = Auth::user()->id;
            $sender_t->end_user_id              = isset($userInfo) ? $userInfo->id : null;
            $sender_t->uuid                     = $unique_code;
            $sender_t->transaction_reference_id = $transfer->id;
            $sender_t->transaction_type_id      = Transferred;
            $sender_t->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
            if ($emailFilterValidate)
            {
                $sender_t->email = $sessionValue['receiver'];
            }
            elseif ($phoneRegex)
            {
                $sender_t->phone = $sessionValue['receiver'];
            }
            $sender_t->subtotal          = $sessionValue['amount'];
            $sender_t->percentage        = @$feesDetails->charge_percentage ? @$feesDetails->charge_percentage : 0;
            $sender_t->charge_percentage = @$feesDetails->charge_percentage ? $p_calc : 0;
            $sender_t->charge_fixed      = @$feesDetails->charge_fixed ? @$feesDetails->charge_fixed : 0;
            $sender_t->total             = '-' . ($total_with_fee);
            $sender_t->note              = $sessionValue['note'];
            $sender_t->status            = $transfer->status;
            $sender_t->save();
            //Sender Transaction save ends here

            //Receiver Transactions Save starts here
            $receiver_t                           = new Transaction();
            $receiver_t->currency_id              = $request_wallet_currency;
            $receiver_t->user_id                  = isset($userInfo) ? $userInfo->id : null;
            $receiver_t->end_user_id              = Auth::user()->id;
            $receiver_t->uuid                     = $unique_code;
            $receiver_t->transaction_reference_id = $transfer->id;
            $receiver_t->transaction_type_id      = Received;
            $receiver_t->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
            if ($emailFilterValidate)
            {
                $receiver_t->email = $sessionValue['receiver'];
            }
            elseif ($phoneRegex)
            {
                $receiver_t->phone = $sessionValue['receiver'];
            }
            $receiver_t->subtotal          = $sessionValue['amount'];
            $receiver_t->percentage        = 0;
            $receiver_t->charge_percentage = 0;
            $receiver_t->charge_fixed      = 0;
            $receiver_t->total             = $sessionValue['amount'];
            $receiver_t->note              = $sessionValue['note'];
            $receiver_t->status            = $transfer->status;
            $receiver_t->save();
            //Receiver Transaction Save ends here

            //Updating Sender Wallet Balance
            $senderWallet         = Wallet::where(['id' => $sessionValue['wallet']])->first(['id','balance']);
            $senderWallet->balance = $senderWallet->balance - $total_with_fee;
            $senderWallet->save();

            //Creating or Updating Receiver Wallet Balance starts here
            if (!empty($transfer->receiver_id) && isset($userInfo))
            {
                $receiver_wallet = Wallet::where(['user_id' => $userInfo->id, 'currency_id' => $request_wallet_currency])->first(['id', 'balance']);
                if (empty($receiver_wallet))
                {
                    $wallet              = new Wallet();
                    $wallet->user_id     = isset($userInfo) ? $userInfo->id : null;
                    $wallet->currency_id = $request_wallet_currency;
                    $wallet->is_default  = 'No';
                    $wallet->balance     = $sessionValue['amount'];
                    $wallet->save();
                }
                else
                {
                    $receiver_wallet->balance = ($receiver_wallet->balance + $sessionValue['amount']);
                    $receiver_wallet->save();
                }
            }
            //Creating or Updating Receiver Wallet Balance ends here

            if ($emailFilterValidate && $processedBy == "email")
            {
                /**
                 * Mail To Sender
                 */
                //if other language's subject and body not set, get en sub and body for mail
                $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                $sender_info           = EmailTemplate::where(['temp_id' => 1, 'language_id' => Session::get('default_language'), 'type' => 'email'])->select('subject', 'body')->first();
                if (!empty($sender_info->subject) && !empty($sender_info->body))
                {
                    $sender_subject = $sender_info->subject;
                    $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $sender_info->body);
                }
                else
                {
                    $sender_subject = $englishSenderLanginfo->subject;
                    $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $englishSenderLanginfo->body);
                }
                $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $sender_msg);

                $sender_msg = str_replace('{uuid}', $unique_code, $sender_msg);
                $sender_msg = str_replace('{receiver_id}', isset($userInfo) ? $userInfo->first_name . ' ' . $userInfo->last_name : $sessionValue['receiver'], $sender_msg);
                $sender_msg = str_replace('{fee}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['percentage_fee'] + $sessionValue['fixed_fee'])), $sender_msg);
                $sender_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $sender_msg);
                $sender_msg = str_replace('{soft_name}', Session::get('name'), $sender_msg);

                if (checkAppMailEnvironment())
                {
                    try
                    {
                        $this->email->sendEmail($transfer->sender->email, $sender_subject, $sender_msg);
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        clearActionSession();
                        $this->helper->one_time_message('error', $e->getMessage());
                        return redirect('moneytransfer');
                    }
                }

                /**
                 * Mail To Receiver
                 */
                //if other language's subject and body not set, get en sub and body for mail
                $englishLangReceiverinfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en'])->select('subject', 'body')->first();
                $receiver_info           = EmailTemplate::where([
                    'temp_id'     => 2,
                    'language_id' => Session::get('default_language'),
                ])->select('subject', 'body')->first();

                if (isset($userInfo))
                {
                    if (!empty($receiver_info->subject) && !empty($receiver_info->body))
                    {
                        $receiver_subject = $receiver_info->subject;
                        $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $receiver_info->body); //
                    }
                    else
                    {
                        $receiver_subject = $englishLangReceiverinfo->subject;
                        $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $englishLangReceiverinfo->body); //
                    }
                    $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $receiver_msg);
                    $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
                    $receiver_msg = str_replace('{uuid}', $unique_code, $receiver_msg);
                    $receiver_msg = str_replace('{created_at}', date('Y-m-d'), $receiver_msg);
                    $receiver_msg = str_replace('{soft_name}', Session::get('name'), $receiver_msg);

                    if (checkAppMailEnvironment())
                    {
                        try
                        {
                            $this->email->sendEmail($transfer->receiver->email, $receiver_subject, $receiver_msg);
                        }
                        catch (\Exception $e)
                        {
                            \DB::rollBack();
                            clearActionSession();
                            $this->helper->one_time_message('error', $e->getMessage());
                            return redirect('moneytransfer');
                        }
                    }
                }
                else
                {
                    $email_explode                 = explode("@", trim($sessionValue['receiver']));
                    $unregisteredUserNameFromEmail = $email_explode[0];

                    $profileName = Session::get('name');
                    $subject     = 'Notice of Transfer!';
                    $message     = 'Hi ' . $unregisteredUserNameFromEmail . ',<br><br>';
                    $message .= 'You have got ' . moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])) . ' money transfer from ' . Auth::user()->email . '.<br>';
                    $message .= 'To receive, please register on : ' . url('/register') . ' with current email.<br><br>';
                    $message .= 'Regards,<br>';
                    $message .= $profileName;

                    if (checkAppMailEnvironment())
                    {
                        try
                        {
                            $this->email->sendEmail($sessionValue['receiver'], $subject, $message);
                        }
                        catch (\Exception $e)
                        {
                            \DB::rollBack();
                            clearActionSession();
                            $this->helper->one_time_message('error', $e->getMessage());
                            return redirect('moneytransfer');
                        }
                    }
                }
            }
            elseif ($phoneRegex && $processedBy == "phone")
            {
                /**
                 * SMS for sender
                 */
                //if other language's subject and body not set, get en sub and body for mail
                $englishSenderLangSMSinfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                // dd($englishSenderLangSMSinfo);
                $senderSmsInfo = EmailTemplate::where(['temp_id' => 1, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();
                if (!empty($senderSmsInfo->subject) && !empty($senderSmsInfo->body))
                {
                    $sender_subject = $senderSmsInfo->subject;
                    $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $senderSmsInfo->body);
                }
                else
                {
                    $sender_subject = $englishSenderLangSMSinfo->subject;
                    $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $englishSenderLangSMSinfo->body);
                }
                $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $sender_msg);
                $sender_msg = str_replace('{soft_name}', Session::get('name'), $sender_msg);

                if (!empty($transfer->sender->carrierCode) && !empty($transfer->sender->phone))
                {
                    if (checkAppSmsEnvironment())
                    {
                        if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                        {
                            try
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->sender->carrierCode . $transfer->sender->phone, $sender_msg);
                            }
                            catch (\Exception $e)
                            {
                                \DB::rollBack();
                                clearActionSession();
                                $this->helper->one_time_message('error', $e->getMessage());
                                return redirect('moneytransfer');
                            }
                        }
                    }
                }

                /**
                 * SMS to receiver
                 */
                //if other language's subject and body not set, get en sub and body for mail
                $englishLangReceiverSmsInfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                $receiverSmsInfo            = EmailTemplate::where([
                    'temp_id'     => 2,
                    'language_id' => Session::get('default_language'),
                    'type'        => 'sms',
                ])->select('subject', 'body')->first();

                if (isset($userInfo))
                {
                    if (!empty($receiverSmsInfo->subject) && !empty($receiverSmsInfo->body))
                    {
                        $receiver_subject = $receiverSmsInfo->subject;
                        $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $receiverSmsInfo->body); //
                    }
                    else
                    {
                        $receiver_subject = $englishLangReceiverSmsInfo->subject;
                        $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $englishLangReceiverSmsInfo->body); //
                    }
                    $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $receiver_msg);
                    $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
                    $receiver_msg = str_replace('{soft_name}', Session::get('name'), $receiver_msg);

                    if (!empty($transfer->receiver->carrierCode) && !empty($transfer->receiver->phone))
                    {
                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                try
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->receiver->carrierCode . $transfer->receiver->phone, $receiver_msg);
                                }
                                catch (\Exception $e)
                                {
                                    \DB::rollBack();
                                    clearActionSession();
                                    $this->helper->one_time_message('error', $e->getMessage());
                                    return redirect('moneytransfer');
                                }
                            }
                        }
                    }
                }
            }
            elseif ($processedBy == "email_or_phone")
            {
                if ($emailFilterValidate)
                {
                    /**
                     * Mail To Sender
                     */
                    //if other language's subject and body not set, get en sub and body for mail
                    $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                    $sender_info           = EmailTemplate::where(['temp_id' => 1, 'language_id' => Session::get('default_language'), 'type' => 'email'])->select('subject', 'body')->first();
                    if (!empty($sender_info->subject) && !empty($sender_info->body))
                    {
                        $sender_subject = $sender_info->subject;
                        $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $sender_info->body);
                    }
                    else
                    {
                        $sender_subject = $englishSenderLanginfo->subject;
                        $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $englishSenderLanginfo->body);
                    }
                    $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $sender_msg);

                    $sender_msg = str_replace('{uuid}', $unique_code, $sender_msg);
                    $sender_msg = str_replace('{receiver_id}', isset($userInfo) ? $userInfo->first_name . ' ' . $userInfo->last_name : $sessionValue['receiver'], $sender_msg);
                    $sender_msg = str_replace('{fee}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['percentage_fee'] + $sessionValue['fixed_fee'])), $sender_msg);
                    $sender_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $sender_msg);
                    $sender_msg = str_replace('{soft_name}', Session::get('name'), $sender_msg);

                    if (checkAppMailEnvironment())
                    {
                        try
                        {
                            $this->email->sendEmail($transfer->sender->email, $sender_subject, $sender_msg);
                        }
                        catch (\Exception $e)
                        {
                            \DB::rollBack();
                            clearActionSession();
                            $this->helper->one_time_message('error', $e->getMessage());
                            return redirect('moneytransfer');
                        }
                    }

                    /**
                     * Mail To Receiver
                     */
                    //if other language's subject and body not set, get en sub and body for mail
                    $englishLangReceiverinfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en'])->select('subject', 'body')->first();
                    $receiver_info           = EmailTemplate::where([
                        'temp_id'     => 2,
                        'language_id' => Session::get('default_language'),
                    ])->select('subject', 'body')->first();

                    if (isset($userInfo))
                    {
                        if (!empty($receiver_info->subject) && !empty($receiver_info->body))
                        {
                            $receiver_subject = $receiver_info->subject;
                            $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $receiver_info->body); //
                        }
                        else
                        {
                            $receiver_subject = $englishLangReceiverinfo->subject;
                            $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $englishLangReceiverinfo->body); //
                        }
                        $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $receiver_msg);
                        $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
                        $receiver_msg = str_replace('{uuid}', $unique_code, $receiver_msg);
                        $receiver_msg = str_replace('{created_at}', date('Y-m-d'), $receiver_msg);
                        $receiver_msg = str_replace('{soft_name}', Session::get('name'), $receiver_msg);

                        if (checkAppMailEnvironment())
                        {
                            try
                            {
                                $this->email->sendEmail($transfer->receiver->email, $receiver_subject, $receiver_msg);
                            }
                            catch (\Exception $e)
                            {
                                \DB::rollBack();
                                clearActionSession();
                                $this->helper->one_time_message('error', $e->getMessage());
                                return redirect('moneytransfer');
                            }
                        }
                    }
                    else
                    {
                        $email_explode                 = explode("@", trim($sessionValue['receiver']));
                        $unregisteredUserNameFromEmail = $email_explode[0];

                        $profileName = Session::get('name');
                        $subject     = 'Notice of Transfer!';
                        $message     = 'Hi ' . $unregisteredUserNameFromEmail . ',<br><br>';
                        $message .= 'You have got ' . moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])) . ' money transfer from ' . Auth::user()->email . '.<br>';
                        $message .= 'To receive, please register on : ' . url('/register') . ' with current email.<br><br>';
                        $message .= 'Regards,<br>';
                        $message .= $profileName;

                        if (checkAppMailEnvironment())
                        {
                            try
                            {
                                $this->email->sendEmail($sessionValue['receiver'], $subject, $message);
                            }
                            catch (\Exception $e)
                            {
                                \DB::rollBack();
                                clearActionSession();
                                $this->helper->one_time_message('error', $e->getMessage());
                                return redirect('moneytransfer');
                            }
                        }
                    }
                }
                elseif ($phoneRegex)
                {
                    /**
                     * SMS for sender
                     */
                    //if other language's subject and body not set, get en sub and body for mail
                    $englishSenderLangSMSinfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                    // dd($englishSenderLangSMSinfo);
                    $senderSmsInfo = EmailTemplate::where(['temp_id' => 1, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();
                    if (!empty($senderSmsInfo->subject) && !empty($senderSmsInfo->body))
                    {
                        $sender_subject = $senderSmsInfo->subject;
                        $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $senderSmsInfo->body);
                    }
                    else
                    {
                        $sender_subject = $englishSenderLangSMSinfo->subject;
                        $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $englishSenderLangSMSinfo->body);
                    }
                    $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $sender_msg);
                    $sender_msg = str_replace('{soft_name}', Session::get('name'), $sender_msg);

                    if (!empty($transfer->sender->carrierCode) && !empty($transfer->sender->phone))
                    {
                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                try
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->sender->carrierCode . $transfer->sender->phone, $sender_msg);
                                }
                                catch (\Exception $e)
                                {
                                    \DB::rollBack();
                                    clearActionSession();
                                    $this->helper->one_time_message('error', $e->getMessage());
                                    return redirect('moneytransfer');
                                }
                            }
                        }
                    }

                    /**
                     * SMS to receiver
                     */
                    //if other language's subject and body not set, get en sub and body for mail
                    $englishLangReceiverSmsInfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                    $receiverSmsInfo            = EmailTemplate::where([
                        'temp_id'     => 2,
                        'language_id' => Session::get('default_language'),
                        'type'        => 'sms',
                    ])->select('subject', 'body')->first();

                    if (isset($userInfo))
                    {
                        if (!empty($receiverSmsInfo->subject) && !empty($receiverSmsInfo->body))
                        {
                            $receiver_subject = $receiverSmsInfo->subject;
                            $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $receiverSmsInfo->body); //
                        }
                        else
                        {
                            $receiver_subject = $englishLangReceiverSmsInfo->subject;
                            $receiver_msg     = str_replace('{receiver_id}', $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name, $englishLangReceiverSmsInfo->body); //
                        }
                        $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $receiver_msg);
                        $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
                        $receiver_msg = str_replace('{soft_name}', Session::get('name'), $receiver_msg);

                        if (!empty($transfer->receiver->carrierCode) && !empty($transfer->receiver->phone))
                        {
                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->receiver->carrierCode . $transfer->receiver->phone, $receiver_msg);
                                }
                            }
                        }
                    }
                }
            }
            \DB::commit();

            $data['menu']                  = 'send_receive';
            $data['submenu']               = 'send';
            $data['transInfo']             = $sessionValue;
            $data['transInfo']['trans_id'] = $sender_t->id;
            $data['userInfo']              = $userInfo;
            $receiverName                  = isset($userInfo) ? $userInfo->first_name . ' ' . $userInfo->last_name : '';
            $data['receiverName']          = $receiverName;
            $data['content_title']         = 'Money Transfer';

            //clearing session
            clearActionSession();
            return view('user_dashboard.moneytransfer.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('moneytransfer');
        }
    }

    //Send Money - Generate pdf for print
    public function transferPrintPdf($trans_id)
    {
        $data['companyInfo']        = Setting::where(['type' => 'general', 'name' => 'logo'])->first(['value']);
        $data['transactionDetails'] = Transaction::with(['end_user:id,first_name,last_name', 'currency:id,symbol,code'])
            ->where(['id' => $trans_id])
            ->first(['transaction_type_id', 'end_user_id', 'currency_id', 'uuid', 'created_at', 'status', 'subtotal', 'charge_percentage', 'charge_fixed', 'total', 'note']);

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);
        $mpdf = new \Mpdf\Mpdf([
            'mode'                 => 'utf-8',
            'format'               => 'A3',
            'orientation'          => 'P',
            'shrink_tables_to_fit' => 0,
        ]);
        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;
        $mpdf->SetJS('this.print();');
        $mpdf->WriteHTML(view('user_dashboard.moneytransfer.transferPaymentPdf', $data));
        $mpdf->Output('sendMoney_' . time() . '.pdf', 'I'); // this will output data
    }
}
