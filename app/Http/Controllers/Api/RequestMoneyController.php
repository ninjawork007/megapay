<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\FeesLimit;
use App\Models\Preference;
use App\Models\RequestPayment;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RequestMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $notFound           = 404;
    public $email;

    public function __construct()
    {
        $this->email = new EmailController();
    }

    //Request Money starts here
    public function postRequestMoneyEmailCheckApi()
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
                $success['message'] = 'You cannot request money to yourself!';
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

    public function postRequestMoneyPhoneCheckApi()
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
                    $success['message'] = 'You cannot request money to yourself!';
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

    //Request Payment Currency List
    public function getRequestMoneyCurrenciesApi()
    {
        $currenciesList  = Currency::where(['status' => 'Active'])->get(['id', 'code', 'symbol']);
        $feesLimitWallet = FeesLimit::where(['transaction_type_id' => Request_To, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $success['currencies'] = $this->requestWalletList($currenciesList, $feesLimitWallet);

        //Set default wallet as selected - starts
        $user_id                            = request('user_id');
        $defaultWallet                      = Wallet::where(['user_id' => $user_id, 'is_default' => 'Yes'])->first(['currency_id']);
        $success['defaultWalletCurrencyId'] = $defaultWallet->currency_id;
        //Set default wallet as selected - ends

        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Helper Functions Starts here
    public function requestWalletList($currenciesList, $feesLimitWallet)
    {
        $selectedWallet = [];
        foreach ($currenciesList as $currency)
        {
            foreach ($feesLimitWallet as $flWallet)
            {
                if ($currency->id == $flWallet->currency_id && $flWallet->has_transaction == 'Yes')
                {
                    $selectedWallet[$currency->id]['id']     = $currency->id;
                    $selectedWallet[$currency->id]['code']   = $currency->code;
                    $selectedWallet[$currency->id]['symbol'] = $currency->symbol;
                }
            }
        }
        return $selectedWallet;
    }
    //Helper Functions Ends here

    public function postRequestMoneyPayApi()
    {
        // dd(request()->all());

        $uid          = request('user_id');
        $emailOrPhone = request('emailOrPhone');
        $amount       = request('amount');
        $currency_id  = request('currencyId');
        $note         = request('note');
        $uuid         = unique_code();

        $soft_name = Setting::where(['name' => 'name'])->first(['value']);
        $language  = Setting::where(['name' => 'default_language'])->first(['value']);

        //processedBy
        $processedBy         = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        $emailFilterValidate = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $emailOrPhone);

        $senderInfo = User::where(['id' => $uid])->first(['first_name', 'last_name', 'email']);
        $senderName = $senderInfo->first_name . ' ' . $senderInfo->last_name;

        if ($emailFilterValidate)
        {
            $receiverInfo = User::where(['email' => trim($emailOrPhone)])->first(['id', 'first_name', 'last_name', 'carrierCode', 'phone']);
        }
        elseif ($phoneRegex)
        {
            $receiverInfo = User::where(['formattedPhone' => $emailOrPhone])->first(['id', 'first_name', 'last_name', 'email']); // fetching receiver id
        }
        $receiverName = isset($receiverInfo) ? $receiverInfo->first_name . ' ' . $receiverInfo->last_name : '';

        try
        {
            \DB::beginTransaction();

            //Save data to Request Payment Table
            $RequestPayment              = new RequestPayment();
            $RequestPayment->user_id     = $uid;
            $RequestPayment->receiver_id = isset($receiverInfo) ? $receiverInfo->id : null;
            $RequestPayment->currency_id = $currency_id;
            $RequestPayment->uuid        = $uuid;
            $RequestPayment->amount      = $amount;
            if ($emailFilterValidate)
            {
                $RequestPayment->email = $emailOrPhone;
            }
            elseif ($phoneRegex)
            {
                $RequestPayment->phone = $emailOrPhone;
            }
            $RequestPayment->note   = $note;
            $RequestPayment->status = "Pending";
            $RequestPayment->save();

            // Created pending transaction for Request Created
            $transaction                           = new Transaction();
            $transaction->user_id                  = $uid;
            $transaction->currency_id              = $currency_id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $RequestPayment->id;
            $transaction->transaction_type_id      = Request_From;
            if (!empty($receiverInfo))
            {
                $transaction->end_user_id = $receiverInfo->id;
                $transaction->user_type   = 'registered';
            }
            else
            {
                $transaction->user_type = 'unregistered';
            }
            if ($emailFilterValidate)
            {
                $transaction->email = $emailOrPhone;
            }
            elseif ($phoneRegex)
            {
                $transaction->phone = $emailOrPhone;
            }
            $transaction->subtotal          = $amount;
            $transaction->charge_percentage = 0;
            $transaction->charge_fixed      = 0;
            $transaction->total             = $amount;
            $transaction->note              = $note;
            $transaction->status            = 'Pending';
            $transaction->save();

            // Created pending transaction for Request To
            $transactionRequestTo                           = new Transaction();
            $transactionRequestTo->user_id                  = isset($receiverInfo) ? $receiverInfo->id : null;
            $transactionRequestTo->end_user_id              = $uid;
            $transactionRequestTo->currency_id              = $currency_id;
            $transactionRequestTo->uuid                     = $uuid;
            $transactionRequestTo->transaction_reference_id = $RequestPayment->id;
            $transactionRequestTo->transaction_type_id      = Request_To;
            $transactionRequestTo->subtotal                 = $amount;
            $transactionRequestTo->charge_percentage        = 0;
            $transactionRequestTo->charge_fixed             = 0;
            $transactionRequestTo->total                    = '-' . $amount;
            $transactionRequestTo->note                     = $note;
            if (!empty($receiverInfo))
            {
                $transactionRequestTo->user_type = 'registered';
            }
            else
            {
                $transactionRequestTo->user_type = 'unregistered';
            }
            if ($emailFilterValidate)
            {
                $transactionRequestTo->email = $emailOrPhone;
            }
            elseif ($phoneRegex)
            {
                $transactionRequestTo->phone = $emailOrPhone;
            }
            $transactionRequestTo->status = 'Pending';
            $transactionRequestTo->save();

            //request creator wallet check
            $createWallet = Wallet::where(['user_id' => $uid, 'currency_id' => $currency_id])->first();
            if (empty($createWallet))
            {
                $wallet              = new Wallet();
                $wallet->user_id     = $uid;
                $wallet->currency_id = $currency_id;
                $wallet->balance     = 0.00;
                $wallet->is_default  = 'No';
                $wallet->save();
            }

            /**
             * Mail & SMS to registered request acceptor - starts
             */
            if ($emailFilterValidate && $processedBy == "email")
            {
                /**
                 * Mail to registered request acceptor
                 */
                if (isset($receiverInfo))
                {
                    $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 4, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

                    $req_create_temp = EmailTemplate::where([
                        'temp_id'     => 4,
                        'language_id' => $language->value,
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
                    $req_create_msg = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $req_create_msg);
                    $req_create_msg = str_replace('{amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($amount)), $req_create_msg);
                    $req_create_msg = str_replace('{uuid}', $uuid, $req_create_msg);
                    $req_create_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $req_create_msg);
                    $req_create_msg = str_replace('{note}', $note, $req_create_msg);
                    $req_create_msg = str_replace('{soft_name}', $soft_name->value, $req_create_msg);

                    if (checkAppMailEnvironment())
                    {
                        try {
                            $this->email->sendEmail($emailOrPhone, $req_create_sub, $req_create_msg);
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
                    /**
                     * Mail to unregistered request acceptor
                     */
                    $emailOrPhone_explode          = explode("@", trim($emailOrPhone));
                    $unregisteredUserNameFromEmail = $emailOrPhone_explode[0];
                    $profileName                   = $soft_name->value;
                    $subject                       = 'Notice of Request Creation!';
                    $message                       = 'Hi ' . $unregisteredUserNameFromEmail . ',<br><br>';
                    $message .= 'You have got ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($amount)) . ' payment request from ' . $senderInfo->email . '.<br>';
                    $message .= 'To accept the request, please register on : ' . url('/register') . ' with current email.<br><br>';
                    $message .= 'Regards,<br>';
                    $message .= $profileName;

                    if (checkAppMailEnvironment())
                    {
                        try {
                            $this->email->sendEmail($emailOrPhone, $subject, $message);
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
            elseif ($phoneRegex && $processedBy == "phone")
            {
                /**
                 * SMS to registered request acceptor
                 */
                if (isset($receiverInfo))
                {
                    $enRpSmsTempInfo = EmailTemplate::where(['temp_id' => 4, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                    $reqSmsTempInfo  = EmailTemplate::where(['temp_id' => 4, 'language_id' => $language->value, 'type' => 'sms'])->select('subject', 'body')->first();
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
                    $reqSmsTempInfo_msg = str_replace('{amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($amount)), $reqSmsTempInfo_msg);
                    $reqSmsTempInfo_msg = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $reqSmsTempInfo_msg);

                    if (!empty($receiverInfo->carrierCode) && !empty($receiverInfo->phone))
                    {
                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                try {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $receiverInfo->carrierCode . $receiverInfo->phone, $reqSmsTempInfo_msg);
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
                }
            }
            elseif ($processedBy == "email_or_phone")
            {
                if ($emailFilterValidate)
                {
                    if (isset($receiverInfo))
                    {
                        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 4, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

                        $req_create_temp = EmailTemplate::where([
                            'temp_id'     => 4,
                            'language_id' => $language->value,
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
                        $req_create_msg = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $req_create_msg);
                        $req_create_msg = str_replace('{amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($amount)), $req_create_msg);
                        $req_create_msg = str_replace('{uuid}', $uuid, $req_create_msg);
                        $req_create_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $req_create_msg);
                        $req_create_msg = str_replace('{note}', $note, $req_create_msg);
                        $req_create_msg = str_replace('{soft_name}', $soft_name->value, $req_create_msg);

                        if (checkAppMailEnvironment())
                        {
                            try {
                                $this->email->sendEmail($emailOrPhone, $req_create_sub, $req_create_msg);
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
                        /**
                         * Mail to unregistered request acceptor
                         */
                        $emailOrPhone_explode          = explode("@", trim($emailOrPhone));
                        $unregisteredUserNameFromEmail = $emailOrPhone_explode[0];
                        $profileName                   = $soft_name->value;
                        $subject                       = 'Notice of Request Creation!';
                        $message                       = 'Hi ' . $unregisteredUserNameFromEmail . ',<br><br>';
                        $message .= 'You have got ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($amount)) . ' payment request from ' . $senderInfo->email . '.<br>';
                        $message .= 'To accept the request, please register on : ' . url('/register') . ' with current email.<br><br>';
                        $message .= 'Regards,<br>';
                        $message .= $profileName;
                        if (checkAppMailEnvironment())
                        {
                            try {
                                $this->email->sendEmail($emailOrPhone, $subject, $message);
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
                elseif ($phoneRegex)
                {
                    if (isset($receiverInfo))
                    {
                        $enRpSmsTempInfo = EmailTemplate::where(['temp_id' => 4, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
                        $reqSmsTempInfo  = EmailTemplate::where(['temp_id' => 4, 'language_id' => $language->value, 'type' => 'sms'])->select('subject', 'body')->first();
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
                        $reqSmsTempInfo_msg = str_replace('{amount}', moneyFormat($RequestPayment->currency->symbol, formatNumber($amount)), $reqSmsTempInfo_msg);
                        $reqSmsTempInfo_msg = str_replace('{creator}', $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name, $reqSmsTempInfo_msg);

                        if (!empty($receiverInfo->carrierCode) && !empty($receiverInfo->phone))
                        {
                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    try {
                                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $receiverInfo->carrierCode . $receiverInfo->phone, $reqSmsTempInfo_msg);
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
            /**
             * Mail & SMS to registered request acceptor - ends
             */
            \DB::commit();

            $success['status']    = $this->successStatus;
            $success['tr_ref_id'] = $RequestPayment->id;
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
    //Request Money Ends here
}
