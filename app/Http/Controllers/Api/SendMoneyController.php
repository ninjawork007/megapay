<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\FeesLimit;
use App\Models\Preference;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SendMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $notFound           = 404;
    public $email;

    public function __construct()
    {
        $this->email = new EmailController();
    }

    public function checkProcessedByApi()
    {
        $processedBy = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        return response()->json([
            'status'      => $this->successStatus,
            'processedBy' => $processedBy,
        ]);
    }

    //Send Money Starts here
    public function postSendMoneyEmailCheckApi()
    {
        // dd(request()->all());
        if (request('user_id'))
        {
            $user_id       = request('user_id');
            $receiverEmail = request('receiverEmail');
            $user          = User::where('id', '=', $user_id)->first(['email']);
            $receiver      = User::where('email', '=', $receiverEmail)->first(['email']);

            if (@$user->email == @$receiver->email)
            {
                $success['status']  = $this->unauthorisedStatus;
                $success['message'] = 'You cannot send money to yourself!';
                return response()->json(['success' => $success], $this->successStatus);
            }
            else
            {
                if ($receiver)
                {
                    $success['status'] = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
                else
                {
                    $success['status'] = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
            }
        }
        else
        {
            echo "In else block";exit();return false;
        }
    }

    public function postSendMoneyPhoneCheckApi()
    {
        // dd(request()->all());
        if (request('user_id'))
        {
            $user     = User::where('id', '=', request('user_id'))->first(['formattedPhone']);
            $receiver = User::where('formattedPhone', '=', request('receiverPhone'))->first(['formattedPhone']);
            if (!empty($user->formattedPhone))
            {
                if (@$user->formattedPhone == @$receiver->formattedPhone)
                {
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = 'You cannot send money to yourself!';
                    return response()->json(['success' => $success], $this->successStatus);
                }
                else
                {
                    if ($receiver)
                    {
                        $success['status'] = $this->successStatus;
                        return response()->json(['success' => $success], $this->successStatus);
                    }
                    else
                    {
                        $success['status'] = $this->successStatus;
                        return response()->json(['success' => $success], $this->successStatus);
                    }
                }
            }
            else
            {
                $success['status']  = $this->notFound;
                $success['message'] = 'Please set your phone number first!';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }
        else
        {
            echo "In else block";exit();return false;
        }
    }

    public function getSendMoneyCurrenciesApi()
    {
        // dd(request()->all());
        $user_id = request('user_id');

        /*Check Whether Currency is Activated in feesLimit*/
        $walletList                      = Wallet::with('currency:id,code')->where(['user_id' => $user_id])->whereHas('active_currency')->get(['currency_id', 'is_default']);
        $checkWhetherCurrencyIsActivated = FeesLimit::where(['transaction_type_id' => Transferred, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $success['currencies']           = $this->walletList($walletList, $checkWhetherCurrencyIsActivated);
        $success['status']               = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Helper Functions Starts here
    public function walletList($activeWallet, $feesLimitWallet)
    {
        $selectedWallet = [];
        foreach ($activeWallet as $aWallet)
        {
            foreach ($feesLimitWallet as $flWallet)
            {
                if ($aWallet->currency_id == $flWallet->currency_id && $flWallet->has_transaction == 'Yes')
                {
                    $selectedWallet[$aWallet->currency_id]['id']         = $aWallet->currency_id;
                    $selectedWallet[$aWallet->currency_id]['code']       = $aWallet->currency->code;
                    $selectedWallet[$aWallet->currency_id]['is_default'] = $aWallet->is_default;
                }
            }
        }
        return $selectedWallet;
    }
    //Helper Functions Ends here

    public function postSendMoneyFeesAmountLimitCheckApi()
    {
        // dd(request()->all());
        $currency_id = request('sendCurrency');
        $user_id     = request('user_id');
        $amount      = request('sendAmount');
        $feesDetails = FeesLimit::with('currency:id,code,symbol')
            ->where(['transaction_type_id' => Transferred, 'currency_id' => $currency_id])->first(['charge_percentage', 'charge_fixed', 'currency_id', 'min_limit', 'max_limit']);

        //Wallet Balance Limit Check Starts here
        $feesPercentage      = $amount * ($feesDetails->charge_percentage / 100);
        $checkAmountWithFees = $amount + $feesDetails->charge_fixed + $feesPercentage;
        $wallet              = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['balance']);
        if (@$wallet)
        {
            if ((@$checkAmountWithFees) > (@$wallet->balance) || (@$wallet->balance < 0))
            {
                $success['reason']  = 'insufficientBalance';
                $success['message'] = "Sorry, not enough funds to perform the operation!";
                $success['status']  = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }
        //Wallet Balance Limit Check Ends here

        //Amount Limit Check Starts here
        if (@$feesDetails)
        {
            if (@$feesDetails->max_limit == null)
            {
                if ((@$amount < @$feesDetails->min_limit))
                {
                    $success['reason']   = 'minLimit';
                    $success['minLimit'] = @$feesDetails->min_limit;
                    $success['message']  = 'Minimum amount ' . @$feesDetails->min_limit;
                    $success['status']   = '401';
                }
                else
                {
                    $feesPercentage                = $amount * ($feesDetails->charge_percentage / 100);
                    $feesFixed                     = $feesDetails->charge_fixed;
                    $totalFess                     = $feesPercentage + $feesFixed;
                    $totalAmount                   = $amount + $totalFess;
                    $success['sendAmount']         = $amount;
                    $success['sendCurrency']       = $currency_id;
                    $success['totalFees']          = $totalFess;
                    $success['sendAmountDisplay']  = formatNumber($amount);
                    $success['totalFeesDisplay']   = formatNumber($totalFess);
                    $success['totalAmountDisplay'] = formatNumber($totalAmount);
                    $success['currCode']           = $feesDetails->currency->code;
                    $success['currSymbol']         = $feesDetails->currency->symbol;
                    $success['status']             = $this->successStatus;
                }
            }
            else
            {
                if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                {
                    $success['reason']   = 'minMaxLimit';
                    $success['minLimit'] = @$feesDetails->min_limit;
                    $success['maxLimit'] = @$feesDetails->max_limit;
                    $success['message']  = 'Minimum amount ' . @$feesDetails->min_limit . ' and Maximum amount ' . @$feesDetails->max_limit;
                    $success['status']   = '401';
                }
                else
                {
                    $feesPercentage                = $amount * ($feesDetails->charge_percentage / 100);
                    $feesFixed                     = $feesDetails->charge_fixed;
                    $totalFess                     = $feesPercentage + $feesFixed;
                    $totalAmount                   = $amount + $totalFess;
                    $success['sendAmount']         = $amount;
                    $success['sendCurrency']       = $currency_id;
                    $success['totalFees']          = $totalFess;
                    $success['sendAmountDisplay']  = formatNumber($amount);
                    $success['totalFeesDisplay']   = formatNumber($totalFess);
                    $success['totalAmountDisplay'] = formatNumber($totalAmount);
                    $success['currCode']           = $feesDetails->currency->code;
                    $success['currSymbol']         = $feesDetails->currency->symbol;
                    $success['status']             = $this->successStatus;
                }
            }
            return response()->json(['success' => $success], $this->successStatus);
        }
        else
        {
            $feesPercentage                = 0;
            $feesFixed                     = 0;
            $totalFess                     = $feesPercentage + $feesFixed;
            $totalAmount                   = $amount + $totalFess;
            $success['sendAmount']         = $amount;
            $success['sendCurrency']       = $currency_id;
            $success['totalFees']          = $totalFess;
            $success['sendAmountDisplay']  = formatNumber($amount);
            $success['totalFeesDisplay']   = formatNumber($totalFess);
            $success['totalAmountDisplay'] = formatNumber($totalAmount);
            $success['currCode']           = $feesDetails->currency->code;
            $success['currSymbol']         = $feesDetails->currency->symbol;
            $success['status']             = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        //Amount Limit Check Ends here
    }

    public function postSendMoneyPayApi()
    {
        // d(request()->all(),1);
        $user_id             = request('user_id');
        $emailOrPhone        = request('emailOrPhone');
        $currency_id         = request('currency_id');
        $amount              = request('amount');
        $totalFees           = request('totalFees');
        $total_with_fee      = $amount + $totalFees;
        $note                = request('note');
        $unique_code         = unique_code();
        $emailFilterValidate = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $emailOrPhone);

        //processedBy
        $processedBy = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;

        //feesDetails
        $feesDetails = FeesLimit::where(['transaction_type_id' => Transferred, 'currency_id' => $currency_id])->first(['charge_percentage', 'charge_fixed']);

        //Setting details
        $soft_name = Setting::where(['name' => 'name'])->first(['value']);
        $language  = Setting::where(['name' => 'default_language'])->first(['value']);

        $user = User::where(['id' => $user_id])->first(['email']);

        if ($emailFilterValidate)
        {
            $receiverInfo = User::where(['email' => trim($emailOrPhone)])->first(['id', 'first_name', 'last_name', 'email']); // fetching receiver id
        }
        elseif ($phoneRegex)
        {
            $receiverInfo = User::where(['formattedPhone' => $emailOrPhone])->first(['id', 'first_name', 'last_name', 'email']); // fetching receiver id
        }
        $receiverName = isset($receiverInfo) ? $receiverInfo->first_name . ' ' . $receiverInfo->last_name : '';

        try
        {
            \DB::beginTransaction();

            //Save to transfer table
            $transfer              = new Transfer();
            $transfer->sender_id   = $user_id;
            $transfer->receiver_id = isset($receiverInfo) ? $receiverInfo->id : null;
            $transfer->currency_id = $currency_id;
            $transfer->uuid        = $unique_code;
            $transfer->fee         = $totalFees;
            $transfer->amount      = $amount;
            $transfer->note        = $note;
            if ($emailFilterValidate)
            {
                $transfer->email = $emailOrPhone;
            }
            elseif ($phoneRegex)
            {
                $transfer->phone = $emailOrPhone;
            }
            if ($transfer->receiver_id != null)
            {
                $transfer->status = 'Success';
            }
            else
            {
                $transfer->status = 'Pending';
            }
            $transfer->save();

            //Sender Transaction save starts here
            $sender_t                           = new Transaction();
            $sender_t->currency_id              = $currency_id;
            $sender_t->user_id                  = $user_id;
            $sender_t->end_user_id              = isset($receiverInfo) ? $receiverInfo->id : null;
            $sender_t->uuid                     = $unique_code;
            $sender_t->transaction_reference_id = $transfer->id;
            $sender_t->transaction_type_id      = Transferred;
            $sender_t->user_type                = isset($receiverInfo) ? 'registered' : 'unregistered';
            if ($emailFilterValidate)
            {
                $sender_t->email = $emailOrPhone;
            }
            elseif ($phoneRegex)
            {
                $sender_t->phone = $emailOrPhone;
            }
            $sender_t->subtotal          = $amount;
            $sender_t->percentage        = @$feesDetails->charge_percentage ? @$feesDetails->charge_percentage : 0;
            $sender_t->charge_percentage = @$feesDetails->charge_percentage ? $amount * (@$feesDetails->charge_percentage / 100) : 0;
            $sender_t->charge_fixed      = @$feesDetails->charge_fixed ? @$feesDetails->charge_fixed : 0;
            $sender_t->total             = '-' . ($total_with_fee);
            $sender_t->note              = $note;
            $sender_t->status            = $transfer->status;
            $sender_t->save();
            //Sender Transaction save ends here

            //Receiver Transactions Save starts here
            $receiver_t                           = new Transaction();
            $receiver_t->currency_id              = $currency_id;
            $receiver_t->user_id                  = isset($receiverInfo) ? $receiverInfo->id : null;
            $receiver_t->end_user_id              = $user_id;
            $receiver_t->uuid                     = $unique_code;
            $receiver_t->transaction_reference_id = $transfer->id;
            $receiver_t->transaction_type_id      = Received;
            $receiver_t->user_type                = isset($receiverInfo) ? 'registered' : 'unregistered';
            if ($emailFilterValidate)
            {
                $receiver_t->email = $emailOrPhone;
            }
            elseif ($phoneRegex)
            {
                $receiver_t->phone = $emailOrPhone;
            }
            $receiver_t->subtotal          = $amount;
            $receiver_t->percentage        = 0;
            $receiver_t->charge_percentage = 0;
            $receiver_t->charge_fixed      = 0;
            $receiver_t->total             = $amount;
            $receiver_t->note              = $note;
            $receiver_t->status            = $transfer->status;
            $receiver_t->save();
            //Receiver Transaction Save ends here

            //Updating Sender Wallet Balance
            $senderWallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['id', 'balance']);
            $senderWallet->balance = $senderWallet->balance - $total_with_fee;
            $senderWallet->save();

            //Updating Receiver Wallet Balance - If receiver id is not null in transfers table and user exists in system
            if (!empty($transfer->receiver_id) && isset($receiverInfo))
            {
                $receiverWallet = Wallet::where(['user_id' => $receiverInfo->id, 'currency_id' => $currency_id])->first(['id', 'balance']);
                //Check whether receiver has wallet or not
                if (empty($receiverWallet))
                {
                    //create wallet if doesn't exist
                    $receiverWalletNewInstance              = new Wallet();
                    $receiverWalletNewInstance->user_id     = isset($receiverInfo) ? $receiverInfo->id : null;
                    $receiverWalletNewInstance->currency_id = $currency_id;
                    $receiverWalletNewInstance->is_default  = 'No';
                    $receiverWalletNewInstance->balance     = $amount;
                    $receiverWalletNewInstance->save();
                }
                else
                {
                    //adding tranferred amount to receiver's wallet balance
                    $receiverWallet->balance = ($receiverWallet->balance + $amount);
                    $receiverWallet->save();
                }
            }
            //

            if ($emailFilterValidate && $processedBy == "email")
            {
                /**
                 * Mail To Sender
                 */
                if (checkAppMailEnvironment())
                {
                    //if other language's subject and body not set, get en sub and body for mail
                    $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                    $sender_info           = EmailTemplate::where(['temp_id' => 1, 'language_id' => $language->value, 'type' => 'email'])->select('subject', 'body')->first();
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
                    $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $sender_msg);
                    $sender_msg = str_replace('{uuid}', $unique_code, $sender_msg);
                    $sender_msg = str_replace('{receiver_id}', isset($receiverInfo) ? $receiverInfo->first_name . ' ' . $receiverInfo->last_name : $emailOrPhone, $sender_msg);
                    $sender_msg = str_replace('{fee}', moneyFormat($transfer->currency->symbol, formatNumber($totalFees)), $sender_msg);
                    $sender_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $sender_msg);
                    $sender_msg = str_replace('{soft_name}', $soft_name->value, $sender_msg);

                    try {
                        $this->email->sendEmail($transfer->sender->email, $sender_subject, $sender_msg);
                    }
                    catch (Exception $e)
                    {
                        \DB::rollBack();
                        $success['status']  = $this->unauthorisedStatus;
                        $success['message'] = $e->getMessage();
                        return response()->json(['success' => $success], $this->unauthorisedStatus);
                    }
                }

                /**
                 * Mail To Receiver
                 */
                $this->mailToReceiver($language->value, $receiverInfo, $transfer, $amount, $unique_code, $soft_name->value, $emailOrPhone, $user->email);
            }
            elseif ($phoneRegex && $processedBy == "phone")
            {
                /**
                 * SMS for sender
                 */
                if (!empty($transfer->sender->carrierCode) && !empty($transfer->sender->phone))
                {
                    if (checkAppSmsEnvironment())
                    {
                        if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                        {
                            //if other language's subject and body not set, get en sub and body for mail
                            $englishSenderLangSMSinfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                            // dd($englishSenderLangSMSinfo);

                            $senderSmsInfo = EmailTemplate::where(['temp_id' => 1, 'language_id' => $language->value, 'type' => 'sms'])->select('subject', 'body')->first();
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
                            $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $sender_msg);
                            $sender_msg = str_replace('{soft_name}', $soft_name->value, $sender_msg);

                            try {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->sender->carrierCode . $transfer->sender->phone, $sender_msg);
                            }
                            catch (Exception $e)
                            {
                                \DB::rollBack();
                                $success['status']  = $this->unauthorisedStatus;
                                $success['message'] = $e->getMessage();
                                return response()->json(['success' => $success], $this->unauthorisedStatus);
                            }
                            //for checking Nexmo API Response
                            // $s = sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->sender->carrierCode . $transfer->sender->phone, $sender_msg);
                            // if ($s == true) {
                            //     \DB::commit();
                            // } else {
                            //     \DB::rollBack();
                            // }
                        }
                    }
                }

                /**
                 * SMS to receiver
                 */
                if (isset($receiverInfo))
                {
                    //if other language's subject and body not set, get en sub and body for mail
                    if (!empty($transfer->receiver->carrierCode) && !empty($transfer->receiver->phone))
                    {
                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                $englishLangReceiverSmsInfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                                $receiverSmsInfo            = EmailTemplate::where([
                                    'temp_id'     => 2,
                                    'language_id' => $language->value,
                                    'type'        => 'sms',
                                ])->select('subject', 'body')->first();
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
                                $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $receiver_msg);
                                $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
                                $receiver_msg = str_replace('{soft_name}', $soft_name->value, $receiver_msg);

                                try {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->receiver->carrierCode . $transfer->receiver->phone, $receiver_msg);
                                }
                                catch (Exception $e)
                                {
                                    \DB::rollBack();
                                    $success['status']  = $this->unauthorisedStatus;
                                    $success['message'] = $e->getMessage();
                                    return response()->json(['success' => $success], $this->unauthorisedStatus);
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
                    if (checkAppMailEnvironment())
                    {

                        //if other language's subject and body not set, get en sub and body for mail
                        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                        $sender_info           = EmailTemplate::where(['temp_id' => 1, 'language_id' => $language->value, 'type' => 'email'])->select('subject', 'body')->first();
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
                        $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $sender_msg);
                        $sender_msg = str_replace('{uuid}', $unique_code, $sender_msg);
                        $sender_msg = str_replace('{receiver_id}', isset($receiverInfo) ? $receiverInfo->first_name . ' ' . $receiverInfo->last_name : $emailOrPhone, $sender_msg);
                        $sender_msg = str_replace('{fee}', moneyFormat($transfer->currency->symbol, formatNumber($totalFees)), $sender_msg);
                        $sender_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $sender_msg);
                        $sender_msg = str_replace('{soft_name}', $soft_name->value, $sender_msg);

                        try {
                            $this->email->sendEmail($transfer->sender->email, $sender_subject, $sender_msg);
                        }
                        catch (Exception $e)
                        {
                            \DB::rollBack();
                            $success['status']  = $this->unauthorisedStatus;
                            $success['message'] = $e->getMessage();
                            return response()->json(['success' => $success], $this->unauthorisedStatus);
                        }
                    }

                    /**
                     * Mail To Receiver
                     */
                    $this->mailToReceiver($language->value, $receiverInfo, $transfer, $amount, $unique_code, $soft_name->value, $emailOrPhone, $user->email);
                }
                elseif ($phoneRegex)
                {
                    /**
                     * SMS for sender
                     */
                    if (!empty($transfer->sender->carrierCode) && !empty($transfer->sender->phone))
                    {
                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                //if other language's subject and body not set, get en sub and body for mail
                                $englishSenderLangSMSinfo = EmailTemplate::where(['temp_id' => 1, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                                // dd($englishSenderLangSMSinfo);

                                $senderSmsInfo = EmailTemplate::where(['temp_id' => 1, 'language_id' => $language->value, 'type' => 'sms'])->select('subject', 'body')->first();
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
                                $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $sender_msg);
                                $sender_msg = str_replace('{soft_name}', $soft_name->value, $sender_msg);

                                try {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->sender->carrierCode . $transfer->sender->phone, $sender_msg);
                                }
                                catch (Exception $e)
                                {
                                    \DB::rollBack();
                                    $success['status']  = $this->unauthorisedStatus;
                                    $success['message'] = $e->getMessage();
                                    return response()->json(['success' => $success], $this->unauthorisedStatus);
                                }
                            }
                        }
                    }

                    /**
                     * SMS to receiver
                     */
                    if (isset($receiverInfo))
                    {
                        //if other language's subject and body not set, get en sub and body for mail
                        if (!empty($transfer->receiver->carrierCode) && !empty($transfer->receiver->phone))
                        {
                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    $englishLangReceiverSmsInfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                                    $receiverSmsInfo            = EmailTemplate::where([
                                        'temp_id'     => 2,
                                        'language_id' => $language->value,
                                        'type'        => 'sms',
                                    ])->select('subject', 'body')->first();
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
                                    $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $receiver_msg);
                                    $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
                                    $receiver_msg = str_replace('{soft_name}', $soft_name->value, $receiver_msg);

                                    try {
                                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->receiver->carrierCode . $transfer->receiver->phone, $receiver_msg);
                                    }
                                    catch (Exception $e)
                                    {
                                        \DB::rollBack();
                                        $success['status']  = $this->unauthorisedStatus;
                                        $success['message'] = $e->getMessage();
                                        return response()->json(['success' => $success], $this->unauthorisedStatus);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            \DB::commit();
            $success['status']    = $this->successStatus;
            $success['tr_ref_id'] = $transfer->id;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    public function mailToReceiver($language, $receiverInfo, $transfer, $amount, $unique_code, $soft_name, $unregisteredEmail, $userEmail)
    {
        //if other language's subject and body not set, get en sub and body for mail
        $englishLangReceiverinfo = EmailTemplate::where(['temp_id' => 2, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $receiver_info           = EmailTemplate::where([
            'temp_id'     => 2,
            'language_id' => $language,
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        if (isset($receiverInfo))
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
            $receiver_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($amount)), $receiver_msg);
            $receiver_msg = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $receiver_msg);
            $receiver_msg = str_replace('{uuid}', $unique_code, $receiver_msg);
            $receiver_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $receiver_msg);
            $receiver_msg = str_replace('{soft_name}', $soft_name, $receiver_msg);

            if (checkAppMailEnvironment())
            {
                try {
                    $this->email->sendEmail($transfer->receiver->email, $receiver_subject, $receiver_msg);
                }
                catch (Exception $e)
                {
                    \DB::rollBack();
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = $e->getMessage();
                    return response()->json(['success' => $success], $this->unauthorisedStatus);
                }
            }
        }
        else
        {
            $emailOrPhone_explode          = explode("@", trim($unregisteredEmail)); //careful about this, place here request email only
            $unregisteredUserNameFromEmail = $emailOrPhone_explode[0];
            $profileName                   = $soft_name;
            $subject                       = 'Notice of Transfer!';
            $message                       = 'Hi ' . $unregisteredUserNameFromEmail . ',<br><br>';
            $message .= 'You have got ' . moneyFormat($transfer->currency->symbol, formatNumber($amount)) . ' money transfer from ' . $userEmail . '.<br>';
            $message .= 'To receive, please register on : ' . url('/register') . ' with current email.<br><br>';
            $message .= 'Regards,<br>';
            $message .= $profileName;

            if (checkAppMailEnvironment())
            {
                try {
                    $this->email->sendEmail($unregisteredEmail, $subject, $message); //careful about this, place here request email only
                }
                catch (Exception $e)
                {
                    \DB::rollBack();
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = $e->getMessage();
                    return response()->json(['success' => $success], $this->unauthorisedStatus);
                }
            }
        }
    }
    //Send Money Ends here
}
