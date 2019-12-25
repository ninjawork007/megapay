<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\CurrencyPaymentMethod;
use App\Models\Deposit;
use App\Models\FeesLimit;
use App\Models\File;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use PayPal\Api\Amount;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Validator;

class DepositMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;

    //Deposit Money Starts here
    public function getDepositCurrencyList()
    {
        $activeCurrency                     = Currency::where(['status' => 'Active'])->get(['id', 'code', 'status']);
        $feesLimitCurrency                  = FeesLimit::where(['transaction_type_id' => Deposit, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);

        //Set default wallet as selected - starts
        $defaultWallet                      = Wallet::where(['user_id' => request('user_id'), 'is_default' => 'Yes'])->first(['currency_id']);
        $success['defaultWalletCurrencyId'] = $defaultWallet->currency_id;
        //Set default wallet as selected - ends

        $success['currencies']              = $this->currencyList($activeCurrency, $feesLimitCurrency);
        $success['status']                  = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Extended function - 1
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
            }])
            ->where(['transaction_type_id' => $request->transaction_type_id, 'has_transaction' => 'Yes', 'currency_id' => $request->currency_id])
            ->get(['payment_method_id']);

        $currencyPaymentMethods                       = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('activated_for', 'like', "%deposit%")->get(['method_id']);
        $currencyPaymentMethodFeesLimitCurrenciesList = $this->currencyPaymentMethodFeesLimitCurrencies($feesLimits, $currencyPaymentMethods);
        $success['paymentMethods']                    = $currencyPaymentMethodFeesLimitCurrenciesList;
        $success['status']                            = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Extended function - 2
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

    public function getDepositDetailsWithAmountLimitCheck()
    {
        $user_id         = request('user_id');
        $amount          = request('amount');
        $currency_id     = request('currency_id');
        $paymentMethodId = request('paymentMethodId');

        $success['paymentMethodName'] = PaymentMethod::where('id', $paymentMethodId)->first(['name'])->name;
        $wallets                      = Wallet::where(['currency_id' => request('currency_id'), 'user_id' => $user_id])->first(['balance']);

        $feesDetails = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => request('currency_id'), 'payment_method_id' => $paymentMethodId])
            ->first(['charge_percentage', 'charge_fixed', 'min_limit', 'max_limit', 'currency_id']);
        //  dd($feesDetails);

        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['reason']   = 'minLimit';
                $success['minLimit'] = @$feesDetails->min_limit;
                $success['message']  = 'Minimum amount ' . @$feesDetails->min_limit;
                $success['status']   = '401';
                return response()->json(['success' => $success]);
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
                $success['reason']   = 'minMaxLimit';
                $success['minLimit'] = @$feesDetails->min_limit;
                $success['maxLimit'] = @$feesDetails->max_limit;
                $success['message']  = 'Minimum amount ' . @$feesDetails->min_limit . ' and Maximum amount ' . @$feesDetails->max_limit;
                $success['status']   = '401';
                return response()->json(['success' => $success]);
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
            $success['message'] = "ERROR";
            $success['status']  = 401;
        }
        else
        {
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['amount']         = $amount;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['currency_id']    = $feesDetails->currency_id;
            $success['currSymbol']     = $feesDetails->currency->symbol;
            $success['currCode']       = $feesDetails->currency->code;
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = @$wallets->balance ? @$wallets->balance : 0;
            $success['status']         = 200;
        }
        return response()->json(['success' => $success]);
    }

    /**
     * Stripe Starts
     * @return [type] [description]
     */
    //Get Stripe Info
    public function getStripeInfo()
    {
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => request('currency_id'), 'method_id' => request('method_id')])
            ->where('activated_for', 'like', "%deposit%")
            ->first(['method_data']);
        if (empty($currencyPaymentMethod))
        {
            $success['message'] = __('Payment gateway credentials not found!');
            $success['status']  = 401;
        }
        else
        {
            $success['stripe_keys'] = json_decode($currencyPaymentMethod->method_data);
            $success['status']      = 200;
            return response()->json(['success' => $success]);
        }
    }

    //Deposit Confirm Post via Stripe
    public function stripePaymentStore()
    {
        /* dd(request()->all()); */
        $validation = Validator::make(request()->all(), [
            'stripeToken' => 'required',
        ]);
        if ($validation->fails())
        {
            $data['status']  = 401;
            $data['message'] = $validation->errors();
            return response()->json(['success' => $data]);
        }
        $payment_method_id = request('deposit_payment_id');

        $user_id = request('user_id');
        $wallet  = Wallet::where(['currency_id' => request('currency_id'), 'user_id' => $user_id])->first(['id', 'currency_id']);
        try {
            \DB::beginTransaction();

            if (empty($wallet))
            {
                $walletInstance              = new Wallet();
                $walletInstance->user_id     = $user_id;
                $walletInstance->currency_id = request('currency_id');
                $walletInstance->balance     = 0;
                $walletInstance->is_default  = 'No';
                $walletInstance->save();
            }
            $currencyId = isset($wallet->currency_id) ? $wallet->currency_id : $walletInstance->currency_id;
            $currency   = Currency::find($currencyId, ['id', 'code']);
            if ($_POST)
            {
                if (request('stripeToken') != null)
                {
                    $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $currencyId, 'method_id' => $payment_method_id])->where('activated_for', 'like', "%deposit%")->first(['method_data']);
                    $methodData            = json_decode($currencyPaymentMethod->method_data);
                    $totalAmount           = (float) request('totalAmount');
                    $amount                = (float) request('amount');
                    $stripe                = Setting::where(['type' => 'Stripe', 'name' => 'secret'])->first();
                    $gateway               = Omnipay::create('Stripe');
                    $gateway->setApiKey($methodData->secret_key);
                    $response = $gateway->purchase([
                        //Stripe accepts 2 decimal places only(only for server) - if not rounded to 2 decimal places, it will throw error - Amount precision is too high for currency.
                        'amount'   => number_format($totalAmount, 2, '.', ''),
                        'currency' => $currency->code,
                        'token'    => request('stripeToken'),
                    ])->send();

                    if ($response->isSuccessful())
                    {
                        $token         = $response->getTransactionReference();
                        $feeInfo       = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
                        $feePercentage = $amount * ($feeInfo->charge_percentage / 100);

                        //Save to Deposit
                        $uuid                       = unique_code();
                        $deposit                    = new Deposit();
                        $deposit->user_id           = $user_id;
                        $deposit->currency_id       = $currencyId;
                        $deposit->payment_method_id = $payment_method_id;
                        $deposit->uuid              = $uuid;
                        $deposit->charge_percentage = $feePercentage;
                        $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                        $deposit->amount            = $amount;
                        $deposit->status            = 'Success'; //in Stripe deposit, status will be success
                        $deposit->save();

                        //Save to Transaction
                        $transaction                           = new Transaction();
                        $transaction->user_id                  = $user_id;
                        $transaction->currency_id              = $currencyId;
                        $transaction->payment_method_id        = $payment_method_id;
                        $transaction->uuid                     = $uuid;
                        $transaction->transaction_reference_id = $deposit->id;
                        $transaction->transaction_type_id      = Deposit;
                        $transaction->subtotal                 = $deposit->amount;
                        $transaction->percentage               = $feeInfo->charge_percentage;
                        $transaction->charge_percentage        = $feePercentage;
                        $transaction->charge_fixed             = $feeInfo->charge_fixed;
                        $transaction->total                    = ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
                        $transaction->status                   = 'Success';
                        $transaction->save();

                        //Update to Wallet
                        $wallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $currencyId])->first(['id', 'balance']);
                        $wallet->balance = ($wallet->balance + $transaction->subtotal);
                        $wallet->save();

                        \DB::commit();
                        $data['transaction'] = $transaction;
                        $data['status']      = 200;
                        return response()->json(['success' => $data]);
                    }
                    else
                    {
                        $data['status']  = 401;
                        $data['message'] = $validation->errors();
                        return response()->json(['success' => $data]);
                    }
                }
                else
                {
                    $data['status']  = 401;
                    $data['message'] = $validation->errors();
                    return response()->json(['success' => $data]);
                }
            }
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage(); // echo print_r($e->getMessage());
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }
    /**
     * Stripe Ends
     * @return [type] [description]
     */

    /**
     * Paypal Starts
     * @return [type] [description]
     */
    //Get Paypal Info
    public function getPaypalInfo()
    {
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => request('currency_id'), 'method_id' => request('method_id')])
            ->where('activated_for', 'like', "%deposit%")
            ->first(['method_data']);

        if (empty($currencyPaymentMethod))
        {
            $success['message'] = __('Payment gateway credentials not found!');
            $success['status']  = 401;
        }
        else
        {
            $success['method_info'] = json_decode($currencyPaymentMethod->method_data);
            $success['status']      = 200;
            return response()->json(['success' => $success]);
        }
    }

    //Deposit Confirm Post via Paypal
    public function paypalPaymentStore()
    {
        if (request('details')['status'] != 'COMPLETED')
        {
            $success['status']  = 401;
            $success['message'] = __('Unsuccessful Transaction');
            return response()->json(['success' => $success]);
        }

        $amount            = request('amount');
        $currency_id       = request('currencyID');
        $payment_method_id = request('methodID');
        $user_id           = request('userId');
        $uuid              = unique_code();
        $feeInfo           = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currency_id, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
        $wallet            = Wallet::where(['currency_id' => $currency_id, 'user_id' => $user_id])->first(['id', 'balance']);

        try {
            \DB::beginTransaction();

            if (empty($wallet))
            {
                $walletInstance              = new Wallet();
                $walletInstance->user_id     = $user_id;
                $walletInstance->currency_id = $currency_id;
                $walletInstance->balance     = 0;
                $walletInstance->is_default  = 'No';
                $walletInstance->save();
            }
            $feePercentage = $amount * ($feeInfo->charge_percentage / 100);

            //Save to Deposit
            $deposit                    = new Deposit();
            $deposit->user_id           = $user_id;
            $deposit->currency_id       = $currency_id;
            $deposit->payment_method_id = $payment_method_id;
            $deposit->uuid              = $uuid;
            $deposit->charge_percentage = $feePercentage;
            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $deposit->amount            = $amount;
            $deposit->status            = 'Success'; //in paypal deposit, status will be success
            $deposit->save();

            //Save to Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $user_id;
            $transaction->currency_id              = $currency_id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id      = Deposit;
            $transaction->subtotal                 = $deposit->amount;
            $transaction->percentage               = $feeInfo->charge_percentage;
            $transaction->charge_percentage        = $feePercentage;
            $transaction->charge_fixed             = $feeInfo->charge_fixed;
            $transaction->total                    = ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
            $transaction->status                   = 'Success';
            $transaction->payment_method_id        = $payment_method_id;
            $transaction->save();

            //Update to Wallet
            $wallet->balance = ($wallet->balance + $transaction->subtotal);
            $wallet->save();

            \DB::commit();
            $success['transaction'] = $transaction;
            $success['status']      = 200;
            return response()->json(['success' => $success]);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage(); // echo print_r($e->getMessage());
            return response()->json(['success' => $success], $this->unauthorisedStatus);
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

    /**
     * Paypal Ends
     * @return [type] [description]
     */

    /**
     * Bank Starts
     * @return [type] [description]
     */
    public function getDepositBankList()
    {
        $banks                  = Bank::where(['currency_id' => request('currency_id')])->get(['id', 'bank_name', 'is_default', 'account_name', 'account_number']);
        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', request('currency_id'))
            ->where('activated_for', 'like', "%deposit%")
            ->where('method_data', 'like', "%bank_id%")
            ->get(['method_data']);

        $bankList = $this->bankList($banks, $currencyPaymentMethods);
        if (empty($bankList))
        {
            $success['status']  = 401;
            $success['message'] = __('Banks Does Not Exist For Selected Currency!');
        }
        else
        {
            $success['status'] = $this->successStatus;
            $success['banks']  = $bankList;
        }
        return response()->json(['success' => $success], $this->successStatus);
    }

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

    public function getBankDetails()
    {
        $bank = Bank::with('file:id,filename')->where(['id' => request('bank')])->first(['account_name', 'account_number', 'bank_name', 'file_id']);
        if ($bank)
        {
            $success['status'] = 200;
            $success['bank']   = $bank;
            if (!empty($bank->file_id))
            {
                $success['bank_logo'] = $bank->file->filename;
            }
        }
        else
        {
            $success['status'] = 401;
            $success['bank']   = "Bank Not Found!";
        }
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Deposit Confirm Post via Bank
    public function bankPaymentStore()
    {
        // dd(request()->all());
        $uid                  = request('user_id');
        $uuid                 = unique_code();
        $deposit_payment_id   = request('deposit_payment_id');
        $deposit_payment_name = request('deposit_payment_name');
        $currency_id          = request('currency_id');
        $amount               = request('amount');
        $bank_id              = request('bank_id');
        $totalAmount          = request('amount') + request('totalFees');
        $feeInfo              = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currency_id, 'payment_method_id' => $deposit_payment_id])->first(['charge_percentage', 'charge_fixed']);
        $feePercentage        = $amount * ($feeInfo->charge_percentage / 100);

        try {
            \DB::beginTransaction();

            if ($deposit_payment_name == 'Bank')
            {
                // File Entries
                if (request()->hasFile('file'))
                {
                    $fileName     = request()->file('file');
                    $originalName = $fileName->getClientOriginalName();
                    $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                    $file_extn    = strtolower($fileName->getClientOriginalExtension());
                    $path         = 'uploads/files/bank_attached_files';
                    $uploadPath   = public_path($path);
                    $fileName->move($uploadPath, $uniqueName);

                    //File
                    $file               = new File();
                    $file->user_id      = $uid;
                    $file->filename     = $uniqueName;
                    $file->originalname = $originalName;
                    $file->type         = $file_extn;
                    $file->save();
                }
            }

            //Save to Deposit
            $deposit                    = new Deposit();
            $deposit->user_id           = $uid;
            $deposit->currency_id       = $currency_id;
            $deposit->payment_method_id = $deposit_payment_id;
            $deposit->uuid              = $uuid;
            $deposit->charge_percentage = $feePercentage;
            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $deposit->amount            = $amount;
            $deposit->status            = 'Pending'; //in bank deposit, status will be pending
            if ($deposit_payment_name == 'Bank')
            {
                $deposit->bank_id = $bank_id;
                $deposit->file_id = $file->id;
            }
            $deposit->save();

            //Save to Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $uid;
            $transaction->currency_id              = $currency_id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id      = Deposit;
            $transaction->subtotal                 = $deposit->amount;
            $transaction->percentage               = $feeInfo->charge_percentage;
            $transaction->charge_percentage        = $feePercentage;
            $transaction->charge_fixed             = $feeInfo->charge_fixed;
            $transaction->total                    = ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
            $transaction->status                   = 'Pending';
            $transaction->payment_method_id        = $deposit_payment_id;
            if ($deposit_payment_name == 'Bank')
            {
                $transaction->bank_id = $bank_id;
                $transaction->file_id = $file->id;
            }
            $transaction->save();

            $wallet = Wallet::where(['user_id' => $uid, 'currency_id' => $currency_id])->first(['id']);
            if (empty($wallet))
            {
                $wallet              = new Wallet();
                $wallet->user_id     = $uid;
                $wallet->currency_id = $currency_id;
                $wallet->balance     = 0.00; // as initially, transaction status will be pending
                $wallet->is_default  = 'No';
                $wallet->save();
            }
            \DB::commit();
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage(); // echo print_r($e->getMessage());
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }
    /**
     * Bank Ends
     * @return [type] [description]
     */

    //Deposit Money Ends here
}
