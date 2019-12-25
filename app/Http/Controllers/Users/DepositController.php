<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\CurrencyPaymentMethod;
use App\Models\Deposit;
use App\Models\FeesLimit;
use App\Models\File;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Auth;
use CoinPayment;
use Hexters\CoinPayment\Entities\cointpayment_log_trx;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Session;
use Validator;

class DepositController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function create(Request $request)
    {
        // dd('working');

        setActionSession(); //set the session for validate the action

        $data['menu']          = 'deposit';
        $data['content_title'] = 'Deposit';
        $data['icon']          = 'university';

        $activeCurrency             = Currency::where(['status' => 'Active'])->get(['id', 'code', 'status']);
        $feesLimitCurrency          = FeesLimit::where(['transaction_type_id' => Deposit, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $data['activeCurrencyList'] = $this->currencyList($activeCurrency, $feesLimitCurrency);
        $data['defaultWallet']      = $defaultWallet      = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        // if (!empty($request->all()))
        if ($_POST)
        {
            //backend validation starts

            $rules = array(
                'amount'         => 'required',
                'currency_id'    => 'required',
                'payment_method' => 'required',
            );
            $fieldNames = array(
                'amount'         => __("Amount"),
                'currency_id'    => __("Currency"),
                'payment_method' => __("Payment Method"),
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);
            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }

            //backend validation ends
            $currency_id       = $request->currency_id;
            $user_id           = Auth::user()->id;
            $amount            = $request->amount;
            $coinpaymentAmount = $amount;
            Session::put('coinpaymentAmount', $coinpaymentAmount);

            $data['active_currency']    = $activeCurrency    = Currency::where(['status' => 'Active'])->get(['id', 'code', 'status']);
            $feesLimitCurrency          = FeesLimit::where(['transaction_type_id' => Deposit, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
            $data['activeCurrencyList'] = $this->currencyList($activeCurrency, $feesLimitCurrency);
            $data['walletList']         = $activeCurrency;
            $data['payment_met']        = PaymentMethod::where(['status' => 'Active'])->get(['id', 'name']);
            $currency                   = Currency::where(['id' => $currency_id, 'status' => 'Active'])->first(['symbol']);
            $request['currSymbol']      = $currency->symbol;
            $data['payMtd']             = $payMtd             = PaymentMethod::where(['id' => $request->payment_method, 'status' => 'Active'])->first(['name']);
            $request['payment_name']    = $payMtd->name . '.' . 'jpg';
            $request['totalAmount']     = $request['amount'] + $request['fee'];
            session(['transInfo' => $request->all()]);
            $data['transInfo']           = $transInfo           = $request->all();
            $data['transInfo']['wallet'] = $request->currency_id;
            Session::put('payment_method_id', $request->payment_method);
            Session::put('wallet_currency_id', $request->currency_id);

            //Code for FeesLimit starts here
            $feesDetails = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currency_id, 'payment_method_id' => $transInfo['payment_method'], 'has_transaction' => 'Yes'])
                ->first(['min_limit', 'max_limit']);
            if (@$feesDetails->max_limit == null)
            {
                if ((@$amount < @$feesDetails->min_limit))
                {
                    $data['error'] = __('Minimum amount ') . $feesDetails->min_limit;
                    return view('user_dashboard.deposit.create', $data);
                }
            }
            else
            {
                if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                {
                    $data['error'] = __('Minimum amount ') . $feesDetails->min_limit . __(' and Maximum amount ') . $feesDetails->max_limit;
                    return view('user_dashboard.deposit.create', $data);
                }
            }
            //Code for FeesLimit ends here

            if ($payMtd->name == 'Bank')
            {
                $banks                  = Bank::where(['currency_id' => $currency_id])->get(['id', 'bank_name', 'is_default', 'account_name', 'account_number']);
                $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('activated_for', 'like', "%deposit%")->where('method_data', 'like', "%bank_id%")->get(['method_data']);
                $data['banks']          = $bankList          = $this->bankList($banks, $currencyPaymentMethods);
                if (empty($bankList))
                {
                    $this->helper->one_time_message('error', __('Banks Does Not Exist For Selected Currency!'));
                    return redirect('deposit');
                }
                return view('user_dashboard.deposit.bank_confirmation', $data);
            }
            return view('user_dashboard.deposit.confirmation', $data);
        }
        return view('user_dashboard.deposit.create', $data);
    }

    /**
     * [Extended Function] - starts
     */
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
    /**
     * [Extended Function] - ends
     */

    public function bankList($banks, $currencyPaymentMethods)
    {
        $selectedBanks = [];
        $i             = 0;
        foreach ($banks as $bank)
        {
            foreach ($currencyPaymentMethods as $cpm)
            {
                if ($bank->id == json_decode($cpm->method_data)->bank_id)
                {
                    $selectedBanks[$i]['id']             = $bank->id;
                    $selectedBanks[$i]['bank_name']      = $bank->bank_name;
                    $selectedBanks[$i]['is_default']     = $bank->is_default;
                    $selectedBanks[$i]['account_name']   = $bank->account_name;
                    $selectedBanks[$i]['account_number'] = $bank->account_number;
                    $i++;
                }
            }
        }
        return $selectedBanks;
    }

    public function getBankDetailOnChange(Request $request)
    {
        // dd($request->all());
        $bank = Bank::with('file:id,filename')->where(['id' => $request->bank])->first(['bank_name', 'account_name', 'account_number', 'file_id']);
        if ($bank)
        {
            $data['status'] = true;
            $data['bank']   = $bank;

            if (!empty($bank->file_id))
            {
                $data['bank_logo'] = $bank->file->filename;
            }
        }
        else
        {
            $data['status'] = false;
            $data['bank']   = "Bank Not FOund!";
        }
        return $data;
    }

    //getMatchedFeesLimitsCurrencyPaymentMethodsSettingsPaymentMethods
    public function getDepositMatchedFeesLimitsCurrencyPaymentMethodsSettingsPaymentMethods(Request $request)
    {
        $feesLimits = FeesLimit::with([
            'currency'       => function ($query)
            {
                $query->where(['status' => 'Active']);
            },
            'payment_method' => function ($q)
            {
                $q->where(['status' => 'Active']);
            },
        ])
            ->where(['transaction_type_id' => $request->transaction_type_id, 'has_transaction' => 'Yes', 'currency_id' => $request->currency_id])
            ->get(['payment_method_id']);

        $currencyPaymentMethods                       = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('activated_for', 'like', "%deposit%")->get(['method_id']);
        $currencyPaymentMethodFeesLimitCurrenciesList = $this->currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods);
        $success['paymentMethods']                    = $currencyPaymentMethodFeesLimitCurrenciesList;

        return response()->json(['success' => $success]);
    }

    public function currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods)
    {
        $selectedCurrencies = [];
        foreach ($feesLimits as $feesLimit)
        {
            foreach ($currencyPaymentMethods as $currencyPaymentMethod)
            {
                if ($feesLimit->payment_method_id == $currencyPaymentMethod->method_id)
                {
                    $selectedCurrencies[$feesLimit->payment_method_id]['id']   = $feesLimit->payment_method_id;
                    $selectedCurrencies[$feesLimit->payment_method_id]['name'] = $feesLimit->payment_method->name;
                }
            }
        }
        return $selectedCurrencies;
    }

    //getDepositFeesLimit
    public function getDepositFeesLimit(Request $request)
    {
        $amount      = $request->amount;
        $user_id     = Auth::user()->id;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $request->currency_id, 'payment_method_id' => $request->payment_method_id])
            ->first(['min_limit', 'max_limit', 'charge_percentage', 'charge_fixed']);
        // dd($feesDetails);

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
            $success['pFeesHtml']      = formatNumber($feesPercentage); //2.3
            $success['fFeesHtml']      = formatNumber($feesFixed);      //2.3
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
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage); //2.3
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed);      //2.3
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $wallet                    = Wallet::where(['currency_id' => $request->currency_id, 'user_id' => $user_id])->first(['balance']);
            $success['balance']        = @$wallet->balance ? @$wallet->balance : 0;
        }
        return response()->json(['success' => $success]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        //to check action whether action is valid or not
        actionSessionCheck();

        $userid = Auth::user()->id;
        $rules  = array(
            'amount' => 'required|numeric',
        );
        $fieldNames = array(
            'amount' => 'Amount',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $methodId              = $request->method;
            $amount                = $request->amount;
            $PaymentMethod         = PaymentMethod::find($methodId, ['id', 'name']);
            $method                = ucfirst(strtolower($PaymentMethod->name));
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => session('wallet_currency_id'), 'method_id' => $methodId])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
            $methodData            = json_decode($currencyPaymentMethod->method_data);
            if (empty($methodData))
            {
                $this->helper->one_time_message('error', __('Payment gateway credentials not found!'));
                return back();
            }
            Session::put('method', $method);
            Session::put('payment_method_id', $methodId);
            Session::put('amount', $amount);
            Session::save();

            $currencyId = session('wallet_currency_id');
            $currency   = Currency::find($currencyId, ['id', 'code']);
            if ($method == 'Paypal')
            {
                if ($currency)
                {
                    $currencyCode = $currency->code;
                }
                else
                {
                    $currencyCode = "USD";
                }

                //paypal setup is a custom function to setup paypal api credentials
                $apiContext = $this->paypalSetup($methodData->client_id, $methodData->client_secret, $methodData->mode);
                $payer      = new Payer();
                $payer->setPaymentMethod('paypal');

                $amount = new Amount();
                $amount->setTotal(round($request->amount, 3));
                $amount->setCurrency($currencyCode);

                $transaction = new \PayPal\Api\Transaction();
                $transaction->setAmount($amount);

                $redirectUrls = new RedirectUrls();
                $redirectUrls->setReturnUrl(url("deposit/payment_success"))
                    ->setCancelUrl(url("deposit/payment_cancel"));

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
                    // Log::error($ex->getData());
                    $this->helper->one_time_message('error', $ex->getData());
                    return redirect('deposit');
                }
            }
            else if ($method == 'Stripe')
            {
                $publishable = $methodData->publishable_key;
                Session::put('publishable', $publishable);
                return redirect('deposit/stripe_payment');
            }
            else if ($method == 'Skrill')
            {
                return redirect('deposit/skrill_payment');
            }
            else if ($method == '2checkout')
            {
                $transInfo             = Session::get('transInfo');
                $currencyId            = $transInfo['currency_id'];
                $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $methodId])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
                $methodData            = json_decode($currencyPaymentMethod->method_data);
                // dd($methodData->mode);

                $seller_id = $methodData->seller_id;
                Session::put('seller_id', $seller_id);
                Session::put('wallet_currency_id', $currencyId);
                Session::put('2Checkout_mode', $methodData->mode);
                return redirect('deposit/checkout/payment');
            }
            else if ($method == 'Payumoney')
            {
                $transInfo = Session::get('transInfo');
                // dd($transInfo);
                $currencyId            = $transInfo['currency_id'];
                $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $methodId])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
                $methodData            = json_decode($currencyPaymentMethod->method_data);
                Session::put('mode', $methodData->mode);
                Session::put('key', $methodData->key);
                Session::put('salt', $methodData->salt);
                return redirect('deposit/payumoney_payment');
            }
            else if ($method == 'Coinpayments')
            {
                $trx['amountTotal'] = $amount;
                $trx['payload']     = [
                    'type'     => 'deposit',
                    'currency' => $currency->code,
                ];
                changeEnvironmentVariable('coinpayment_currency', $currency->code);
                $link_transaction = CoinPayment::url_payload($trx);
                Session::put('link_transaction', $link_transaction);
                return redirect($link_transaction);
            }
            else if ($method == 'Payeer')
            {
                $transInfo             = Session::get('transInfo');
                $currencyId            = $transInfo['currency_id'];
                $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $methodId])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
                $payeer                = json_decode($currencyPaymentMethod->method_data);
                Session::put('payeer_merchant_id', $payeer->merchant_id);
                Session::put('payeer_secret_key', $payeer->secret_key);
                Session::put('payeer_encryption_key', $payeer->encryption_key);
                Session::put('payeer_merchant_domain', $payeer->merchant_domain);
                return redirect('deposit/payeer/payment');
            }
            // elseif ($method == 'Perfectmoney')
            // {
            //     $transInfo = Session::get('transInfo');

            //     dd($transInfo);

            //     $currencyId            = $transInfo['currency_id'];
            //     $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $methodId])->where('activated_for', 'like', "%deposit%")->first();
            //     $methodData            = json_decode($currencyPaymentMethod->method_data);
            //     Session::put('payee_account', $methodData->account_id);
            //     Session::put('alter_password', $methodData->alter_password);
            //     Session::put('method_id', $methodId);
            //     Session::put('currency_id', $currencyId);
            //     Session::put('user_id', Auth::user()->id);
            //     Session::put('payee_name', Session::get('company_name'));
            //     Session::put('payment_amount', $transInfo['totalAmount']);
            //     Session::put('amount', $transInfo['amount']);
            //     Session::put('payment_units', $currency->code);

            //     return redirect('deposit/perfect_money_payment');
            // }
            else
            {
                $this->helper->one_time_message('error', __('Please check your payment method!'));
            }
            return back();
        }
    }

    /* Start of Stripe */
    public function stripePayment()
    {
        $data['menu']              = 'deposit';
        $data['amount']            = Session::get('amount');
        $data['payment_method_id'] = $method_id = Session::get('payment_method_id');
        $data['content_title']     = 'Deposit';
        $data['icon']              = 'university';
        $sessionValue              = session('transInfo');
        $currencyId                = $sessionValue['currency_id'];
        $currencyPaymentMethod     = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
        $methodData                = json_decode($currencyPaymentMethod->method_data);
        $data['publishable']       = $methodData->publishable_key;
        return view('user_dashboard.deposit.stripe', $data);
    }

    public function stripePaymentStore(Request $request)
    {
        actionSessionCheck();

        $validation = Validator::make($request->all(), [
            'stripeToken' => 'required',
        ]);
        if ($validation->fails())
        {
            return redirect()->back()->withErrors($validation->errors());
        }
        $payment_method_id = Session::get('payment_method_id');
        $amount            = Session::get('amount');
        $sessionValue      = session('transInfo');
        $user_id           = Auth::user()->id;
        $wallet            = Wallet::where(['currency_id' => $sessionValue['currency_id'], 'user_id' => $user_id])->first(['id', 'currency_id']);
        // dd($wallet);
        if (empty($wallet))
        {
            $walletInstance              = new Wallet();
            $walletInstance->user_id     = $user_id;
            $walletInstance->currency_id = $sessionValue['currency_id'];
            $walletInstance->balance     = 0.00000000;
            $walletInstance->is_default  = 'No';
            $walletInstance->save();
        }
        // dd($walletInstance->currency_id);
        $currencyId = isset($wallet->currency_id) ? $wallet->currency_id : $walletInstance->currency_id;
        $currency   = Currency::find($currencyId, ['id', 'code']);
        if ($_POST)
        {
            if (isset($request->stripeToken))
            {
                $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $payment_method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
                $methodData            = json_decode($currencyPaymentMethod->method_data);
                $gateway               = Omnipay::create('Stripe');
                $gateway->setApiKey($methodData->secret_key);
                $response = $gateway->purchase([
                    //Stripe accepts 2 decimal places only(only for server) - if not rounded to 2 decimal places, it will throw error - Amount precision is too high for currency.
                    'amount'   => number_format((float) $amount, 2, '.', ''),
                    'currency' => $currency->code,
                    'token'    => $request->stripeToken,
                ])->send();
                // dd($response);

                if ($response->isSuccessful())
                {
                    $token = $response->getTransactionReference();
                    if ($token)
                    {
                        $uuid       = unique_code();
                        $feeInfo    = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
                        $p_calc     = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100); //correct calc
                        $total_fees = $p_calc+@$feeInfo->charge_fixed;

                        try
                        {
                            \DB::beginTransaction();
                            //Deposit
                            $deposit                    = new Deposit();
                            $deposit->uuid              = $uuid;
                            $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
                            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                            $deposit->amount            = $present_amount            = ($amount - $total_fees);
                            $deposit->status            = 'Success';
                            $deposit->user_id           = $user_id;
                            $deposit->currency_id       = $currencyId;
                            $deposit->payment_method_id = $payment_method_id;
                            $deposit->save();

                            //Transaction
                            $transaction                           = new Transaction();
                            $transaction->user_id                  = $user_id;
                            $transaction->currency_id              = $currencyId;
                            $transaction->payment_method_id        = $payment_method_id;
                            $transaction->transaction_reference_id = $deposit->id;
                            $transaction->transaction_type_id      = Deposit;
                            $transaction->uuid                     = $uuid;
                            $transaction->subtotal                 = $present_amount;
                            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
                            $transaction->charge_percentage        = @$feeInfo->charge_percentage ? $p_calc : 0;
                            $transaction->charge_fixed             = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                            $transaction->total                    = $sessionValue['amount'] + $total_fees;
                            $transaction->status                   = 'Success';
                            $transaction->save();

                            //Wallet
                            $wallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $currencyId])->first(['id', 'balance']);
                            $wallet->balance = ($wallet->balance + $present_amount);
                            $wallet->save();
                            \DB::commit();
                            $data['transaction'] = $transaction;
                            clearActionSession();
                            return view('user_dashboard.deposit.success', $data);
                        }
                        catch (\Exception $e)
                        {
                            \DB::rollBack();
                            $this->helper->one_time_message('error', $e->getMessage());
                            return back();
                        }
                    }
                    else
                    {
                        $this->helper->one_time_message('error', __('Token is missing!'));
                        return back();
                    }
                }
                else
                {
                    $message = $response->getMessage();
                    $this->helper->one_time_message('error', $message);
                    return back();
                }
            }
            else
            {
                $this->helper->one_time_message('error', __('Please try again later!'));
                return back();
            }
        }
    }
    /* End of Stripe */

    /* Start of PayPal */
    public function paypalPaymentSuccess(Request $request)
    {
        actionSessionCheck();
        $method            = Session::get('method');
        $amount            = Session::get('amount');
        $payment_method_id = Session::get('payment_method_id');
        $sessionValue      = session('transInfo');
        $user_id           = Auth::user()->id;
        $wallet            = Wallet::where(['currency_id' => $sessionValue['currency_id'], 'user_id' => $user_id])->first(['id', 'currency_id']);
        if (empty($wallet))
        {
            $walletInstance              = new Wallet();
            $walletInstance->user_id     = $user_id;
            $walletInstance->currency_id = $sessionValue['currency_id'];
            $walletInstance->balance     = 0;
            $walletInstance->is_default  = 'No';
            $walletInstance->save();
        }
        $currencyId = isset($wallet->currency_id) ? $wallet->currency_id : $walletInstance->currency_id;
        $currency   = Currency::find($currencyId, ['id', 'code']);
        if ($currency)
        {
            $currencyCode = $currency->code;
        }
        else
        {
            $currencyCode = "USD";
        }

        if (isset($request->paymentId) && $request->paymentId != null)
        {
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $payment_method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
            $methodData            = json_decode($currencyPaymentMethod->method_data);
            $apiContext            = $this->paypalSetup($methodData->client_id, $methodData->client_secret, $methodData->mode);

            $paymentId = $request->paymentId;
            $payment   = Payment::get($paymentId, $apiContext);

            $execution = new PaymentExecution();
            $execution->setPayerId($request->PayerID);

            $transaction = new \PayPal\Api\Transaction();
            $amountO     = new Amount();
            $amountO->setCurrency($currencyCode);
            $amountO->setTotal(number_format((float) $amount, 2, '.', '')); //PayPal accepts 2 decimal places only - if not rounded to 2 decimal places, PayPal will throw error.
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
                    return redirect('deposit');
                }
            }
            catch (\Exception $ex)
            {
                // Log::error($ex->getMessage());
                $this->helper->one_time_message('error', $ex->getMessage());
                return redirect('deposit');
            }
        }
        else
        {
            // Log::error("User Cancelled the transaction");
            $this->helper->one_time_message('error', __('User Cancelled the transaction!'));
            return redirect('deposit');
        }
        $uuid    = unique_code();
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
        $p_calc  = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100); //correct calc

        try
        {
            \DB::beginTransaction();

            //Deposit
            $deposit                    = new Deposit();
            $deposit->uuid              = $uuid;
            $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $deposit->status            = 'Success';
            $deposit->user_id           = $user_id;
            $deposit->currency_id       = $currencyId;
            $deposit->payment_method_id = $payment_method_id;
            $deposit->amount            = $present_amount            = ($amount - ($p_calc+@$feeInfo->charge_fixed));
            $deposit->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $user_id;
            $transaction->currency_id              = $currencyId;
            $transaction->payment_method_id        = $payment_method_id;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id      = Deposit;
            $transaction->uuid                     = $uuid;
            $transaction->subtotal                 = $present_amount;
            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
            $transaction->charge_percentage        = $deposit->charge_percentage;
            $transaction->charge_fixed             = $deposit->charge_fixed;
            $total_fees                            = $deposit->charge_percentage + $deposit->charge_fixed;
            $transaction->total                    = $sessionValue['amount'] + $total_fees;
            $transaction->status                   = 'Success';
            $transaction->save();

            //Wallet
            $wallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $currencyId])->first(['id', 'balance']);
            $wallet->balance = ($wallet->balance + $present_amount);
            $wallet->save();

            \DB::commit();

            $data['transaction'] = $transaction;
            clearActionSession();
            return view('user_dashboard.deposit.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('deposit');
        }
    }

    public function paypalSetup()
    {
        $numarr = func_num_args();
        if ($numarr > 0)
        {
            $clientID   = func_get_arg(0);
            $secret     = func_get_arg(1);
            $mode       = func_get_arg(2);
            $apicontext = new ApiContext(new OAuthTokenCredential($clientID, $secret));
            $apicontext->setConfig([
                'mode' => $mode,
            ]);
        }
        else
        {
            $credentials = Setting::where(['type' => 'PayPal'])->get();
            $clientID    = $credentials[0]->value;
            $secret      = $credentials[1]->value;
            $apicontext  = new ApiContext(new OAuthTokenCredential($clientID, $secret));
            $apicontext->setConfig([
                'mode' => $credentials[3]->value,
            ]);
        }
        return $apicontext;
    }

    public function paymentCancel()
    {
        clearActionSession();
        $this->helper->one_time_message('error', __('You have cancelled your payment'));
        return back();
    }
    /* End of PayPal */

    /* Start of 2Checkout */
    public function checkoutPayment()
    {
        $data['menu']              = 'deposit';
        $amount                    = Session::get('amount');
        $data['amount']            = number_format((float) $amount, 2, '.', ''); //2Checkout accepts 2 decimal places only - if not rounded to 2 decimal places, 2Checkout will throw ERROR CODE:PE103.
        $data['payment_method_id'] = Session::get('payment_method_id');
        $data['seller_id']         = Session::get('seller_id');
        $currencyId                = Session::get('wallet_currency_id');
        $data['currency']          = Currency::find($currencyId, ['id', 'code']);
        $data['mode']              = Session::get('2Checkout_mode');
        return view('user_dashboard.deposit.2checkout', $data);
    }

    public function checkoutPaymentStore(Request $request)
    {
        // dd($request->all());
        actionSessionCheck();

        $payment_method_id = Session::get('payment_method_id');
        $sessionValue      = session('transInfo');
        $user_id           = Auth::user()->id;
        $wallet            = Wallet::where(['currency_id' => $sessionValue['currency_id'], 'user_id' => $user_id])->first(['id', 'currency_id']);
        if (empty($wallet))
        {
            $walletInstance              = new Wallet();
            $walletInstance->user_id     = $user_id;
            $walletInstance->currency_id = $sessionValue['currency_id'];
            $walletInstance->balance     = 0;
            $walletInstance->is_default  = 'No';
            $walletInstance->save();
        }
        $currencyId = isset($wallet->currency_id) ? $wallet->currency_id : $walletInstance->currency_id;
        if ($request->all())
        {
            $amount     = Session::get('amount');
            $uuid       = unique_code();
            $feeInfo    = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
            $p_calc     = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100);
            $total_fees = $p_calc+@$feeInfo->charge_fixed;

            try
            {
                \DB::beginTransaction();
                //Deposit
                $deposit                    = new Deposit();
                $deposit->user_id           = $user_id;
                $deposit->currency_id       = $currencyId;
                $deposit->payment_method_id = $payment_method_id;
                $deposit->uuid              = $uuid;
                $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
                $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                $deposit->amount            = $present_amount            = $amount - $total_fees;
                $deposit->status            = 'Success';
                $deposit->save();

                //Transaction
                $transaction                           = new Transaction();
                $transaction->user_id                  = $user_id;
                $transaction->currency_id              = $currencyId;
                $transaction->payment_method_id        = $payment_method_id;
                $transaction->transaction_reference_id = $deposit->id;
                $transaction->transaction_type_id      = Deposit;
                $transaction->uuid                     = $uuid;
                $transaction->subtotal                 = $present_amount;
                $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
                $transaction->charge_percentage        = $deposit->charge_percentage;
                $transaction->charge_fixed             = $deposit->charge_fixed;
                $transaction->total                    = $sessionValue['amount'] + $total_fees;
                $transaction->status                   = 'Success';
                $transaction->save();

                //Wallet
                $wallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $currencyId])->first(['id', 'balance']);
                $wallet->balance = ($wallet->balance + $present_amount);
                $wallet->save();

                \DB::commit();
                $data['transaction'] = $transaction;
                clearActionSession();
                return view('user_dashboard.deposit.success', $data);
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect('deposit');
            }
        }
        else
        {
            $this->helper->one_time_message('error', __('Please try again later!'));
            return back();
        }
    }
    /* End of 2Checkout */

    /* Start of Payumoney */
    public function payumoneyPayment()
    {
        $data['menu'] = 'deposit';

        //Check Currency Code - starts - pm_v2.3
        $currency_id  = session('transInfo')['currency_id'];
        $currencyCode = Currency::where(['id' => $currency_id])->first(['code'])->code;
        if ($currencyCode !== 'INR')
        {
            $this->helper->one_time_message('error', __('PayUMoney only supports Indian Rupee(INR)'));
            return redirect('deposit');
        }
        //Check Currency Code - ends - pm_v2.3
        $amount         = session('transInfo')['amount'];//fixed - was getting total - should get amount
        $data['amount'] = number_format((float) $amount, 2, '.', ''); //Payumoney accepts 2 decimal places only - if not rounded to 2 decimal places, Payumoney will throw.

        $data['mode']      = Session::get('mode');
        $data['key']       = Session::get('key');
        $data['salt']      = Session::get('salt');
        $data['email']     = Auth::user()->email;
        $data['txnid']     = unique_code();
        $data['firstname'] = Auth::user()->first_name;
        return view('user_dashboard.deposit.payumoney', $data);
    }

    public function payumoneyPaymentSuccess()
    {
        actionSessionCheck();

        $sessionValue = session('transInfo');
        $user_id      = auth()->user()->id;
        $amount       = Session::get('amount');
        $uuid         = unique_code();

        if ($_POST['status'] == 'success')
        {
            $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
                ->first(['charge_percentage', 'charge_fixed']);
            $p_calc     = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100);
            $total_fees = $p_calc+@$feeInfo->charge_fixed;

            try
            {
                \DB::beginTransaction();
                //Deposit
                $deposit                    = new Deposit();
                $deposit->user_id           = $user_id;
                $deposit->currency_id       = $sessionValue['currency_id'];
                $deposit->payment_method_id = Session::get('payment_method_id');
                $deposit->uuid              = $uuid;
                $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
                $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                $deposit->amount            = $present_amount            = $amount - $total_fees;
                $deposit->status            = 'Success';
                $deposit->save();

                //Transaction
                $transaction                           = new Transaction();
                $transaction->user_id                  = $user_id;
                $transaction->currency_id              = $sessionValue['currency_id'];
                $transaction->payment_method_id        = Session::get('payment_method_id');
                $transaction->transaction_reference_id = $deposit->id;
                $transaction->transaction_type_id      = Deposit;
                $transaction->uuid                     = $uuid;
                $transaction->subtotal                 = $present_amount;
                $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
                $transaction->charge_percentage        = $deposit->charge_percentage;
                $transaction->charge_fixed             = $deposit->charge_fixed;
                $transaction->total                    = $sessionValue['amount'] + $total_fees;
                $transaction->status                   = 'Success';
                $transaction->save();

                //Wallet
                $chkWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $sessionValue['currency_id']])->first(['id', 'balance']);
                if (empty($chkWallet))
                {
                    $wallet              = new Wallet();
                    $wallet->user_id     = $user_id;
                    $wallet->currency_id = $sessionValue['currency_id'];
                    $wallet->balance     = $present_amount;
                    $wallet->is_default  = 'No';
                    $wallet->save();
                }
                else
                {
                    $chkWallet->balance = ($chkWallet->balance + $present_amount);
                    $chkWallet->save();
                }
                \DB::commit();
                $data['transaction'] = $transaction;
                clearActionSession();
                return view('user_dashboard.deposit.success', $data);
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect('deposit');
            }
        }
    }

    public function payumoneyPaymentFail(Request $request)
    {
        // dd($request->all());
        if ($_POST['status'] == 'failure')
        {
            clearActionSession();
            $this->helper->one_time_message('error', __('You have cancelled your payment'));
            return redirect('deposit');
        }
    }
    /* End of Payumoney */

    /* Start of Bank Payment Method */
    public function bankPaymentSuccess(Request $request)
    {
        // dd($request->all());
        actionSessionCheck();

        $sessionValue = session('transInfo');
        $feeInfo      = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
            ->first(['charge_percentage', 'charge_fixed']);
        $uuid   = unique_code();
        $p_calc = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100);

        try
        {
            \DB::beginTransaction();

            //File
            if ($request->hasFile('attached_file'))
            {
                $fileName     = $request->file('attached_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn    = strtolower($fileName->getClientOriginalExtension());
                $path         = 'uploads/files/bank_attached_files';
                $uploadPath   = public_path($path);
                $fileName->move($uploadPath, $uniqueName);

                //File
                $file               = new File();
                $file->user_id      = auth()->user()->id;
                $file->filename     = $uniqueName;
                $file->originalname = $originalName;
                $file->type         = $file_extn;
                $file->save();
            }

            //Deposit
            $deposit                    = new Deposit();
            $deposit->user_id           = auth()->user()->id;
            $deposit->currency_id       = $sessionValue['currency_id'];
            $deposit->payment_method_id = $sessionValue['payment_method'];
            $deposit->bank_id           = $request->bank;
            $deposit->file_id           = $file->id;
            $deposit->uuid              = $uuid;
            $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $deposit->amount            = $sessionValue['amount'];
            $deposit->status            = 'Pending'; //in bank deposit, status will be pending
            $deposit->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = auth()->user()->id;
            $transaction->currency_id              = $sessionValue['currency_id'];
            $transaction->payment_method_id        = $sessionValue['payment_method'];
            $transaction->bank_id                  = $request->bank;
            $transaction->file_id                  = $file->id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id      = Deposit;
            $transaction->subtotal                 = $deposit->amount;
            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
            $transaction->charge_percentage        = $deposit->charge_percentage;
            $transaction->charge_fixed             = $deposit->charge_fixed;
            $transaction->total                    = $sessionValue['amount'] + $deposit->charge_percentage + $deposit->charge_fixed;
            $transaction->status                   = 'Pending'; //in bank deposit, status will be pending
            $transaction->save();

            //Wallet
            $wallet = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $sessionValue['currency_id']])->first(['id']);
            if (empty($wallet))
            {
                $wallet              = new Wallet();
                $wallet->user_id     = auth()->user()->id;
                $wallet->currency_id = $sessionValue['currency_id'];
                $wallet->balance     = 0; // as initially, transaction status will be pending
                $wallet->is_default  = 'No';
                $wallet->save();
            }
            \DB::commit();

            //For print
            $data['transaction'] = $transaction;
            //clearing session
            clearActionSession();
            return view('user_dashboard.deposit.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('deposit');
        }
    }
    /* End of Bank Payment Method */

    /* Start of CoinPayment */
    public function coinpaymentsCheckStatus()
    {
        $coinLog = cointpayment_log_trx::where('status', 0)->get(['id', 'payload', 'payment_id', 'status_text', 'status', 'confirmation_at']);
        foreach ($coinLog as $data)
        {
            $obj = json_decode($data->payload);
            if (isset($obj->type) && $obj->type == "deposit" && isset($obj->deposit_id))
            {

                $deposit                   = Deposit::find($obj->deposit_id, ['id', 'status', 'user_id', 'currency_id', 'payment_method_id', 'amount']);
                $session['payment_method'] = $deposit->payment_method_id;
                $session['currency_id']    = $deposit->currency_id;
                session(['transInfo' => $session]);
                //

                $payment = CoinPayment::api_call('get_tx_info', [
                    'txid' => $data->payment_id,
                ]);
                // dd($payment);

                if ($payment['error'] == "ok")
                {
                    $result = $payment['result'];

                    $data->status_text     = $result['status_text'];
                    $data->status          = $result['status'];
                    $data->confirmation_at = ((INT) $result['status'] === 100) ? date('Y-m-d H:i:s', $result['time_completed']) : null;
                    $data->save();

                    if ($result['status'] == 100 || $result['status'] == 2)
                    {
                        try
                        {
                            \DB::beginTransaction();

                            // payment is complete or queued for nightly payout, success
                            if (!empty($deposit))
                            {
                                $deposit->status = "Success";
                                $deposit->save();
                            }

                            $trans = Transaction::where("transaction_reference_id", $deposit->id)->where('transaction_type_id', Deposit)->first(['id', 'status']);
                            // dd($trans);
                            if (!empty($trans))
                            {
                                $trans->status = "Success";
                                $trans->save();
                            }

                            $wallet = Wallet::where(['user_id' => $deposit->user_id, 'currency_id' => $deposit->currency_id])->first(['id', 'balance']);
                            if (!empty($wallet))
                            {
                                $wallet->balance = ($wallet->balance + $deposit->amount);
                                $wallet->save();
                            }
                            \DB::commit();
                        }
                        catch (\Exception $e)
                        {
                            \DB::rollBack();
                            $this->helper->one_time_message('error', $e->getMessage());
                            return redirect('deposit');
                        }
                    }
                    else if ($result['status'] == 0)
                    {
                        echo "<pre>";
                        echo "Waiting for CoinPayments buyer funds for Payment ID - " . $data->payment_id;
                        echo "<br>";
                    }
                    else if ($result['status'] < 0)
                    {
                        //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
                        echo "<pre>";
                        echo "Payment Error for Payment ID - " . $data->payment_id;
                        echo "<br>";
                    }
                }
            }
        }
    }

    public function coinpaymentsCancel()
    {
        clearActionSession();
        $this->helper->one_time_message('error', __('You have cancelled your payment'));
        return redirect('deposit');
    }
    /* End of CoinPayment */

    /* Start of Payeer */
    public function payeerPayement()
    {
        // dd(session()->all());

        $data['menu']       = 'deposit';
        $amount             = Session::get('amount');
        $transInfo          = Session::get('transInfo');
        $currency           = Currency::where(['id' => $transInfo['currency_id']])->first(['code']);
        $payeer_merchant_id = Session::get('payeer_merchant_id');
        $data['m_shop']     = $m_shop     = $payeer_merchant_id;
        $data['m_orderid']  = $m_orderid  = six_digit_random_number();

        $data['m_amount'] = $m_amount = number_format((float) $amount, 2, '.', ''); //Payeer might throw error, if 2 decimal place amount is not sent to Payeer server
                                                                                    // $data['m_amount'] = $m_amount = "0.01"; // for test purpose

        $data['m_curr']             = $m_curr             = $currency->code;
        $data['form_currency_code'] = $form_currency_code = $currency->code;
        $data['m_desc']             = $m_desc             = base64_encode('Deposit');
        $payeer_secret_key          = Session::get('payeer_secret_key');
        $m_key                      = $payeer_secret_key;
        $arHash                     = array(
            $m_shop,
            $m_orderid,
            $m_amount,
            $m_curr,
            $m_desc,
        );
        $merchantDomain = Session::get('payeer_merchant_domain');
        $arParams       = array(
            'success_url' => url('/') . '/deposit/payeer/payment/success',
            'fail_url'    => url('/') . '/deposit/payeer/payment/fail',
            'status_url'  => url('/') . '/deposit/payeer/payment/status',
            'reference'   => array(
                'email' => auth()->user()->email,
                'name'  => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            ),
            'submerchant' => $merchantDomain,
        );
        $cipher                = 'AES-256-CBC';
        $merchantEncryptionKey = Session::get('payeer_encryption_key');
        $key                   = md5($merchantEncryptionKey . $m_orderid);                                                            //key from (payeer.com->merchant settings->Key for encryption additional parameters)
        $m_params              = @urlencode(base64_encode(openssl_encrypt(json_encode($arParams), $cipher, $key, OPENSSL_RAW_DATA))); // this throws error if '@' symbol is not used
        $arHash[]              = $data['m_params']              = $m_params;
        $arHash[]              = $m_key;
        $data['sign']          = strtoupper(hash('sha256', implode(":", $arHash)));
        return view('user_dashboard.deposit.payeer', $data);
    }

    public function payeerPayementSuccess(Request $request)
    {
        // dd($request->all());
        if (isset($request['m_operation_id']) && isset($request['m_sign']))
        {
            // dd($request->all());
            $payeer_secret_key = Session::get('payeer_secret_key');

            $m_key  = $payeer_secret_key;
            $arHash = array(
                $request['m_operation_id'],
                $request['m_operation_ps'],
                $request['m_operation_date'],
                $request['m_operation_pay_date'],
                $request['m_shop'],
                $request['m_orderid'],
                $request['m_amount'],
                $request['m_curr'],
                $request['m_desc'],
                $request['m_status'],
            );

            //additional parameters
            if (isset($request['m_params']))
            {
                $arHash[] = $request['m_params'];
            }

            $arHash[]  = $m_key;
            $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));

            if ($request['m_sign'] == $sign_hash && $request['m_status'] == 'success')
            {
                actionSessionCheck();
                $sessionValue = session('transInfo');
                // dd($sessionValue);

                $user_id = Auth::user()->id;
                $uuid    = unique_code();
                $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
                    ->first(['charge_percentage', 'charge_fixed']);
                $p_calc            = $sessionValue['amount'] * (@$feeInfo->charge_percentage / 100);
                $total_fees        = $p_calc+@$feeInfo->charge_fixed;
                $payment_method_id = $sessionValue['payment_method'];
                $sessionAmount     = Session::get('amount');
                $amount            = $sessionAmount;

                try
                {
                    \DB::beginTransaction();
                    //Deposit
                    $deposit                    = new Deposit();
                    $deposit->user_id           = Auth::user()->id;
                    $deposit->currency_id       = $sessionValue['currency_id'];
                    $deposit->payment_method_id = $payment_method_id;
                    $deposit->uuid              = $uuid;
                    $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
                    $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                    $deposit->amount            = $present_amount            = ($amount - ($p_calc + (@$feeInfo->charge_fixed)));
                    $deposit->status            = 'Success';
                    $deposit->save();

                    //Transaction
                    $transaction                           = new Transaction();
                    $transaction->user_id                  = Auth::user()->id;
                    $transaction->currency_id              = $sessionValue['currency_id'];
                    $transaction->payment_method_id        = $payment_method_id;
                    $transaction->transaction_reference_id = $deposit->id;
                    $transaction->transaction_type_id      = Deposit;
                    $transaction->uuid                     = $uuid;
                    $transaction->subtotal                 = $present_amount;
                    $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
                    $transaction->charge_percentage        = $deposit->charge_percentage;
                    $transaction->charge_fixed             = $deposit->charge_fixed;
                    $transaction->total                    = $sessionValue['amount'] + $total_fees;
                    $transaction->status                   = 'Success';
                    $transaction->save();

                    //Wallet
                    $chkWallet = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $sessionValue['currency_id']])->first(['id', 'balance']);
                    if (empty($chkWallet))
                    {
                        //if wallet does not exist, create it
                        $wallet              = new Wallet();
                        $wallet->user_id     = auth()->user()->id;
                        $wallet->currency_id = $sessionValue['currency_id'];
                        $wallet->balance     = $deposit->amount;
                        $wallet->is_default  = 'No';
                        $wallet->save();
                    }
                    else
                    {
                        //add deposit amount to existing wallet
                        $chkWallet->balance = ($chkWallet->balance + $deposit->amount);
                        $chkWallet->save();
                    }
                    \DB::commit();
                    $data['transaction'] = $transaction;
                    clearActionSession();
                    return view('user_dashboard.deposit.success', $data);
                }
                catch (\Exception $e)
                {
                    \DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect('deposit');
                }
            }
            else
            {
                // echo $request['m_orderid'] . '|error';
                $this->helper->one_time_message('error', __('Please try again later!'));
                return back();
            }
        }
    }

    public function payeerPayementStatus(Request $request)
    {
        return $request->all();
    }

    public function payeerPayementFail()
    {
        $this->helper->one_time_message('error', __('You have cancelled your payment'));
        return redirect('deposit');
    }
    /* End of Payeer */

    public function depositPrintPdf($trans_id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first(['value']);

        $data['transactionDetails'] = Transaction::with(['payment_method:id,name', 'currency:id,symbol,code'])
            ->where(['id' => $trans_id])
            ->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);

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
        $mpdf->WriteHTML(view('user_dashboard.deposit.depositPaymentPdf', $data));
        $mpdf->Output('sendMoney_' . time() . '.pdf', 'I'); //
    }

    //perfectMoney
    // public function perfectMoneyPayment()
    // {
    //     actionSessionCheck();
    //     $data['menu']          = 'deposit';
    //     $data['payee_account'] = Session::get('payee_account');
    //     $data['payee_name']    = Session::get('payee_name');

    //     // $data['payment_amount'] = Session::get('payment_amount');
    //     // $data['amount']         = Session::get('amount');

    //     $data['payment_amount'] = "0.01";
    //     $data['amount']         = "0.01";

    //     $data['payment_units'] = Session::get('payment_units');
    //     $data['method_id']     = Session::get('method_id');
    //     $data['currency_id']   = Session::get('currency_id');
    //     $data['user_id']       = Session::get('user_id');
    //     clearActionSession();
    //     return view('user_dashboard.deposit.perfect_money', $data);
    // }

    // public function perfectMoneyIpnCheck()
    // {
    //     $methodid          = $_POST['methodid'];
    //     $currencyid        = $_POST['currencyid'];
    //     $userid            = $_POST['userid'];
    //     $amountwithoutfees = $_POST['amountwithoutfees'];

    //     $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyid, 'method_id' => $methodid])->where('activated_for', 'like', "%deposit%")->first();
    //     $methodData            = json_decode($currencyPaymentMethod->method_data);
    //     $alternate             = strtoupper(md5($methodData->alter_password));

    //     $hash_string =
    //         $_POST['PAYMENT_ID'] . ':' . $_POST['PAYEE_ACCOUNT'] . ':' .
    //         $_POST['PAYMENT_AMOUNT'] . ':' . $_POST['PAYMENT_UNITS'] . ':' .
    //         $_POST['PAYMENT_BATCH_NUM'] . ':' .
    //         $_POST['PAYER_ACCOUNT'] . ':' . $alternate . ':' .
    //         $_POST['TIMESTAMPGMT'];

    //     $hash = strtoupper(md5($hash_string));
    //     if ($hash == $_POST['V2_HASH'])
    //     {
    //         $wallet = Wallet::where(['currency_id' => $currencyid, 'user_id' => $userid])->first();
    //         if (empty($wallet))
    //         {
    //             $wallet              = new Wallet();
    //             $wallet->user_id     = $userid;
    //             $wallet->currency_id = $currencyid;
    //             $wallet->balance     = 0;
    //             $wallet->is_default  = 'No';
    //             $wallet->save();

    //         }
    //         $currencyId = isset($wallet->currency_id) ? $wallet->currency_id : $wallet->currency_id;

    //         $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId])->first();
    //         $uuid    = unique_code();

    //         $deposit                    = new Deposit();
    //         $deposit->uuid              = $uuid;
    //         $deposit->charge_percentage = @$feeInfo->charge_percentage ? ((($amountwithoutfees)) * (@$feeInfo->charge_percentage / 100)) : 0;
    //         $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
    //         $p_calc                     = ((($amountwithoutfees)) * (@$feeInfo->charge_percentage) / 100);
    //         $p_calc = number_format((float) $p_calc, 2, '.', ''); //fix

    //         $sessionAmount     = $_POST['PAYMENT_AMOUNT'];
    //         $amount            = ($sessionAmount);
    //         $payment_method_id = $methodid;

    //         $deposit->amount            = $present_amount            = ($amount - ($p_calc + (@$feeInfo->charge_fixed)));
    //         $deposit->status            = 'Success';
    //         $deposit->user_id           = $userid;
    //         $deposit->currency_id       = $currencyId;
    //         $deposit->payment_method_id = $payment_method_id;
    //         $deposit->save();

    //         $total_fees = (($amountwithoutfees) * (@$feeInfo->charge_percentage / 100) + (@$feeInfo->charge_fixed));

    //         $subtotal = ($amount - $total_fees);

    //         $transaction                           = new Transaction();
    //         $transaction->user_id                  = $userid;
    //         $transaction->currency_id              = $currencyId;
    //         $transaction->payment_method_id        = $payment_method_id;
    //         $transaction->transaction_reference_id = $deposit->id;
    //         $transaction->transaction_type_id      = Deposit;
    //         $transaction->uuid                     = $uuid;
    //         $transaction->subtotal                 = $present_amount;
    //         $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
    //         $transaction->charge_percentage        = $deposit->charge_percentage;
    //         $transaction->charge_fixed             = $deposit->charge_fixed;
    //         $transaction->total                    = ($amountwithoutfees) + $total_fees;
    //         $transaction->status                   = 'Success';
    //         $transaction->save();

    //         $wallet          = Wallet::where(['user_id' => $userid, 'currency_id' => $currencyId])->first();
    //         $wallet->balance = ($wallet->balance + $present_amount);
    //         $wallet->save();
    //     }
    //     else
    //     {
    //         \Log::channel('perfectMoney')->error(serialize($_POST));
    //     }
    // }

    // public function perfectMoneySuccess(Request $request)
    // {
    //     $this->helper->one_time_message('success', __('Your payment was successfull'));
    //     return redirect('deposit');
    // }

    // public function perfectMoneyFail()
    // {

    //     clearActionSession();
    //     $this->helper->one_time_message('error', __('You have cancelled your payment'));
    //     return redirect('deposit');
    // }
    //perfectMoney
}
