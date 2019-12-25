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
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcceptCancelRequestMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $email;

    public function __construct()
    {
        $this->email = new EmailController();
    }

    //Accept/Cancel request payment starts here
    public function getAcceptRequestEmailOrPhone()
    {
        // dd(request()->all());

        $requestPayment = RequestPayment::with(['currency:id,symbol,code','user:id,email'])->where('id', request('tr_ref_id'))->first(['email', 'phone', 'amount', 'user_id', 'currency_id']);
        try
        {
            if (!empty($requestPayment->user))
            {
                $success['email'] = $requestPayment->user->email;
            }
            elseif (!empty($requestPayment->phone))
            {
                $success['phone'] = $requestPayment->phone;
            }
            else
            {
                $success['email'] = null;
                $success['phone'] = null;
            }
            $success['amount']         = $requestPayment->amount;
            $success['currency']       = $requestPayment->currency->code;
            $success['currencySymbol'] = $requestPayment->currency->symbol;
            $success['currency_id']    = $requestPayment->currency->id;
            $success['status']         = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (\Exception $e)
        {
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    public function getAcceptRequestAmountLimit()
    {
        // dd(request()->all());
        $amount      = request('amount');
        $currency_id = request('currency_id');
        $user_id     = request('user_id');

        $RequestAcceptorWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['id']);
        if ($RequestAcceptorWallet == '')
        {
            $success['reason']  = 'invalidCurrency';
            $success['message'] = "You don't have the requested currency!";
            $success['status']  = '401';
            return response()->json(['success' => $success], $this->successStatus);
        }

        //Wallet Balance Limit Check Starts here
        $wallet              = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['balance']);
        $feesDetails         = FeesLimit::where(['transaction_type_id' => Request_To, 'currency_id' => $currency_id])->first(['charge_fixed', 'charge_percentage', 'min_limit', 'max_limit']);
        $feesPercentage      = $amount * ($feesDetails->charge_percentage / 100);
        $checkAmountWithFees = $amount + $feesDetails->charge_fixed + $feesPercentage;
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
                    $success['status'] = $this->successStatus;
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
                    $success['status'] = $this->successStatus;
                }
            }
        }
        else
        {
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }

        return response()->json(['success' => $success], $this->successStatus);
        //Amount Limit Check Ends here
    }

    public function getAcceptFeesDetails()
    {
        // dd(request()->all());
        $user_id     = request('user_id');
        $amount      = request('amount');
        $currency_id = request('currency_id');
        $feesDetails = FeesLimit::where(['transaction_type_id' => Request_To, 'currency_id' => $currency_id])->first(['charge_percentage', 'charge_fixed']);
        // dd($feesDetails);
        try
        {
            $feesPercentage         = $amount * ($feesDetails->charge_percentage / 100);
            $totalFess              = $feesPercentage + (@$feesDetails->charge_fixed);
            $success['totalAmount'] = $amount + $totalFess;
            $success['totalFees']   = $totalFess;
            $currency               = Currency::where(['id' => $currency_id])->first(['symbol', 'code']);
            $success['currSymbol']  = $currency->symbol;
            $success['currCode']    = $currency->code;
            $success['status']      = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (\Exception $e)
        {
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage();
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    //only acceptor can accept request payment
    public function requestAcceptedConfirm()
    {
        // dd(request()->all());

        $tr_ref_id         = request('tr_ref_id');
        $amount            = request('amount');
        $currency_id       = request('currency_id');
        $uid               = request('user_id');
        $totalFee          = request('totalFees');
        $tr_email_or_phone = request('tr_email_or_phone');

        //Setting details
        $soft_name = Setting::where(['name' => 'name'])->first(['value']);
        $language  = Setting::where(['name' => 'default_language'])->first(['value']);
        $FeesLimit = FeesLimit::where(['currency_id' => $currency_id, 'transaction_type_id' => Request_To])->first(['charge_percentage', 'charge_fixed']);

        $processedBy         = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        $emailFilterValidate = filter_var($tr_email_or_phone, FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $tr_email_or_phone);

        try
        {
            \DB::beginTransaction();

            //Save to Request Payment Table

            $RequestPayment                = RequestPayment::find($tr_ref_id); //carefully select (if you want- may miss some field)
            $RequestPayment->accept_amount = $amount;
            $RequestPayment->status        = "Success";
            $RequestPayment->save();

            //Update Request Creator Wallet
            $RequestSenderWallet          = Wallet::where(['user_id' => $RequestPayment->user_id, 'currency_id' => $currency_id])->first(['id', 'balance']);
            $RequestSenderWallet->balance = ($RequestSenderWallet->balance + $amount);
            $RequestSenderWallet->save();

            //Update Request Acceptor Wallet
            $RequestAcceptorWallet          = Wallet::where(['user_id' => $uid, 'currency_id' => $currency_id])->first(['id', 'balance']);
            $RequestAcceptorWallet->balance = ($RequestAcceptorWallet->balance - ($totalFee + $amount));
            $RequestAcceptorWallet->save();

            //Update Request Creator Transaction Information

            $transaction_C                    = Transaction::where(['user_id' => $RequestPayment->user_id, 'currency_id' => $currency_id, 'transaction_reference_id' => $RequestPayment->id, 'transaction_type_id' => Request_From])->first();
            $transaction_C->percentage        = 0;
            $transaction_C->charge_percentage = 0;
            $transaction_C->charge_fixed      = 0;
            $transaction_C->subtotal          = $amount;
            $t_totalTransaction_C             = $transaction_C->subtotal;
            $transaction_C->total             = $t_totalTransaction_C;
            $transaction_C->status            = 'Success';
            $transaction_C->save();

            //Update Request Acceptor Transaction Information
            $transaction_A = Transaction::where(['user_id' => $RequestPayment->receiver_id, 'currency_id' => $currency_id, 'transaction_reference_id' => $RequestPayment->id, 'transaction_type_id' => Request_To])
                ->first();
            $transaction_A->percentage        = @$FeesLimit->charge_percentage ? @$FeesLimit->charge_percentage : 0;
            $transaction_A->charge_percentage = @$FeesLimit->charge_percentage ? $amount * (@$FeesLimit->charge_percentage / 100) : 0;
            $transaction_A->charge_fixed      = @$FeesLimit->charge_fixed ? @$FeesLimit->charge_fixed : 0;
            $transaction_A->subtotal          = $amount;
            $t_totalTransaction_A             = $transaction_A->subtotal + ($transaction_A->charge_percentage + $transaction_A->charge_fixed);
            $transaction_A->total             = '-' . $t_totalTransaction_A;
            $transaction_A->status            = 'Success';
            $transaction_A->save();

            ///////////////////////////MAIL AND SMS - starts//////////////////////////////////
            if ($emailFilterValidate && $processedBy == "email")
            {
                $this->onlyEmailToRequestCreatorOnRequestAccept($language->value, $RequestPayment, $soft_name->value);
            }
            elseif ($phoneRegex && $processedBy == "phone")
            {
                $this->onlySmsTORequestCreatorOnRequestAccept($language->value, $RequestPayment);
            }
            elseif ($processedBy == "email_or_phone")
            {
                if ($emailFilterValidate)
                {
                    $this->onlyEmailToRequestCreatorOnRequestAccept($language->value, $RequestPayment, $soft_name->value);
                }
                elseif ($phoneRegex)
                {
                    $this->onlySmsTORequestCreatorOnRequestAccept($language->value, $RequestPayment);
                }
            }
            ////////////////////////////MAIL AND SMS - ends//////////////////////////////////
            \DB::commit();
            $success['status'] = $this->successStatus;
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

    public function onlyEmailToRequestCreatorOnRequestAccept($language, $RequestPayment, $soft_name)
    {
        /**
         * Mail when request accepted to RequestPayment Creator
         */
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
            try
            {
                $this->email->sendEmail($RequestPayment->user->email, $rp_acc_sub, $rp_msg);
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

    public function onlySmsTORequestCreatorOnRequestAccept($language, $RequestPayment)
    {
        /**
         * SMS when request accepted to RequestPayment Creator
         */
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
                    try
                    {
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $RequestPayment->user->carrierCode . $RequestPayment->user->phone, $reqPaymentAcceptSmsTempInfo_msg);
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

    //both acceptor and creator can cancel request payment - one function for both - logic depends on user_id
    public function requestCancel()
    {
        // dd(request()->all());
        $tr_email_or_phone = request('tr_email_or_phone');
        $user_id           = request('user_id');

        try
        {
            \DB::beginTransaction();

            $TransactionA         = Transaction::find(request('tr_id'), ['id', 'status', 'transaction_type_id', 'transaction_reference_id']);
            $TransactionA->status = "Blocked";
            // dd($TransactionA);
            $TransactionA->save();

            $transaction_type_id  = $TransactionA->transaction_type_id == Request_To ? Request_From : Request_To;
            $TransactionB         = Transaction::where(['transaction_reference_id' => $TransactionA->transaction_reference_id, 'transaction_type_id' => $transaction_type_id])->first(['id', 'status']);
            $TransactionB->status = "Blocked";
            // dd($TransactionB);
            $TransactionB->save();

            $RequestPayment         = RequestPayment::find($TransactionA->transaction_reference_id);
            $RequestPayment->status = "Blocked";
            $RequestPayment->save();

            $this->sendRequestCancelNotificationToAcceptorOrCreatorForApi($RequestPayment, $tr_email_or_phone, $user_id);
            \DB::commit();
            return response()->json([
                'status' => $this->successStatus,
            ]);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return response()->json([
                'status'  => $this->unauthorisedStatus,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function sendRequestCancelNotificationToAcceptorOrCreatorForApi($RequestPayment, $tr_email_or_phone, $user_id)
    {
        $processedBy         = Preference::where(['category' => 'preference', 'field' => 'processed_by'])->first(['value'])->value;
        $emailFilterValidate = filter_var($tr_email_or_phone, FILTER_VALIDATE_EMAIL);
        $phoneRegex          = preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $tr_email_or_phone);

        $soft_name = Setting::where(['name' => 'name'])->first(['value'])->value;

        $messageFromCreatorToAcceptor = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) . ' has been cancelled by ' .
        $RequestPayment->user->first_name . ' ' . $RequestPayment->user->last_name . '.';

        //////////////////////////////////////////////////////////////////////////
        if ($emailFilterValidate && $processedBy == "email")
        {
            if (checkAppMailEnvironment())
            {
                if ($user_id == $RequestPayment->user_id)
                {
                    // dd([$RequestPayment->user_id, $user_id]);
                    if (!empty($RequestPayment->receiver_id))
                    {
                        //ok
                        $data = $this->onlyEmailToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                            $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->email);
                        return $data;
                    }
                    else
                    {
                        //ok
                        $data = $this->onlyEmailToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->email);
                        return $data;
                    }
                }
                elseif (!empty($RequestPayment->receiver_id) && $user_id == $RequestPayment->receiver_id)
                {
                    //ok
                    $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                    ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                    $data = $this->onlyEmailToRequestCreatorForApi($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name, $RequestPayment->user->email);
                    return $data;
                }
            }
        }
        elseif ($phoneRegex && $processedBy == "phone")
        {
            if ($user_id == $RequestPayment->user_id)
            {
                if (!empty($RequestPayment->receiver_id))
                {
                    $data = $this->onlySmsToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                        $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->carrierCode, $RequestPayment->receiver->phone);
                    return $data;
                }
                else
                {
                    $data = $this->onlySmsToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->phone);
                    return $data;
                }
            }
            elseif (!empty($RequestPayment->receiver_id) && $user_id == $RequestPayment->receiver_id)
            {
                $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                $data = $this->onlySmsToRequestCreatorForApi($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name,
                    $RequestPayment->user->carrierCode, $RequestPayment->user->phone);
                return $data;
            }
        }
        elseif ($processedBy == "email_or_phone")
        {
            if ($emailFilterValidate)
            {
                if (checkAppMailEnvironment())
                {
                    if ($user_id == $RequestPayment->user_id)
                    {
                        if (!empty($RequestPayment->receiver_id))
                        {
                            $data = $this->onlyEmailToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                                $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->email);
                            return $data;
                        }
                        else
                        {
                            $data = $this->onlyEmailToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->email);
                            return $data;
                        }
                    }
                    elseif (!empty($RequestPayment->receiver_id) && $user_id == $RequestPayment->receiver_id)
                    {
                        $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                        ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                        $data = $this->onlyEmailToRequestCreatorForApi($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name, $RequestPayment->user->email);
                        return $data;
                    }
                }
            }
            elseif ($phoneRegex)
            {
                if ($user_id == $RequestPayment->user_id)
                {
                    if (!empty($RequestPayment->receiver_id))
                    {
                        $data = $this->onlySmsToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor,
                            $RequestPayment->receiver->first_name, $RequestPayment->receiver->last_name, $soft_name, $RequestPayment->receiver->carrierCode, $RequestPayment->receiver->phone);
                        return $data;
                    }
                    else
                    {
                        $data = $this->onlySmsToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $soft_name, $RequestPayment->phone);
                        return $data;
                    }
                }
                elseif (!empty($RequestPayment->receiver_id) && $user_id == $RequestPayment->receiver_id)
                {
                    $messageFromAcceptorToCreator = 'Your request payment #' . $RequestPayment->uuid . ' of ' . moneyFormat($RequestPayment->currency->symbol, formatNumber($RequestPayment->amount)) .
                    ' has been cancelled by ' . $RequestPayment->receiver->first_name . ' ' . $RequestPayment->receiver->last_name . '.';
                    $data = $this->onlySmsToRequestCreatorForApi($messageFromAcceptorToCreator, $RequestPayment->user->first_name, $RequestPayment->user->last_name, $soft_name,
                        $RequestPayment->user->carrierCode, $RequestPayment->user->phone);
                    return $data;
                }
            }
        }
        //////////////////////////////////////////////////////////////////////////
    }

    // Email to registered receiver
    public function onlyEmailToRegisteredRequestReceiverForApi($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
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
    public function onlyEmailToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $softName, $requestPaymentEmail)
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
    public function onlyEmailToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $requestPaymentEmail)
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
    public function onlySmsToRegisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
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
    public function onlySmsToUnregisteredRequestReceiverForApi($messageFromCreatorToAcceptor, $softName, $RequestPaymentUserPhone)
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
    public function onlySmsToRequestCreatorForApi($messageFromAcceptorToCreator, $requestPaymentFirstName, $requestPaymentLastName, $softName, $RequestPaymentUserCarrierCode,
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

    //Accept/Cancel request payment ends here
}
