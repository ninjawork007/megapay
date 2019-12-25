<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Users\DepositController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\CurrencyPaymentMethod;
use App\Models\FeesLimit;
use App\Models\Merchant;
use App\Models\MerchantPayment;
use App\Models\PaymentMethod;
use App\Models\Preference;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use CoinPayment;
use Hexters\CoinPayment\Entities\cointpayment_log_trx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Omnipay\Omnipay;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;

class MerchantPaymentController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(Request $request)
    {
        // dd($request->all());

        $merchant_id          = $request->merchant_id;
        $merchant_uuid        = $request->merchant;
        $merchant_currency_id = $request->currency_id;

        //2.3
        $data['merchant'] = $merchant = Merchant::with('currency:id,code')->where(['id' => $merchant_id, 'merchant_uuid' => $merchant_uuid, 'currency_id' => $merchant_currency_id])->first(['id', 'currency_id']);
        // dd($merchant);
        if (!$merchant)
        {
            $this->helper->one_time_message('error', __('Merchant not found!'));
            return redirect('payment/fail');
        }

        //for payUmoney
        if ($merchant->currency->code == "INR")
        {
            Session::put('payumoney_merchant_currency_code', $merchant->currency->code);
        }

        //For showing the message that merchant available or not
        $data['isMerchantAvailable'] = true;
        if (!$merchant)
        {
            $data['isMerchantAvailable'] = false;
        }
        $data['paymentInfo'] = $paymentInfo = $request->all();

        //get only the activated and existing payment methods for the default currency
        //payeer removed
        $data['payment_methods'] = PaymentMethod::where(['status' => 'Active'])->whereNotIn('name', ['Payeer'])->get(['id', 'name'])->toArray();
        $cpmWithoutMts           = CurrencyPaymentMethod::where(['currency_id' => $merchant->currency->id])->where('activated_for', 'like', "%deposit%")->pluck('method_id')->toArray();
        // dd($cpmWithoutMts);
        $paymoney = PaymentMethod::where(['name' => 'Mts'])->first(['id']);
        array_push($cpmWithoutMts, $paymoney->id);
        $data['cpm'] = $cpmWithoutMts;

        //get stripe publishable key from CurrencyPaymentMethod
        $stripe                = PaymentMethod::where(['name' => 'Stripe'])->first(['id']);
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $merchant->currency->id, 'method_id' => $stripe->id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
        if (!empty($currencyPaymentMethod))
        {
            $data['publishable'] = json_decode($currencyPaymentMethod->method_data)->publishable_key;
        }
        return view('merchantPayment.app', $data);
    }

    /*System Merchant Payment Starts*/
    public function mtsPayment(Request $request)
    {
        // dd($request->all());

        $unique_code = unique_code();
        $data        = $request->only('email', 'password');
        $merchantChk = Merchant::find($request->merchant, ['id', 'user_id', 'status', 'fee']);
        // dd($merchantChk);
        $curr = Currency::where('code', $request->currency)->first(['id', 'code']);

        //Deposit + Merchant Fee (starts)
        $checkDepositFeesLimit            = $this->checkDepositFeesPaymentMethod($curr->id, 1, $request->amount, $merchantChk->fee);
        $feeInfoChargePercentage          = $checkDepositFeesLimit['feeInfoChargePercentage'];
        $feeInfoChargeFixed               = $checkDepositFeesLimit['feeInfoChargeFixed'];
        $depositCalcPercentVal            = $checkDepositFeesLimit['depositCalcPercentVal'];
        $depositTotalFee                  = $checkDepositFeesLimit['depositTotalFee'];
        $merchantCalcPercentValOrTotalFee = $checkDepositFeesLimit['merchantCalcPercentValOrTotalFee'];
        $totalFee                         = $checkDepositFeesLimit['totalFee'];
        //Deposit + Merchant Fee (ends)

        try
        {
            \DB::beginTransaction();

            if (!$merchantChk)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', __('Merchant not found!')); //TODO - translations
                return redirect('payment/fail');
            }

            //Check currency exists in system or not
            if (!$curr)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', __('Currency does not exist in the system!')); //TODO - translations
                return redirect('payment/fail');
            }

            if (Auth::attempt($data) && $merchantChk->status == 'Approved')
            {
                //Merchant cannot make payment to himself
                if ($merchantChk->user_id == auth()->user()->id)
                {
                    auth()->logout();
                    \DB::rollBack();
                    $this->helper->one_time_message('error', __('Merchant cannot make payment to himself!'));
                    return redirect('payment/fail');
                }

                $senderWallet = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $curr->id])->first(['id', 'balance']);
                //Check User has the wallet or not
                if (!$senderWallet)
                {
                    auth()->logout();
                    \DB::rollBack();
                    $this->helper->one_time_message('error', __('User does not have the wallet - ') . $curr->code . '. ' . __('Please exchange to wallet - ') . $curr->code . '!'); //TODO - translations
                    return redirect('payment/fail');
                }

                //Check user balance
                if ($senderWallet->balance < $request->amount)
                {
                    auth()->logout();
                    $this->helper->one_time_message('error', __("User does not have sufficient balance!"));
                    return redirect('payment/fail');
                }

                $this->setDefaultSessionValues(); //Set Necessary Session Values

                //MerchantPayment - Add on merchant
                $merchantPayment                    = new MerchantPayment();
                $merchantPayment->merchant_id       = $request->merchant;
                $merchantPayment->currency_id       = $curr->id;
                $merchantPayment->payment_method_id = 1;
                $merchantPayment->user_id           = Auth::user()->id;
                $merchantPayment->gateway_reference = $unique_code;
                $merchantPayment->order_no          = $request->order_no;
                $merchantPayment->item_name         = $request->item_name;
                $merchantPayment->uuid              = $unique_code;
                $merchantPayment->charge_percentage = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee; //new
                $merchantPayment->charge_fixed      = @$feeInfoChargeFixed;                                       //new
                $merchantPayment->amount            = $request->amount - $totalFee;                               //new
                $merchantPayment->total             = $request->amount;
                $merchantPayment->status            = 'Success';
                $merchantPayment->save();

                //Wallet - User - Payment Sent - Amount deducted from user wallet
                $senderWallet->balance = ($senderWallet->balance - $request->amount);
                $senderWallet->save();

                //Transaction - A - Payment_Sent
                $transaction_A                           = new Transaction();
                $transaction_A->user_id                  = Auth::user()->id;
                $transaction_A->end_user_id              = $merchantChk->user_id;
                $transaction_A->currency_id              = $curr->id;
                $transaction_A->payment_method_id        = 1;
                $transaction_A->merchant_id              = $request->merchant;
                $transaction_A->uuid                     = $unique_code;
                $transaction_A->transaction_reference_id = $merchantPayment->id;
                $transaction_A->transaction_type_id      = Payment_Sent;
                $transaction_A->subtotal                 = $request->amount;
                $transaction_A->percentage               = $merchantChk->fee+@$feeInfoChargePercentage; //new
                $transaction_A->charge_percentage        = 0;
                $transaction_A->charge_fixed             = 0;
                $transaction_A->total                    = '-' . ($transaction_A->charge_percentage + $transaction_A->charge_fixed + $transaction_A->subtotal); //new
                $transaction_A->status                   = 'Success';
                $transaction_A->save();

                //Transaction - B - Payment_Received
                $transaction_B                           = new Transaction();
                $transaction_B->user_id                  = $merchantChk->user_id;
                $transaction_B->end_user_id              = Auth::user()->id;
                $transaction_B->currency_id              = $curr->id;
                $transaction_B->payment_method_id        = 1;
                $transaction_B->uuid                     = $unique_code;
                $transaction_B->transaction_reference_id = $merchantPayment->id;
                $transaction_B->transaction_type_id      = Payment_Received;
                $transaction_B->subtotal                 = $request->amount - $totalFee;                                                                //new
                $transaction_B->percentage               = $merchantChk->fee+@$feeInfoChargePercentage;                                                 //new
                $transaction_B->charge_percentage        = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee;                                  //new
                $transaction_B->charge_fixed             = @$feeInfoChargeFixed;                                                                        //new
                $transaction_B->total                    = $transaction_B->charge_percentage + $transaction_B->charge_fixed + $transaction_B->subtotal; //new
                $transaction_B->status                   = 'Success';
                $transaction_B->merchant_id              = $request->merchant;
                $transaction_B->save();

                //Wallet - Merchant - Payment Received
                // $merchantWallet          = Wallet::where(['user_id' => $merchantChk->user_id, 'currency_id' => $curr->id])->first(['id', 'balance']);
                // $merchantWallet->balance = $merchantWallet->balance + ($request->amount - $totalFee); //new
                // $merchantWallet->save();

                //Wallet - Merchant - Payment Received - pm_v2.3
                $merchantWallet = Wallet::where(['user_id' => $merchantChk->user_id, 'currency_id' => $curr->id])->first(['id', 'balance']);
                if (empty($merchantWallet))
                {
                    $wallet              = new Wallet();
                    $wallet->user_id     = $merchantChk->user_id;
                    $wallet->currency_id = $curr->id;
                    $wallet->balance     = ($request->amount - $totalFee); // if wallet does not exist - merchant's wallet is created and balance also added - when user makes a merchant payment
                    $wallet->is_default  = 'No';
                    $wallet->save();
                }
                else
                {
                    $merchantWallet->balance = $merchantWallet->balance + ($request->amount - $totalFee); //new
                    $merchantWallet->save();
                }
                \DB::commit();
                return redirect('payment/success');
            }
            else
            {
                \DB::rollBack();
                return redirect('payment/fail');
            }
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('payment/fail');
        }
    }

    protected function setDefaultSessionValues()
    {
        $preferences = Preference::where('field', '!=', 'dflt_lang')->get();
        if (!empty($preferences))
        {
            foreach ($preferences as $pref)
            {
                $pref_arr[$pref->field] = $pref->value;
            }
        }
        if (!empty($preferences))
        {
            Session::put($pref_arr);
        }

        // default_currency
        $default_currency = Setting::where('name', 'default_currency')->first(['value']);
        if (!empty($default_currency))
        {
            Session::put('default_currency', $default_currency->value);
        }

        //default_timezone
        $default_timezone = User::with(['user_detail:id,user_id,timezone'])->where(['id' => auth()->user()->id])->first(['id'])->user_detail->timezone;
        if (!$default_timezone)
        {
            Session::put('dflt_timezone_user', session('dflt_timezone'));
        }
        else
        {
            Session::put('dflt_timezone_user', $default_timezone);
        }

        // default_language
        $default_language = Setting::where('name', 'default_language')->first(['value']);
        if (!empty($default_language))
        {
            Session::put('default_language', $default_language->value);
        }

        // company_name
        $company_name = Setting::where('name', 'name')->first(['value']);
        if (!empty($company_name))
        {
            Session::put('name', $company_name->value);
        }

        // company_logo
        $company_logo = Setting::where(['name' => 'logo', 'type' => 'general'])->first(['value']);
        if (!empty($company_logo))
        {
            Session::put('company_logo', $company_logo->value);
        }
    }
    /*System Merchant Payment ends*/

    /*Stripe Merchant Payment Starts*/
    public function stripePayment(Request $request)
    {
        // dd($request->merchant);

        $validator = Validator::make($request->all(), [
            'stripeToken' => 'required',
            'amount'      => 'required|numeric',
            'merchant'    => 'required',
        ]);

        $merchantChk = Merchant::find($request->merchant, ['id', 'user_id', 'status', 'fee']);
        if (!$merchantChk)
        {
            $this->helper->one_time_message('error', __('Merchant not found!'));
            return redirect('payment/fail');
        }
        if ($validator->fails() || $merchantChk->status != 'Approved')
        {
            $this->helper->one_time_message('error', 'validation error');
            return redirect('payment/fail');
        }
        $amount                = $request->amount;
        $currencyCode          = $request->currency;
        $merchant              = $request->merchant;
        $item_name             = $request->item_name;
        $order_no              = $request->order_no;
        $unique_code           = unique_code();
        $currency              = Currency::where('code', $currencyCode)->first(['id', 'code']);
        $PaymentMethod         = PaymentMethod::where(['name' => 'Stripe'])->first(['id']);
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currency->id, 'method_id' => $PaymentMethod->id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
        $methodData            = json_decode($currencyPaymentMethod->method_data);

        if (empty($methodData))
        {
            $this->helper->one_time_message('error', 'method data of currency' . $currencyCode . ' not found!');
            return redirect('payment/fail');
        }
        if (isset($request->stripeToken))
        {
            //Payment Received
            $gateway = Omnipay::create('Stripe');
            $gateway->setApiKey($methodData->secret_key);
            $response = $gateway->purchase([
                //Stripe accepts 2 decimal places only(only for server) - if not rounded to 2 decimal places, it will throw error - Amount precision is too high for currency.
                'amount'   => number_format((float) $amount, 2, '.', ''),
                'currency' => $currency->code,
                'token'    => $request->stripeToken,
            ])->send();

            if ($response->isSuccessful())
            {
                $token = $response->getTransactionReference();
                if ($token)
                {
                    //Deposit + Merchant Fee (starts)
                    $checkDepositFeesLimit            = $this->checkDepositFeesPaymentMethod($currency->id, $PaymentMethod->id, $amount, $merchantChk->fee);
                    $feeInfoChargePercentage          = $checkDepositFeesLimit['feeInfoChargePercentage'];
                    $feeInfoChargeFixed               = $checkDepositFeesLimit['feeInfoChargeFixed'];
                    $depositCalcPercentVal            = $checkDepositFeesLimit['depositCalcPercentVal'];
                    $depositTotalFee                  = $checkDepositFeesLimit['depositTotalFee'];
                    $merchantCalcPercentValOrTotalFee = $checkDepositFeesLimit['merchantCalcPercentValOrTotalFee'];
                    $totalFee                         = $checkDepositFeesLimit['totalFee'];
                    //Deposit + Merchant Fee (ends)

                    try
                    {
                        \DB::beginTransaction();

                        //merchantPayment
                        $merchantPayment                    = new MerchantPayment();
                        $merchantPayment->merchant_id       = $merchant;
                        $merchantPayment->currency_id       = $currency->id;
                        $merchantPayment->payment_method_id = $PaymentMethod->id;
                        $merchantPayment->gateway_reference = $token;
                        $merchantPayment->order_no          = $order_no;
                        $merchantPayment->item_name         = $item_name;
                        $merchantPayment->uuid              = $unique_code;
                        $merchantPayment->charge_percentage = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee; //new
                        $merchantPayment->charge_fixed      = $feeInfoChargeFixed;                                        //new
                        $merchantPayment->amount            = $amount - $totalFee;                                        //new
                        $merchantPayment->total             = $amount;
                        $merchantPayment->status            = 'Success';
                        $merchantPayment->save();

                        //transaction
                        $transaction                           = new Transaction();
                        $transaction->user_id                  = $merchantChk->user_id;
                        $transaction->currency_id              = $currency->id;
                        $transaction->payment_method_id        = $PaymentMethod->id;
                        $transaction->merchant_id              = $merchant;
                        $transaction->uuid                     = $unique_code;
                        $transaction->transaction_reference_id = $merchantPayment->id;
                        $transaction->transaction_type_id      = Payment_Received;
                        $transaction->subtotal                 = $amount - $totalFee;                                                                             //new
                        $transaction->percentage               = $merchantChk->fee + $feeInfoChargePercentage;                                                    //new
                        $transaction->charge_percentage        = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee;                                      //new
                        $transaction->charge_fixed             = $feeInfoChargeFixed;                                                                             //new
                        $transaction->total                    = $merchantPayment->charge_percentage + $merchantPayment->charge_fixed + $merchantPayment->amount; //new
                        $transaction->status                   = 'Success';
                        $transaction->save();

                        //Add Amount to Merchant Wallet
                        $merchantWallet = Wallet::where(['user_id' => $merchantChk->user_id, 'currency_id' => $currency->id])->first(['id', 'balance']);
                        if (empty($merchantWallet))
                        {
                            $wallet              = new Wallet();
                            $wallet->user_id     = $merchantChk->user_id;
                            $wallet->currency_id = $currency->id;
                            $wallet->balance     = $merchantPayment->amount; // if wallet does not exist - merchant's wallet is created and balance also added - when user makes a merchant payment
                            $wallet->is_default  = 'No';
                            $wallet->save();
                        }
                        else
                        {
                            $merchantWallet->balance = ($merchantWallet->balance + $merchantPayment->amount);
                            $merchantWallet->save();
                        }

                        \DB::commit();
                        Session::put('merchant_amount', $amount);
                        Session::put('merchant_currency_code', $currencyCode);
                        return redirect('payment/success');
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        $this->helper->one_time_message('error', $e->getMessage());
                        return redirect('payment/fail');
                    }
                }
            }
            else
            {
                $this->helper->one_time_message('error', 'Gateway response problem');
                return redirect('payment/fail');
            }
        }
        else
        {
            $this->helper->one_time_message('error', 'Stripe token not found from request');
            return redirect('payment/fail');
        }
    }
    /*Stripe Merchant Payment Starts*/

    /*PayPal Merchant Payment Starts*/
    public function paypalPayment(Request $request)
    {
        $rules = array(
            'amount'   => 'required|numeric',
            'merchant' => 'required',
        );
        $validator   = Validator::make($request->all(), $rules);
        $merchantChk = Merchant::find($request->merchant, ['id', 'user_id', 'status', 'fee']);
        if (!$merchantChk)
        {
            $this->helper->one_time_message('error', 'Merchant not found');
            return redirect('payment/fail');
        }

        if ($validator->fails() || $merchantChk->status != 'Approved')
        {
            $this->helper->one_time_message('error', 'Validation failed');
            return redirect('payment/fail');
        }
        else
        {
            $amount        = $request->amount;
            $currency      = $request->currency;
            $merchant      = $request->merchant;
            $item_name     = $request->item_name;
            $order_no      = $request->order_no;
            $PaymentMethod = PaymentMethod::where(['name' => 'Paypal'])->first(['id', 'name']);
            $currencyInfo  = Currency::where(['code' => $currency])->first(['id', 'code']);
            if ($currencyInfo)
            {
                $currencyCode = $currencyInfo->code;
            }
            else
            {
                $currencyCode = "USD";
            }
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyInfo->id, 'method_id' => $PaymentMethod->id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
            $methodData            = json_decode($currencyPaymentMethod->method_data);
            if (empty($methodData))
            {
                $this->helper->one_time_message('error', 'For currency' . $currency . ' credential not found!');
                return redirect('payment/fail');
            }
            Session::put('currency', $currencyCode);
            Session::put('currency_id', $currencyInfo->id);
            Session::put('payment_method_id', $PaymentMethod->id);
            Session::put('method', $PaymentMethod->name);
            Session::put('amount', $amount);
            Session::put('merchant', $merchant);
            Session::put('item_name', $item_name);
            Session::put('order_no', $order_no);
            Session::save();

            //paypal setup is a custom function to setup paypal api credentials
            $depo       = new DepositController();
            $apiContext = $depo->paypalSetup($methodData->client_id, $methodData->client_secret, $methodData->mode);
            $payer      = new Payer();
            $payer->setPaymentMethod('paypal');

            $pAmount = new Amount();
            $pAmount->setTotal(number_format($amount, 2, '.', '')); //PayPal accepts 2 decimal places only - if not rounded to 2 decimal places, PayPal will throw error.
            $pAmount->setCurrency($currencyCode);

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($pAmount);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(url("payment/paypal_payment_success"))
                ->setCancelUrl(url("payment/fail"));

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions(array($transaction))
                ->setRedirectUrls($redirectUrls);
            try {
                $payment->create($apiContext);
                return redirect()->to($payment->getApprovalLink());
            }
            catch (PayPalConnectionException $ex)
            {
                $this->helper->one_time_message('error', $ex->getData());
                return redirect('payment/fail');
            }
            return redirect('payment/fail');
        }
    }

    public function paypalPaymentSuccess(Request $request)
    {
        //dd($request->all());
        $unique_code       = unique_code();
        $amount            = Session::get('amount');
        $payment_method_id = Session::get('payment_method_id');
        $merchant          = Session::get('merchant');
        $item_name         = Session::get('item_name');
        $order_no          = Session::get('order_no');
        $currencyId        = Session::get('currency_id');
        $currency          = Session::get('currency');
        // dd($currency);

        // Payment Received
        $merchantInfo = Merchant::find($merchant, ['id', 'user_id', 'fee']);
        if (isset($request->paymentId) && $request->paymentId != null)
        {
            $depo                  = new DepositController();
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $payment_method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
            $methodData            = json_decode($currencyPaymentMethod->method_data);
            if (empty($methodData))
            {
                return redirect('payment/fail');
            }
            $apiContext = $depo->paypalSetup($methodData->client_id, $methodData->client_secret, $methodData->mode);
            $paymentId  = $request->paymentId;
            $payment    = Payment::get($paymentId, $apiContext);
            $execution  = new PaymentExecution();
            $execution->setPayerId($request->PayerID);
            $transaction = new \PayPal\Api\Transaction();
            $amountO     = new Amount();
            $amountO->setCurrency($currency);
            $amountO->setTotal(number_format((float) $amount, 2, '.', ''));
            $transaction->setAmount($amountO);
            try {
                $result = $payment->execute($execution, $apiContext);
                try {
                    $payment = Payment::get($paymentId, $apiContext);
                }
                catch (\Exception $ex)
                {
                    // Log::error($ex);
                    $this->helper->one_time_message('error', $ex);
                    return redirect('payment/fail');
                }
            }
            catch (\Exception $ex)
            {
                // Log::error($ex->getMessage());
                $this->helper->one_time_message('error', $ex->getData());
                return redirect('payment/fail');
            }

            //Deposit + Merchant Fee (starts)
            $checkDepositFeesLimit            = $this->checkDepositFeesPaymentMethod($currencyId, $payment_method_id, $amount, $merchantInfo->fee);
            $feeInfoChargePercentage          = $checkDepositFeesLimit['feeInfoChargePercentage'];
            $feeInfoChargeFixed               = $checkDepositFeesLimit['feeInfoChargeFixed'];
            $depositCalcPercentVal            = $checkDepositFeesLimit['depositCalcPercentVal'];
            $depositTotalFee                  = $checkDepositFeesLimit['depositTotalFee'];
            $merchantCalcPercentValOrTotalFee = $checkDepositFeesLimit['merchantCalcPercentValOrTotalFee'];
            $totalFee                         = $checkDepositFeesLimit['totalFee'];
            //Deposit + Merchant Fee (ends)

            try
            {
                \DB::beginTransaction();

                // dd([$totalFee,$amount]);

                //MerchantPayment
                $merchantPayment                    = new MerchantPayment();
                $merchantPayment->merchant_id       = $merchant;
                $merchantPayment->currency_id       = $currencyId;
                $merchantPayment->payment_method_id = $payment_method_id;
                $merchantPayment->gateway_reference = $payment->id;
                $merchantPayment->order_no          = $order_no;
                $merchantPayment->item_name         = $item_name;
                $merchantPayment->uuid              = $unique_code;
                $merchantPayment->charge_percentage = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee; //new
                $merchantPayment->charge_fixed      = $feeInfoChargeFixed;                                        //new
                $merchantPayment->amount            = $amount - $totalFee;                                        //new
                $merchantPayment->total             = $amount;
                $merchantPayment->status            = 'Success';
                $merchantPayment->save();

                //Transaction
                $transaction                           = new Transaction();
                $transaction->user_id                  = $merchantInfo->user_id;
                $transaction->currency_id              = $currencyId;
                $transaction->payment_method_id        = $payment_method_id;
                $transaction->merchant_id              = $merchant;
                $transaction->uuid                     = $unique_code;
                $transaction->transaction_reference_id = $merchantPayment->id;
                $transaction->transaction_type_id      = Payment_Received;
                $transaction->subtotal                 = $amount - $totalFee;                                                                             //new
                $transaction->percentage               = $merchantInfo->fee + $feeInfoChargePercentage;                                                   //new
                $transaction->charge_percentage        = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee;                                      //new
                $transaction->charge_fixed             = $feeInfoChargeFixed;                                                                             //new
                $transaction->total                    = $merchantPayment->charge_percentage + $merchantPayment->charge_fixed + $merchantPayment->amount; //new
                $transaction->status                   = 'Success';
                $transaction->save();

                //Add amount to merchant Wallet
                // $merchantWallet          = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $currencyId])->first(['id', 'balance']);
                // $merchantWallet->balance = ($merchantWallet->balance + $merchantPayment->amount);
                // $merchantWallet->save();

                //Add Amount to Merchant Wallet - pm_v2.3
                $merchantWallet = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $currencyId])->first(['id', 'balance']);
                if (empty($merchantWallet))
                {
                    $wallet              = new Wallet();
                    $wallet->user_id     = $merchantInfo->user_id;
                    $wallet->currency_id = $currencyId;
                    $wallet->balance     = $merchantPayment->amount; // if wallet does not exist - merchant's wallet is created and balance also added - when user makes a merchant payment
                    $wallet->is_default  = 'No';
                    $wallet->save();
                }
                else
                {
                    $merchantWallet->balance = ($merchantWallet->balance + $merchantPayment->amount);
                    $merchantWallet->save();
                }
                \DB::commit();
                return redirect('payment/success');
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect('payment/fail');
            }
        }
    }
    /*PayPal Merchant Payment ends*/

    /*PayUMoney Merchant Payment Starts*/
    public function payumoney(Request $request)
    {
        // dd(session('payumoney_merchant_currency_code'));
        if (session('payumoney_merchant_currency_code') != 'INR')
        {
            $this->helper->one_time_message('error', __('PayUMoney only supports Indian Rupee(INR)'));
            // Session::flush();
            return redirect('payment/fail');
        }
        else
        {
            $paymentMethod         = PaymentMethod::where(['name' => 'PayUmoney'])->first(['id']);
            $currency              = Currency::where(['code' => session('payumoney_merchant_currency_code')])->first(['id']);
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currency->id, 'method_id' => $paymentMethod->id])->where('activated_for', 'like', "%deposit%")->first();
            if (empty($currencyPaymentMethod))
            {
                return redirect('payment/fail');
            }
            $methodData = json_decode($currencyPaymentMethod->method_data);
                                                                                      // $amount        = Session::get('amount');
                                                                                      // dd($amount);
            $data['amount']    = number_format((float) $request->amount, 2, '.', ''); //Payumoney accepts 2 decimal places only - if not rounded to 2 decimal places, Payumoney will throw.
            $data['mode']      = $methodData->mode;
            $data['key']       = $methodData->key;
            $data['salt']      = $methodData->salt;
            $data['email']     = 'parvez.techvill@gmail.com';
            $data['txnid']     = unique_code();
            $data['firstname'] = 'Parvez';
            Session::put('amount', $request->amount);
            Session::put('merchant', $request->merchant);
            Session::put('item_name', $request->item_name);
            Session::put('order_no', $request->order_no);
            Session::save();
            return view('merchantPayment.payumoney', $data);
        }
    }

    public function payuPaymentSuccess(Request $request)
    {
        if (session('payumoney_merchant_currency_code') !== 'INR')
        {
            $this->helper->one_time_message('error', __('PayUMoney only supports Indian Rupee(INR)'));
            // Session::flush();
            return redirect('payment/fail');
        }
        else
        {
            $paymentMethod = PaymentMethod::where(['name' => 'PayUmoney'])->first(['id']);
            $currency      = Currency::where(['code' => session('payumoney_merchant_currency_code')])->first(['id', 'code']);
            $unique_code   = unique_code();
            $amount        = Session::get('amount');
            $merchant      = Session::get('merchant');
            $item_name     = Session::get('item_name');
            $order_no      = Session::get('order_no');

            // Payment Received
            $merchantInfo = Merchant::find($merchant, ['id', 'user_id', 'fee']);
            if (!$merchantInfo)
            {
                // Session::flush();
                $this->helper->one_time_message('error', __('Merchant not found!'));
                return redirect('payment/fail');
            }

            //Deposit + Merchant Fee (starts)
            $checkDepositFeesLimit            = $this->checkDepositFeesPaymentMethod($currency->id, $paymentMethod->id, $amount, $merchantInfo->fee);
            $feeInfoChargePercentage          = $checkDepositFeesLimit['feeInfoChargePercentage'];
            $feeInfoChargeFixed               = $checkDepositFeesLimit['feeInfoChargeFixed'];
            $depositCalcPercentVal            = $checkDepositFeesLimit['depositCalcPercentVal'];
            $depositTotalFee                  = $checkDepositFeesLimit['depositTotalFee'];
            $merchantCalcPercentValOrTotalFee = $checkDepositFeesLimit['merchantCalcPercentValOrTotalFee'];
            $totalFee                         = $checkDepositFeesLimit['totalFee'];
            //Deposit + Merchant Fee (ends)

            if ($request->all())
            {
                try
                {
                    \DB::beginTransaction();

                    //MerchantPayment
                    $merchantPayment                    = new MerchantPayment();
                    $merchantPayment->merchant_id       = $merchant;
                    $merchantPayment->currency_id       = $currency->id;
                    $merchantPayment->payment_method_id = $paymentMethod->id;
                    $merchantPayment->gateway_reference = $request['key'];
                    $merchantPayment->order_no          = $order_no;
                    $merchantPayment->item_name         = $item_name;
                    $merchantPayment->uuid              = $unique_code;
                    $merchantPayment->charge_percentage = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee; //new
                    $merchantPayment->charge_fixed      = $feeInfoChargeFixed;                                        //new
                    $merchantPayment->amount            = $amount - $totalFee;                                        //new
                    $merchantPayment->total             = $amount;
                    $merchantPayment->status            = 'Success';
                    $merchantPayment->save();

                    //Transaction
                    $transaction                           = new Transaction();
                    $transaction->user_id                  = $merchantInfo->user_id;
                    $transaction->currency_id              = $currency->id;
                    $transaction->payment_method_id        = $paymentMethod->id;
                    $transaction->merchant_id              = $merchant;
                    $transaction->uuid                     = $unique_code;
                    $transaction->transaction_reference_id = $merchantPayment->id;
                    $transaction->transaction_type_id      = Payment_Received;
                    $transaction->subtotal                 = $amount - $totalFee;                                                                             //new
                    $transaction->percentage               = $merchantInfo->fee + $feeInfoChargePercentage;                                                   //new
                    $transaction->charge_percentage        = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee;                                      //new
                    $transaction->charge_fixed             = $feeInfoChargeFixed;                                                                             //new
                    $transaction->total                    = $merchantPayment->charge_percentage + $merchantPayment->charge_fixed + $merchantPayment->amount; //new
                    $transaction->status                   = 'Success';
                    $transaction->save();

                    //Wallet
                    $merchantWallet = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $currency->id])->first(['id', 'balance']);
                    if (empty($merchantWallet))
                    {
                        $wallet              = new Wallet();
                        $wallet->user_id     = $merchantInfo->user_id;
                        $wallet->currency_id = $currency->id;
                        $wallet->balance     = $merchantPayment->amount; // if wallet does not exist - merchant's wallet is created and balance also added - when user makes a merchant payment
                        $wallet->is_default  = 'No';
                        $wallet->save();
                    }
                    else
                    {
                        $merchantWallet->balance = $merchantWallet->balance + $merchantPayment->amount;
                        $merchantWallet->save();
                    }
                    \DB::commit();
                    return redirect('payment/success');
                }
                catch (\Exception $e)
                {
                    \DB::rollBack();
                    // Session::flush();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect('payment/fail');
                }
            }
            else
            {
                return redirect('payment/fail');
            }
        }
    }

    //fixed in pm_v2.3
    public function merchantPayumoneyPaymentFail(Request $request)
    {
        if ($_POST['status'] == 'failure')
        {
            clearActionSession();
            $this->helper->one_time_message('error', __('You have cancelled your payment'));
            return redirect('/');
        }
    }
    /*PayUMoney Merchant Payment Ends*/

    /*CoinPayments Merchant Payment Starts*/
    public function coinPayments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'   => 'required|numeric',
            'merchant' => 'required',
        ]);
        $merchantChk = Merchant::find($request->merchant);
        if (!$merchantChk)
        {
            return redirect('payment/fail');
        }
        if ($validator->fails() || $merchantChk->status != 'Approved')
        {
            return redirect('payment/fail');
        }

        $amount        = $request->amount;
        $currencyCode  = $request->currency;
        $merchant      = $request->merchant;
        $item_name     = $request->item_name;
        $order_no      = $request->order_no;
        $unique_code   = unique_code();
        $currency      = Currency::where('code', $currencyCode)->first(['id', 'code']);
        $PaymentMethod = PaymentMethod::where(['name' => 'Coinpayments'])->first(['id']);

        config(['coinpayment.default_currency' => $currencyCode]);
        $trx['amountTotal'] = $amount;
        $trx['payload']     = [
            'type'     => 'merchant',
            'id'       => $request->merchant,
            'currency' => $currencyCode,
        ];

        $mpInfo = [];
        $mpInfo = [
            'payment_method' => $PaymentMethod->id,
            'currency_id'    => $currency->id,
        ];
        Session::put('transInfo', $mpInfo);

        $link_transaction = CoinPayment::url_payload($trx);
        Session::put('link_transaction', $link_transaction);
        Session::put("currency_id", $currency->id);
        Session::put("currency_code", $currency->code);
        Session::put("payment_method", $PaymentMethod->id);
        Session::put("unique_code", $unique_code);
        Session::put("item_name", $item_name);
        Session::put("order_no", $order_no);
        Session::put("amount", $amount);
        return redirect($link_transaction);
    }

    public function coinPaymentsCheck()
    {
        $coinLog = cointpayment_log_trx::where('status', 0)->get(['id', 'payload', 'status_text', 'status', 'confirmation_at']);
        foreach ($coinLog as $data)
        {
            $obj = json_decode($data->payload);
            // dd($obj);

            if (isset($obj->type) && $obj->type == "merchant" && isset($obj->merchant_payment_id))
            {
                $merchantPayment = MerchantPayment::find($obj->merchant_payment_id);
                if (isset($merchantPayment->gateway_reference))
                {
                    //
                    $session['payment_method'] = $merchantPayment->payment_method_id;
                    $session['currency_id']    = $merchantPayment->currency_id;
                    session(['transInfo' => $session]);
                    //

                    $payment = CoinPayment::api_call('get_tx_info', [
                        'txid' => $merchantPayment->gateway_reference,
                    ]);

                    if ($payment['error'] == "ok")
                    {
                        $result = $payment['result'];
                        if ($result['status'] == 100 || $result['status'] == 2)
                        {
                            try
                            {
                                \DB::beginTransaction();

                                $data->status_text     = $result['status_text'];
                                $data->status          = $result['status'];
                                $data->confirmation_at = ((INT) $result['status'] === 100 || (INT) $result['status'] === 2) ? date('Y-m-d H:i:s', $result['time_completed']) : null;
                                $data->save();

                                //merchantPayment / Payment Received
                                $merchantPayment->status = "Success";
                                $merchantPayment->save();

                                $merchantInfo = Merchant::find($merchantPayment->merchant_id, ['id', 'user_id', 'fee']);
                                if (!empty($merchantInfo))
                                {
                                    //transaction
                                    $transaction = Transaction::where("transaction_reference_id", $obj->merchant_payment_id)->where('transaction_type_id', Payment_Received)->first(['id', 'status']);
                                    if (!empty($transaction))
                                    {
                                        $transaction->status = "Success";
                                        $transaction->save();
                                    }

                                    //Wallet
                                    $merchantWallet = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $merchantPayment->currency_id])->first(['id', 'balance']);
                                    if (!empty($merchantWallet))
                                    {
                                        $merchantWallet->balance = ($merchantWallet->balance + $merchantPayment->amount);
                                        $merchantWallet->save();
                                    }
                                }
                                \DB::commit();
                            }
                            catch (\Exception $e)
                            {
                                \DB::rollBack();
                                $this->helper->one_time_message('error', $e->getMessage());
                                return redirect('payment/fail');
                            }
                        }
                        else if ($result['status'] == 0)
                        {
                            echo "<pre>";
                            echo "Waiting for CoinPayments buyer funds for txid- " . $merchantPayment->gateway_reference;
                            echo "<br>";
                        }
                        else if ($result['status'] < 0)
                        {
                            //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
                            echo "<pre>";
                            echo "Payment Error for txid- " . $merchantPayment->gateway_reference;
                            echo "<br>";
                        }
                        else
                        {
                            echo "<pre>";
                            echo "Payment not complete for txid- " . $merchantPayment->gateway_reference;
                            echo "<br>";
                        }
                    }
                }
            }
        }
    }
    /*CoinPayments Merchant Payment Ends*/

    public function success()
    {
        $data['amount']        = Session::get('merchant_amount');
        $data['currency_code'] = Session::get('merchant_currency_code');
        return view('merchantPayment.success', $data);
    }

    public function fail()
    {
        // dd('merchantPayment.fail');
        return view('merchantPayment.fail');
    }

    /**
     * [Extended Function] - Checks Deposit Fees Of each Payment Method(if fees limit is active) with Merchant fee - starts
     */
    protected function checkDepositFeesPaymentMethod($currencyId, $paymentMethodId, $amount, $merchantFee)
    {
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $paymentMethodId])
            ->first(['charge_percentage', 'charge_fixed', 'has_transaction']);
        if ($feeInfo->has_transaction == "Yes")
        {
            //if fees limit is not active, both merchant fee and deposit fee will be added
            $feeInfoChargePercentage          = @$feeInfo->charge_percentage;
            $feeInfoChargeFixed               = @$feeInfo->charge_fixed;
            $depositCalcPercentVal            = $amount * (@$feeInfoChargePercentage / 100);
            $depositTotalFee                  = $depositCalcPercentVal+@$feeInfoChargeFixed;
            $merchantCalcPercentValOrTotalFee = $amount * ($merchantFee / 100);
            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
        }
        else
        {
            //if fees limit is not active, only merchant fee will be added
            $feeInfoChargePercentage          = 0;
            $feeInfoChargeFixed               = 0;
            $depositCalcPercentVal            = 0;
            $depositTotalFee                  = 0;
            $merchantCalcPercentValOrTotalFee = $amount * ($merchantFee / 100);
            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
        }
        $data = [
            'feeInfoChargePercentage'          => $feeInfoChargePercentage,
            'feeInfoChargeFixed'               => $feeInfoChargeFixed,
            'depositCalcPercentVal'            => $depositCalcPercentVal,
            'depositTotalFee'                  => $depositTotalFee,
            'merchantCalcPercentValOrTotalFee' => $merchantCalcPercentValOrTotalFee,
            'totalFee'                         => $totalFee,
        ];
        return $data;
    }
    /**
     * [Extended Function] - ends
     */

    /*2Checkout Merchant Payment Starts*/
    // public function twoCheckoutPayment(Request $request)
    // {
    //     $paymentMethod         = PaymentMethod::where(['name' => '2Checkout'])->first(['id']);
    //     $currency              = Currency::where(['code' => $request->currency])->first(['id']);
    //     $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currency->id, 'method_id' => $paymentMethod->id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
    //     $methodData            = json_decode($currencyPaymentMethod->method_data);
    //     if (empty($methodData))
    //     {
    //         return redirect('payment/fail');
    //     }
    //     $data['amount']    = number_format((float) $request->amount, 2, '.', ''); //2Checkout accepts 2 decimal places only - if not rounded to 2 decimal places, 2Checkout will throw ERROR CODE:PE103.
    //     $data['seller_id'] = $methodData->seller_id;
    //     $data['currency']  = $request->currency;
    //     $data['item_name'] = $request->item_name;
    //     $data['mode']      = $methodData->mode;
    //     Session::put('amount', $request->amount); //form amount - no restriction in decimal places - full amount show here
    //     Session::put('merchant', $request->merchant);
    //     Session::put('item_name', $request->item_name);
    //     Session::put('order_no', $request->order_no);
    //     Session::put('currency', $request->currency);
    //     Session::save();
    //     return view('merchantPayment.2checkout', $data);
    // }

    // public function twoCheckoutPaymentSuccess(Request $request)
    // {
    //     // dd($request->all());
    //     $paymentMethod = PaymentMethod::where(['name' => '2Checkout'])->first(['id']);
    //     $currency      = Currency::where(['code' => Session::get('currency')])->first(['id']);
    //     $unique_code   = unique_code();
    //     $amount        = Session::get('amount');
    //     $merchant      = Session::get('merchant');
    //     $item_name     = Session::get('item_name');
    //     $order_no      = Session::get('order_no');

    //     // Payment Received
    //     $merchantInfo = Merchant::find($merchant, ['id', 'user_id', 'fee']);

    //     //charge percentage calculation
    //     $p_calc = $amount * ($merchantInfo->fee / 100);

    //     if (empty($merchantInfo))
    //     {
    //         return redirect('payment/fail');
    //     }
    //     if ($request->all())
    //     {
    //         try
    //         {
    //             \DB::beginTransaction();
    //             //MerchantPayment
    //             $merchantPayment                    = new MerchantPayment();
    //             $merchantPayment->merchant_id       = $merchant;
    //             $merchantPayment->gateway_reference = $request['key'];
    //             $merchantPayment->currency_id       = $currency->id;
    //             $merchantPayment->payment_method_id = $paymentMethod->id;
    //             $merchantPayment->uuid              = $unique_code;
    //             $merchantPayment->charge_percentage = $p_calc;
    //             $merchantPayment->charge_fixed      = 0;
    //             $merchantPayment->amount            = $amount - ($p_calc);
    //             $merchantPayment->total             = $amount;
    //             $merchantPayment->item_name         = $item_name;
    //             $merchantPayment->order_no          = $order_no;
    //             $merchantPayment->status            = 'Success';
    //             $merchantPayment->save();

    //             //Transaction
    //             $transaction                           = new Transaction();
    //             $transaction->user_id                  = $merchantInfo->user_id;
    //             $transaction->currency_id              = $currency->id;
    //             $transaction->payment_method_id        = $paymentMethod->id;
    //             $transaction->uuid                     = $unique_code;
    //             $transaction->transaction_reference_id = $merchantPayment->id;
    //             $transaction->transaction_type_id      = Payment_Received;
    //             $transaction->subtotal                 = $amount - ($p_calc);
    //             $transaction->percentage               = $merchantInfo->fee; //fixed
    //             $transaction->charge_percentage        = $p_calc;
    //             $transaction->charge_fixed             = 0;
    //             $transaction->total                    = $merchantPayment->charge_percentage + $merchantPayment->amount;
    //             $transaction->status                   = 'Success';
    //             $transaction->merchant_id              = $merchant;
    //             $transaction->save();

    //             //Wallet
    //             $merchantWallet = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $currency->id])->first(['id', 'balance']);
    //             if (empty($merchantWallet))
    //             {
    //                 $wallet              = new Wallet();
    //                 $wallet->user_id     = $merchantInfo->user_id;
    //                 $wallet->currency_id = $currency->id;
    //                 $wallet->balance     = $merchantPayment->amount;
    //                 $wallet->is_default  = 'No';
    //                 $wallet->save();
    //             }
    //             else
    //             {
    //                 // $merchantWallet->balance = ($merchantWallet->balance + $amount - ($p_calc));
    //                 $merchantWallet->balance = $merchantWallet->balance + ($amount - $p_calc);
    //                 $merchantWallet->save();
    //             }
    //             \DB::commit();
    //             return redirect('payment/success');
    //         }
    //         catch (\Exception $e)
    //         {
    //             \DB::rollBack();
    //             $this->helper->one_time_message('error', $e->getMessage());
    //             return redirect('payment/fail');
    //         }
    //     }
    //     else
    //     {
    //         return redirect('payment/fail');
    //     }
    // }
    /*2Checkout Merchant Payment Ends*/
}
