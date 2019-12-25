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
use App\Models\RequestPayment;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Session;
use Validator;

class RequestPaymentController extends Controller
{
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function add()
    {
        //set the session for validating the action
        setActionSession();

        $data['menu']          = 'send_receive';
        $data['submenu']       = 'receive';
        $data['content_title'] = 'Request Payment';

        $activeCurrency       = Currency::where(['status' => 'Active'])->get(['id', 'status', 'code']);
        $feesLimitCurrency    = FeesLimit::where(['transaction_type_id' => Request_To, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $data['currencyList'] = $this->currencyList($activeCurrency, $feesLimitCurrency);

        //pm_v2.3
        $data['defaultWallet'] = $defaultWallet = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        return view('user_dashboard.requestPayment.add', $data);
    }

    public function currencyList($activeCurrency, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($activeCurrency as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $aCurrency->status == 'Active' && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$aCurrency->id]['id']   = $aCurrency->id;
                    $selectedCurrency[$aCurrency->id]['code'] = $aCurrency->code;
                }
            }
        }
        return $selectedCurrency;
    }

    public function requestPaymentEmailValidate(Request $request)
    {
        $phoneRegex = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $request->requestCreatorEmailOrPhone);
        if ($phoneRegex)
        {
            $user = User::where(['id' => auth()->user()->id])->first(['formattedPhone']);
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
            if ($request->requestCreatorEmailOrPhone == auth()->user()->email || $request->requestCreatorEmailOrPhone == auth()->user()->formattedPhone)
            {
                return response()->json([
                    'status'  => true,
                    'message' => __("You Cannot Request Money To Yourself!"),
                ]);
            }
        }
    }

    public function store(Request $request)
    {
        $data['menu']          = 'send_receive';
        $data['submenu']       = 'receive';
        $data['content_title'] = 'Request Payment';
        $rules                 = array(
            'amount' => 'required',
            'email'  => 'required',
            'note'   => 'required',
        );
        $fieldNames = array(
            'amount' => __("Amount"),
            'email'  => __("Email"),
            'note'   => __("Note"),
        );

        //new by arif v2.3
        // backend Validation - starts
        $messages = [
            //
        ];

        if ($request->requestMoneyProcessedBy == 'email')
        {
            $rules['email'] = 'required|email';
        }
        elseif ($request->requestMoneyProcessedBy == 'phone')
        {
            $myStr = explode('+', $request->email);
            if ($request->email[0] != "+" || !is_numeric($myStr[1]))
            {
                return back()->withErrors(__("Please enter valid phone (ex: +12015550123)"))->withInput();
            }
        }
        elseif ($request->requestMoneyProcessedBy == 'email_or_phone')
        {
            $myStr = explode('+', $request->email);
            //valid number is not entered
            if ($request->email[0] != "+" || !is_numeric($myStr[1]))
            {
                //check if valid email
                $rules['email'] = 'required|email';

                $messages = [
                    'email' => __("Please enter valid email (ex: user@gmail.com) or phone (ex: +12015550123)"),
                ];
            }
        }

        //as below function expects custom values
        $request['requestCreatorEmailOrPhone'] = $request->email;
        $requestPaymentEmailValidate           = $this->requestPaymentEmailValidate($request);
        if ($requestPaymentEmailValidate)
        {
            if ($requestPaymentEmailValidate->getData()->status == true || $requestPaymentEmailValidate->getData()->status == 404)
            {
                return back()->withErrors(__($requestPaymentEmailValidate->getData()->message))->withInput();
            }
        }

        // backend Validation - ends

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            // dd($request->all());
            $currency              = Currency::find($request->currency_id, ['id', 'symbol']);
            $request['currSymbol'] = $currency->symbol;
            $data['transInfo']     = $request->all();
            session(['transInfo' => $request->all()]);
            return view('user_dashboard.requestPayment.confirmation', $data);
        }
    }

    public function requestMoneyConfirm()
    {
        actionSessionCheck();

        $uid          = Auth::user()->id;
        $sessionValue = Session::get('transInfo');
        // dd($sessionValue);
        $processedBy = $sessionValue['requestMoneyProcessedBy'];
        $uuid        = unique_code();

        $emailFilterValidate = filter_var(trim($sessionValue['email']), FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            trim($sessionValue['email']));

        if ($emailFilterValidate)
        {
            $userInfo = User::where(['email' => $sessionValue['email']])->first();
        }
        elseif ($phoneRegex)
        {
            $userInfo = User::where(['formattedPhone' => trim($sessionValue['email'])])->first(); // fetching request creator id
        }
        $receiverName = isset($userInfo) ? $userInfo->first_name . ' ' . $userInfo->last_name : '';

        try
        {
            \DB::beginTransaction();

            //Save to RequestPayment table - starts
            $RequestPayment              = new RequestPayment();
            $RequestPayment->user_id     = $uid;
            $RequestPayment->receiver_id = isset($userInfo) ? $userInfo->id : null;
            $RequestPayment->currency_id = $sessionValue['currency_id'];
            $RequestPayment->uuid        = $uuid;
            $RequestPayment->amount      = $sessionValue['amount'];
            if ($emailFilterValidate)
            {
                $RequestPayment->email = $sessionValue['email'];
            }
            elseif ($phoneRegex)
            {
                $RequestPayment->phone = $sessionValue['email'];
            }
            $RequestPayment->note   = $sessionValue['note'];
            $RequestPayment->status = "Pending";
            $RequestPayment->save();
            //Save to RequestPayment table - ends

            // Created pending transaction for Request Created - starts
            $transaction                           = new Transaction();
            $transaction->user_id                  = $uid;
            $transaction->currency_id              = $sessionValue['currency_id'];
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $RequestPayment->id;
            $transaction->transaction_type_id      = Request_From;
            $transaction->subtotal                 = $sessionValue['amount'];
            $transaction->charge_percentage        = 0;
            $transaction->charge_fixed             = 0;
            $transaction->total                    = $sessionValue['amount'];
            $transaction->note                     = $RequestPayment->note;
            if (!empty($userInfo))
            {
                $transaction->end_user_id = $userInfo->id;
                $transaction->user_type   = 'registered';
            }
            else
            {
                $transaction->user_type = 'unregistered';
            }

            if ($emailFilterValidate)
            {
                $transaction->email = $sessionValue['email'];
            }
            elseif ($phoneRegex)
            {
                $transaction->phone = $sessionValue['email'];
            }

            $transaction->status = 'Pending';
            $transaction->save();
            // Created pending transaction for Request Created - ends

            // Created pending transaction for Request To - starts
            $transactionRequestTo                           = new Transaction();
            $transactionRequestTo->user_id                  = isset($userInfo) ? $userInfo->id : null;
            $transactionRequestTo->end_user_id              = $uid;
            $transactionRequestTo->currency_id              = $sessionValue['currency_id'];
            $transactionRequestTo->uuid                     = $uuid;
            $transactionRequestTo->transaction_reference_id = $RequestPayment->id;
            $transactionRequestTo->transaction_type_id      = Request_To;
            $transactionRequestTo->subtotal                 = $sessionValue['amount'];
            $transactionRequestTo->charge_percentage        = 0;
            $transactionRequestTo->charge_fixed             = 0;
            $transactionRequestTo->total                    = '-' . $sessionValue['amount'];
            $transactionRequestTo->note                     = $RequestPayment->note;
            if (!empty($userInfo))
            {
                $transactionRequestTo->user_type = 'registered';
            }
            else
            {
                $transactionRequestTo->user_type = 'unregistered';
            }
            if ($emailFilterValidate)
            {
                $transactionRequestTo->email = $sessionValue['email'];
            }
            elseif ($phoneRegex)
            {
                $transactionRequestTo->phone = $sessionValue['email'];
            }
            $transactionRequestTo->status = 'Pending';
            $transactionRequestTo->save();
            // Created pending transaction for Request To - ends

            //Fixed in pm_v2.1
            //request creator wallet check - starts
            $createWalletIfNotExist = Wallet::where(['user_id' => $uid, 'currency_id' => $sessionValue['currency_id']])->first(['id']);
            if (empty($createWalletIfNotExist))
            {
                $wallet              = new Wallet();
                $wallet->user_id     = $uid;
                $wallet->currency_id = $sessionValue['currency_id'];
                $wallet->balance     = 0.00;
                $wallet->is_default  = 'No';
                $wallet->save();
            }
            //request creator wallet check - ends

            /**
             * Mail & SMS to registered request acceptor - starts
             */
            if ($emailFilterValidate && $processedBy == "email")
            {
                if (isset($userInfo))
                {
                    $this->mailToRegisteredRequestAcceptor($receiverName, $RequestPayment->currency->symbol, $sessionValue['amount'], $uuid, $sessionValue['note'], $sessionValue['email'], $RequestPayment->user->first_name, $RequestPayment->user->last_name);
                }
                else
                {
                    $this->mailToUnRegisteredRequestAcceptor($sessionValue['email'], $RequestPayment->currency->symbol, $sessionValue['amount']);
                }
            }
            elseif ($phoneRegex && $processedBy == "phone")
            {
                $this->smsToRegisteredRequestAcceptor($userInfo, $receiverName, $RequestPayment->currency->symbol, $sessionValue['amount'], $RequestPayment->user->first_name, $RequestPayment->user->last_name);
            }
            elseif ($processedBy == "email_or_phone")
            {
                if ($emailFilterValidate)
                {
                    if (isset($userInfo))
                    {
                        $this->mailToRegisteredRequestAcceptor($receiverName, $RequestPayment->currency->symbol, $sessionValue['amount'], $uuid, $sessionValue['note'], $sessionValue['email'], $RequestPayment->user->first_name, $RequestPayment->user->last_name);
                    }
                    else
                    {
                        $this->mailToUnRegisteredRequestAcceptor($sessionValue['email'], $RequestPayment->currency->symbol, $sessionValue['amount']);
                    }
                }
                elseif ($phoneRegex)
                {
                    $this->smsToRegisteredRequestAcceptor($userInfo, $receiverName, $RequestPayment->currency->symbol, $sessionValue['amount'], $RequestPayment->user->first_name, $RequestPayment->user->last_name);
                }
            }
            /**
             * Mail & SMS to registered request acceptor - ends
             */
            \DB::commit();

            $data['transInfo']             = $sessionValue;
            $data['transInfo']['trans_id'] = $transaction->id;
            $data['userInfo']              = $userInfo;
            $data['receiverName']          = $receiverName;
            $data['content_title']         = 'Money Request';

            //clearing session
            clearActionSession();
            return view('user_dashboard.requestPayment.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('request_payment/add');
        }
    }

    public function mailToRegisteredRequestAcceptor($receiverName, $RequestPaymentCurrencySymbol, $sessionValueAmount, $uuid, $sessionValueNote, $sessionValueEmail, $RequestPaymentUserFirstName,
        $RequestPaymentUserLastName)
    {
        /**
         * Mail when request created
         */
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 4, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $req_create_temp       = EmailTemplate::where([
            'temp_id'     => 4,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        if (!empty($req_create_temp->subject) && !empty($req_create_temp->body))
        {
            $req_create_sub = $req_create_temp->subject;
            $req_create_msg = str_replace('{acceptor}', $receiverName, $req_create_temp->body);
        }
        else
        {
            $req_create_sub = $englishSenderLanginfo->subject;
            $req_create_msg = str_replace('{acceptor}', $receiverName, $englishSenderLanginfo->body);
        }
        $req_create_msg = str_replace('{creator}', $RequestPaymentUserFirstName . ' ' . $RequestPaymentUserLastName, $req_create_msg);
        $req_create_msg = str_replace('{amount}', moneyFormat($RequestPaymentCurrencySymbol, formatNumber($sessionValueAmount)), $req_create_msg);
        $req_create_msg = str_replace('{uuid}', $uuid, $req_create_msg);
        $req_create_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $req_create_msg);
        $req_create_msg = str_replace('{note}', $sessionValueNote, $req_create_msg);
        $req_create_msg = str_replace('{soft_name}', Session::get('name'), $req_create_msg);

        if (checkAppMailEnvironment())
        {
            try
            {
                $this->email->sendEmail($sessionValueEmail, $req_create_sub, $req_create_msg);
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

    public function mailToUnRegisteredRequestAcceptor($sessionValueEmail, $RequestPaymentCurrencySymbol, $sessionValueAmount)
    {
        /**
         * Mail to unregistered user when request created
         */
        $email_explode                 = explode("@", trim($sessionValueEmail));
        $unregisteredUserNameFromEmail = $email_explode[0];

        $profileName = Session::get('name');
        $subject     = 'Notice of Request Creation!';
        $message     = 'Hi ' . $unregisteredUserNameFromEmail . ',<br><br>';
        $message .= 'You have got ' . moneyFormat($RequestPaymentCurrencySymbol, formatNumber($sessionValueAmount)) . ' payment request from ' . Auth::user()->email . '.<br>';
        $message .= 'To accept the request, please register on : ' . url('/register') . ' with current email.<br><br>';
        $message .= 'Regards,<br>';
        $message .= $profileName;

        if (checkAppMailEnvironment())
        {
            try
            {
                $this->email->sendEmail($sessionValueEmail, $subject, $message);
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

    public function smsToRegisteredRequestAcceptor($userInfo, $receiverName, $RequestPaymentCurrencySymbol, $sessionValueAmount, $RequestPaymentUserFirstName, $RequestPaymentUserLastName)
    {
        if (isset($userInfo))
        {
            /**
             * SMS
             */
            $enRpSmsTempInfo = EmailTemplate::where(['temp_id' => 4, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
            $reqSmsTempInfo  = EmailTemplate::where(['temp_id' => 4, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();
            if (!empty($reqSmsTempInfo->subject) && !empty($reqSmsTempInfo->body))
            {
                $reqSmsTempInfo_sub = $reqSmsTempInfo->subject;
                $reqSmsTempInfo_msg = str_replace('{acceptor}', $receiverName, $reqSmsTempInfo->body);
            }
            else
            {
                $reqSmsTempInfo_sub = $enRpSmsTempInfo->subject;
                $reqSmsTempInfo_msg = str_replace('{acceptor}', $receiverName, $enRpSmsTempInfo->body);
            }
            $reqSmsTempInfo_msg = str_replace('{amount}', moneyFormat($RequestPaymentCurrencySymbol, formatNumber($sessionValueAmount)), $reqSmsTempInfo_msg);
            $reqSmsTempInfo_msg = str_replace('{creator}', $RequestPaymentUserFirstName . ' ' . $RequestPaymentUserLastName, $reqSmsTempInfo_msg);

            if (!empty($userInfo->carrierCode) && !empty($userInfo->phone))
            {
                if (checkAppSmsEnvironment())
                {
                    if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                    {
                        try
                        {
                            sendSMS(getNexmoDetails()->default_nexmo_phone_number, $userInfo->carrierCode . $userInfo->phone, $reqSmsTempInfo_msg);
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

    //Cancel from request acceptor
    public function cancel(Request $request)
    {
        // dd(request()->all());
        $id = $request->id;
        try
        {
            \DB::beginTransaction();
            $TransactionA         = Transaction::find($id); //TODO: query optimization
            $TransactionA->status = "Blocked";
            $TransactionA->save();

            $transaction_type_id = $TransactionA->transaction_type_id == Request_To ? Request_From : Request_To;
            $TransactionB        = Transaction::where([
                'transaction_reference_id' => $TransactionA->transaction_reference_id,
                'transaction_type_id'      => $transaction_type_id])->first(); //TODO: query optimization
            $TransactionB->status = "Blocked";
            $TransactionB->save();

            $RequestPayment         = RequestPayment::find($TransactionA->transaction_reference_id); //TODO: query optimization
            $RequestPayment->status = "Blocked";
            $RequestPayment->save();
            \DB::commit();
            $data = $this->sendRequestCancelNotificationToAcceptorOrCreator($RequestPayment, $request->notificationType); //TODO: query optimization
            return json_encode($data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('dashboard');
        }
    }

    //Cancel from request creator
    public function cancelfrom(Request $request)
    {
        // dd(request()->all());

        $id = $request->id;
        try
        {
            \DB::beginTransaction();
            if ($request->type == Request_From)
            {
                $TransactionA         = Transaction::find($id); //TODO: query optimization
                $TransactionA->status = "Blocked";
                $TransactionA->save();

                $TransactionB         = Transaction::where(['transaction_reference_id' => $TransactionA->transaction_reference_id, 'transaction_type_id' => Request_To])->first(); //TODO: query optimization
                $TransactionB->status = "Blocked";
                $TransactionB->save();

            }
            elseif ($request->type == Request_To)
            {
                $TransactionA         = Transaction::find($id); //TODO: query optimization
                $TransactionA->status = "Blocked";
                $TransactionA->save();

                $TransactionB         = Transaction::where(['transaction_reference_id' => $TransactionA->transaction_reference_id, 'transaction_type_id' => Request_From])->first(); //TODO: query optimization
                $TransactionB->status = "Blocked";
                $TransactionB->save();
            }
            $RequestPayment         = RequestPayment::find($TransactionA->transaction_reference_id); //TODO: query optimization
            $RequestPayment->status = "Blocked";
            $RequestPayment->save();
            \DB::commit();

            $data = $this->sendRequestCancelNotificationToAcceptorOrCreator($RequestPayment, $request->notificationType); //TODO: query optimization
            return json_encode($data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('dashboard');
        }
    }

    public function sendRequestCancelNotificationToAcceptorOrCreator($RequestPayment, $notificationType)
    {
        $processedBy         = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        $emailFilterValidate = filter_var($notificationType, FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $notificationType);

        $soft_name = Session::get('name');

        $messageFromCreatorToAcceptor = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) . ' has been cancelled by ' .
        $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name . '.';

        //////////////////////////////////////////////////////////////////////////
        if ($emailFilterValidate && $processedBy == "email")
        {
            if (auth()->user()->id == $RequestPayment->user_id)
            {
                if (!empty($RequestPayment->receiver_id))
                {
                    //ok
                    $data = $this->onlyEmailToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                        $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->email);
                    return $data;
                }
                else
                {
                    //ok
                    $data = $this->onlyEmailToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->email);
                    return $data;
                }
            }
            elseif (!empty($RequestPayment->receiver_id) && auth()->user()->id == $RequestPayment->receiver_id)
            {
                //ok
                $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                $data = $this->onlyEmailToRequestCreator($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name, $RequestPayment->user->email);
                return $data;
            }
        }
        elseif ($phoneRegex && $processedBy == "phone")
        {
            if (auth()->user()->id == $RequestPayment->user_id)
            {
                if (!empty($RequestPayment->receiver_id))
                {
                    $data = $this->onlySmsToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                        $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->carrierCode, $RequestPayment->receiver->phone);
                    return $data;
                }
                else
                {
                    $data = $this->onlySmsToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->phone);
                    return $data;
                }
            }
            elseif (!empty($RequestPayment->receiver_id) && auth()->user()->id == $RequestPayment->receiver_id)
            {
                $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                $data = $this->onlySmsToRequestCreator($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name,
                    $RequestPayment->user->carrierCode, $RequestPayment->user->phone);
                return $data;
            }
        }
        elseif ($processedBy == "email_or_phone")
        {
            if ($emailFilterValidate)
            {
                if (auth()->user()->id == $RequestPayment->user_id)
                {
                    if (!empty($RequestPayment->receiver_id))
                    {
                        $data = $this->onlyEmailToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                            $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->email);
                        return $data;
                    }
                    else
                    {
                        $data = $this->onlyEmailToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->email);
                        return $data;
                    }
                }
                elseif (!empty($RequestPayment->receiver_id) && auth()->user()->id == $RequestPayment->receiver_id)
                {
                    $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                    ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                    $data = $this->onlyEmailToRequestCreator($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name, $RequestPayment->user->email);
                    return $data;
                }
            }
            elseif ($phoneRegex)
            {
                if (auth()->user()->id == $RequestPayment->user_id)
                {
                    if (!empty($RequestPayment->receiver_id))
                    {
                        $data = $this->onlySmsToRegisteredRequestReceiver($messageFromCreatorToAcceptor,
                            $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->carrierCode, $RequestPayment->receiver->phone);
                        return $data;
                    }
                    else
                    {
                        $data = $this->onlySmsToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->phone);
                        return $data;
                    }
                }
                elseif (!empty($RequestPayment->receiver_id) && auth()->user()->id == $RequestPayment->receiver_id)
                {
                    $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                    ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                    $data = $this->onlySmsToRequestCreator($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name,
                        $RequestPayment->user->carrierCode, $RequestPayment->user->phone);
                    return $data;
                }
            }
        }
        //////////////////////////////////////////////////////////////////////////
    }

    // Email to registered receiver
    public function onlyEmailToRegisteredRequestReceiver($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = 'Cancellation of Request Payment';
        $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>'; //
        $message .= $messageFromAcceptorToCreator;
        $message .= '<br><br>';
        $message .= 'If you have any questions, please feel free to reply to this mail';
        $message .= '<br><br>';
        $message .= 'Regards,';
        $message .= '<br>';
        $message .= $softName;
        try {

            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
        }
    }

    // Email to unregistered receiver
    public function onlyEmailToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = 'Cancellation of Request Payment';
        $message = 'Hi ' . $requestPaymentEmail . ',<br><br>'; //
        $message .= $messageFromCreatorToAcceptor;
        $message .= '<br><br>';
        $message .= 'If you have any questions, please feel free to reply to this mail';
        $message .= '<br><br>';
        $message .= 'Regards,';
        $message .= '<br>';
        $message .= $softName;
        try {
            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
        }
    }

    // Email to registered creator
    public function onlyEmailToRequestCreator($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
    {
        // Mail to request creator when a request is cancelled (both sides)
        $subject = 'Cancellation of Request Payment';
        $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>'; //
        $message .= $messageFromAcceptorToCreator;
        $message .= '<br><br>';
        $message .= 'If you have any questions, please feel free to reply to this mail';
        $message .= '<br><br>';
        $message .= 'Regards,';
        $message .= '<br>';
        $message .= $softName;
        try {

            $this->email->sendEmail($requestPaymentEmail, $subject, $message);
            $data['status'] = 'Cancelled';
            return $data['status'];
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
        }
    }

    // Sms to registered receiver
    public function onlySmsToRegisteredRequestReceiver($messageFromCreatorToAcceptor, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
        $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserCarrierCode) && !empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                {
                    try {

                        // Mail to request creator when a request is cancelled (both sides)
                        $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>';
                        $message .= $messageFromCreatorToAcceptor;
                        $message .= '<br><br>';
                        $message .= 'Regards,';
                        $message .= '<br>';
                        $message .= $softName;
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $RequestPaymentUserCarrierCode . $RequestPaymentUserPhone, $message);
                        $data['status'] = 'Cancelled';
                        return $data['status'];
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                    }
                }
            }
        }
    }

    // Sms to unregistered receiver
    public function onlySmsToUnregisteredRequestReceiver($messageFromCreatorToAcceptor, $softName, $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                {
                    try {

                        // Mail to request creator when a request is cancelled (both sides)
                        $message = 'Hi ' . $RequestPaymentUserPhone . ',<br><br>';
                        $message .= $messageFromCreatorToAcceptor;
                        $message .= '<br><br>';
                        $message .= 'Regards,';
                        $message .= '<br>';
                        $message .= $softName;
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $RequestPaymentUserPhone, $message);
                        $data['status'] = 'Cancelled';
                        return $data['status'];
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                    }
                }
            }
        }
    }

    // Sms to registered creator
    public function onlySmsToRequestCreator($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
        $RequestPaymentUserPhone)
    {
        if (!empty($RequestPaymentUserCarrierCode) && !empty($RequestPaymentUserPhone))
        {
            if (checkAppSmsEnvironment())
            {
                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                {
                    try {

                        // Mail to request creator when a request is cancelled (both sides)
                        $message = 'Hi ' . $requestPaymentFirstName . ' ' . $requestPaymentLastName . ',<br><br>';
                        $message .= $messageFromAcceptorToCreator;
                        $message .= '<br><br>';
                        $message .= 'Regards,';
                        $message .= '<br>';
                        $message .= $softName;
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $RequestPaymentUserCarrierCode . $RequestPaymentUserPhone, $message);
                        $data['status'] = 'Cancelled';
                        return $data['status'];
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                    }
                }
            }
        }
    }

    public function requestAccept($id)
    {
        //set the session for validating the action
        setActionSession();

        $data['menu']           = 'request_payment';
        $data['content_title']  = 'Request Payment';
        $data['icon']           = 'money';
        $data['requestPayment'] = $requestPayment = RequestPayment::with(['currency:id,symbol,code','user:id,email'])->where(['id' => $id])->first();
        $data['transfer_fee']   = $transfer_fee   = FeesLimit::where(['transaction_type_id' => Request_To, 'currency_id' => $requestPayment->currency_id])->first(['charge_percentage', 'charge_fixed']);

        // dd('jre');
        return view('user_dashboard.requestPayment.accept', $data);
    }

    public function requestAccepted(Request $request)
    {
        // dd(request()->all());

        if ($_POST)
        {
            $rules = array(
                'amount' => 'required|numeric',
            );
            $fieldNames = array(
                'amount' => 'Amount',
            );

            // backend Validation - starts
            $request['amount']              = $request->amount;
            $request['currency_id']         = $request->currency_id;
            $request['transaction_type_id'] = Request_To;
            $amountLimitCheck               = $this->amountLimitCheck($request);
            if ($amountLimitCheck->getData()->success->status == 404 || $amountLimitCheck->getData()->success->status == 401)
            {
                return back()->withErrors(__($amountLimitCheck->getData()->success->message))->withInput();
            }
            //backend validation - ends

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);
            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $amount                   = $request->amount;
                $currency_id              = $request->currency_id;
                $data['requestPaymentId'] = $request->id;
                $request['currSymbol']    = $request->currencySymbol;
                $request['totalAmount']   = $request->amount + $request->fee;
                session(['transInfo' => $request->all()]); //needed for requestAcceptedConfirm
                $data['transInfo'] = $request->all();
            }
            return view('user_dashboard.requestPayment.acceptconfirmation', $data);
        }
    }
    //Amount Limit Check
    public function amountLimitCheck(Request $request)
    {
        $amount      = $request->amount;
        $currency_id = $request->currency_id;
        $user_id     = Auth::user()->id;

        $RequestAcceptorWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['id']);
        if (empty($RequestAcceptorWallet))
        {
            $success['status']  = 404;
            $success['message'] = __("You don't have the requested currency!");
            return response()->json(['success' => $success]);
        }

        $wallet              = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['balance']);
        $feesDetails         = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $currency_id])->first(['charge_fixed', 'charge_percentage', 'min_limit', 'max_limit']);
        $feesPercentage      = $amount * ($feesDetails->charge_percentage / 100);
        $checkAmountWithFees = $amount + $feesDetails->charge_fixed + $feesPercentage;
        if (@$wallet)
        {
            if ((@$checkAmountWithFees) > (@$wallet->balance) || (@$wallet->balance < 0))
            {
                $success['message'] = __("Not have enough balance !");
                $success['status']  = '401';
                return response()->json(['success' => $success]);
            }
        }

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
            $success['pFeesHtml']          = formatNumber($feesPercentage);
            $success['fFeesHtml']          = formatNumber($feesFixed);
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = 0;
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
            $success['pFeesHtml']          = formatNumber($feesDetails->charge_percentage);
            $success['fFeesHtml']          = formatNumber($feesDetails->charge_fixed);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = isset($wallet) ? $wallet->balance : 0.00;
        }
        //Code for Fees Limit Ends here
        return response()->json(['success' => $success]);
    }

    public function requestAcceptedConfirm()
    {
        // dd(request()->all());

        actionSessionCheck();

        $uid       = Auth::user()->id;
        $soft_name = Session::get('name');
        $language  = Session::get('default_language');

        $sessionValue = session('transInfo');
        // dd($sessionValue);

        $RequestPaymentId    = $sessionValue['id'];
        $processedBy         = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        $emailFilterValidate = filter_var($sessionValue['emailOrPhone'], FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $sessionValue['emailOrPhone']);

        try
        {
            \DB::beginTransaction();

            $RequestPayment                = RequestPayment::with(['user:id,first_name,last_name,phone,carrierCode,email', 'receiver:id,first_name,last_name', 'currency:id,symbol,code'])->find($RequestPaymentId);
            $RequestPayment->accept_amount = $sessionValue['amount'];
            $RequestPayment->status        = "Success";
            $RequestPayment->save();

            //Update Request Creator Transaction Information
            $FeesLimit                        = FeesLimit::where(['currency_id' => $sessionValue['currency_id'], 'transaction_type_id' => Request_To])->first(['charge_percentage', 'charge_percentage']);
            $transaction_C                    = Transaction::where(['user_id' => $RequestPayment->user_id, 'currency_id' => $sessionValue['currency_id'], 'transaction_reference_id' => $RequestPayment->id, 'transaction_type_id' => Request_From])->first(['id', 'percentage', 'charge_percentage', 'charge_percentage', 'subtotal', 'total', 'status']);
            $transaction_C->percentage        = 0;
            $transaction_C->charge_percentage = 0;
            $transaction_C->charge_fixed      = 0;
            $transaction_C->subtotal          = $sessionValue['amount'];
            $t_total                          = $transaction_C->subtotal;
            $transaction_C->total             = $t_total;
            $transaction_C->status            = 'Success';
            $transaction_C->save();

            //Update Request Acceptor Transaction Information
            $transaction_A = Transaction::where(['user_id' => $RequestPayment->receiver_id, 'currency_id' => $sessionValue['currency_id'], 'transaction_reference_id' => $RequestPayment->id, 'transaction_type_id' => Request_To])->first(['id', 'percentage', 'charge_percentage', 'charge_percentage', 'subtotal', 'total', 'status']);

            $transaction_A->percentage        = @$FeesLimit->charge_percentage ? @$FeesLimit->charge_percentage : 0;
            $transaction_A->charge_percentage = $sessionValue['percentage_fee'];
            $transaction_A->charge_fixed      = $sessionValue['fixed_fee'];
            $transaction_A->subtotal          = $sessionValue['amount'];
            $t_total                          = $transaction_A->subtotal + ($transaction_A->charge_percentage + $transaction_A->charge_fixed);
            $transaction_A->total             = '-' . $t_total;
            $transaction_A->status            = 'Success';
            $transaction_A->save();

            //Update Request Creator Wallet
            $RequestSenderWallet = Wallet::where(['user_id' => $RequestPayment->user_id, 'currency_id' => $sessionValue['currency_id']])->first(['id', 'balance']);
            if (!empty($RequestSenderWallet))
            {
                $RequestSenderWallet->balance = $RequestSenderWallet->balance + $sessionValue['amount'];
                $RequestSenderWallet->save();
            }
            else
            {
                $creatorWallet              = new Wallet();
                $creatorWallet->balance     = $sessionValue['amount'];
                $creatorWallet->user_id     = $RequestPayment->user_id;
                $creatorWallet->currency_id = $sessionValue['currency_id'];
                $creatorWallet->is_default  = 'No';
                $creatorWallet->save();
            }

            //Update Request Acceptor Wallet
            $RequestAcceptorWallet          = Wallet::where(['user_id' => $uid, 'currency_id' => $sessionValue['currency_id']])->first(['id', 'balance']);
            $RequestAcceptorWallet->balance = $RequestAcceptorWallet->balance - ($sessionValue['amount'] + $sessionValue['fee']);
            $RequestAcceptorWallet->save();

            //Mail or SMS try catch
            try
            {
                ///////////////////////////MAIL AND SMS - starts//////////////////////////////////
                if ($emailFilterValidate && $processedBy == "email")
                {
                    /**
                     * Mail when request accepted
                     */
                    $this->onlyEmailToRequestCreatorOnRequestAccept($language, $RequestPayment, $soft_name);
                }
                elseif ($phoneRegex && $processedBy == "phone")
                {
                    /**
                     * SMS to $RequestPayment->user
                     */
                    $this->onlySmsTORequestCreatorOnRequestAccept($language, $RequestPayment);
                }
                elseif ($processedBy == "email_or_phone")
                {
                    if ($emailFilterValidate)
                    {
                        /**
                         * Mail when request accepted
                         */
                        $this->onlyEmailToRequestCreatorOnRequestAccept($language, $RequestPayment, $soft_name);
                    }
                    elseif ($phoneRegex)
                    {
                        /**
                         * SMS to $RequestPayment->user
                         */
                        $this->onlySmsTORequestCreatorOnRequestAccept($language, $RequestPayment);
                    }
                }
                ////////////////////////////MAIL AND SMS - ends//////////////////////////////////
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                clearActionSession();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect("request_payment/accept/$RequestPaymentId");
            }
            \DB::commit();

            $data['transInfo']             = $sessionValue;
            $data['requestCreator']        = $requestCreator        = $RequestPayment->user;
            $data['transInfo']['trans_id'] = $transaction_A->id; //fixed in pm_v2.3

            clearActionSession();
            return view('user_dashboard.requestPayment.acceptsuccess', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect("request_payment/accept/$RequestPaymentId");
        }
    }

    public function onlyEmailToRequestCreatorOnRequestAccept($language, $RequestPayment, $soft_name)
    {
        //if other language's subject and body not set, get en sub and body for mail
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 5, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $rp_accept_temp        = EmailTemplate::where([
            'temp_id'     => 5,
            'language_id' => $language,
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        if (!empty($rp_accept_temp->subject) && !empty($rp_accept_temp->body))
        {
            $rp_acc_sub = $rp_accept_temp->subject;
            $rp_msg     = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $rp_accept_temp->body);
        }
        else
        {
            $rp_acc_sub = $englishSenderLanginfo->subject;
            $rp_msg     = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $englishSenderLanginfo->body);
        }
        $rp_msg = str_replace('{uuid}', $RequestPayment->uuid, $rp_msg);
        $rp_msg = str_replace('{acceptor}', $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name, $rp_msg);
        $rp_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $rp_msg);
        $rp_msg = str_replace('{amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)), $rp_msg);
        $rp_msg = str_replace('{accept_amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->accept_amount)), $rp_msg);
        $rp_msg = str_replace('{currency}', $RequestPayment->currency->code, $rp_msg);
        $rp_msg = str_replace('{soft_name}', $soft_name, $rp_msg);

        if (checkAppMailEnvironment())
        {
            $this->email->sendEmail($RequestPayment->user->email, $rp_acc_sub, $rp_msg);
        }
    }

    public function onlySmsTORequestCreatorOnRequestAccept($language, $RequestPayment)
    {

        $enRpAcceptSmsTempInfo       = EmailTemplate::where(['temp_id' => 5, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $reqPaymentAcceptSmsTempInfo = EmailTemplate::where(['temp_id' => 5, 'language_id' => $language, 'type' => 'sms'])->select('subject', 'body')->first();
        if (!empty($reqPaymentAcceptSmsTempInfo->subject) && !empty($reqPaymentAcceptSmsTempInfo->body))
        {
            $reqPaymentAcceptSmsTempInfo_sub = $reqPaymentAcceptSmsTempInfo->subject;
            $reqPaymentAcceptSmsTempInfo_msg = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $reqPaymentAcceptSmsTempInfo->body);
        }
        else
        {
            $reqPaymentAcceptSmsTempInfo_sub = $enRpAcceptSmsTempInfo->subject;
            $reqPaymentAcceptSmsTempInfo_msg = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $enRpAcceptSmsTempInfo->body);
        }
        $reqPaymentAcceptSmsTempInfo_msg = str_replace('{uuid}', $RequestPayment->uuid, $reqPaymentAcceptSmsTempInfo_msg);
        $reqPaymentAcceptSmsTempInfo_msg = str_replace('{amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)), $reqPaymentAcceptSmsTempInfo_msg);
        $reqPaymentAcceptSmsTempInfo_msg = str_replace('{acceptor}', $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name, $reqPaymentAcceptSmsTempInfo_msg);
        if (!empty($RequestPayment->user->carrierCode) && !empty($RequestPayment->user->phone))
        {
            if (checkAppSmsEnvironment())
            {
                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                {
                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $RequestPayment->user->carrierCode . $RequestPayment->user->phone, $reqPaymentAcceptSmsTempInfo_msg);
                }
            }
        }
        //
    }

    /**
     * Generate pdf for print
     */
    public function printPdf($trans_id)
    {
        $data['companyInfo']        = Setting::where(['type' => 'general', 'name' => 'logo'])->first(['value']);
        $data['transactionDetails'] = $transactionDetails = Transaction::with(['end_user:id,first_name,last_name', 'currency:id,symbol,code'])
            ->where(['id' => $trans_id])
            ->first(['transaction_type_id', 'end_user_id', 'currency_id', 'uuid', 'created_at', 'status', 'subtotal', 'charge_percentage', 'charge_fixed', 'total', 'note', 'user_type', 'email']);

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);
        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A3',
            'orientation' => 'P',
        ]);
        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;
        $mpdf->SetJS('this.print();');
        $mpdf->WriteHTML(view('user_dashboard.requestPayment.requestPaymentPrintPdf', $data));
        $mpdf->Output('requestPayment_' . time() . '.pdf', 'I'); // this will output data
    }
}
