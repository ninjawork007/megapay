<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\FeesLimit;
use App\Models\Preference;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class ExchangeMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;

    //Exchange Money Starts here
    public function getUserWalletsWithActiveAndHasTransactionCurrency()
    {
        // dd(request()->all());
        $feesLimitCurrency                               = FeesLimit::where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $userCurrencyList                                = array_column(Wallet::where(['user_id' => request('user_id')])->get()->toArray(), 'currency_id');
        $userCurrencyList                                = Currency::whereIn('id', $userCurrencyList)->where(['status' => 'Active'])->get(['id', 'code', 'status']);
        $success['activeHasTransactionUserCurrencyList'] = $activeHasTransactionUserCurrencyList = $this->activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency);

        //Set default wallet as selected - starts
        $defaultWallet                      = Wallet::where(['user_id' => request('user_id'), 'is_default' => 'Yes'])->first(['currency_id']);
        $success['defaultWalletCurrencyId'] = $defaultWallet->currency_id;
        //Set default wallet as selected - ends

        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    //Users Active, Has Transaction and Existing Currency Wallets/list
    public function activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($userCurrencyList as $aCurrency)
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

    public function getWalletsExceptSelectedFromWallet()
    {
        // dd(request()->all());

        $feesLimitCurrency = FeesLimit::where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);

        $activeCurrency = Currency::where('id', '!=', request('currency_id'))->where(['status' => 'Active'])->get(['id', 'code', 'status']);
        // dd($activeCurrency);

        $currencyList = $this->currencyList($activeCurrency, $feesLimitCurrency, request('user_id'));
        // dd($currencyList);

        if ($currencyList)
        {
            return response()->json([
                'currencies' => $currencyList,
                'status'     => true,
            ]);
        }
        else
        {
            return response()->json([
                'currencies' => null,
                'status'     => false,
            ]);
        }
    }

    public function currencyList($activeCurrency, $feesLimitCurrency, $user_id)
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

                    $wallet = Wallet::where(['currency_id' => $aCurrency->id, 'user_id' => $user_id])->first(['balance']);
                    // dd($wallet);
                    if (!empty($wallet))
                    {
                        $selectedCurrency[$aCurrency->id]['balance'] = isset($wallet->balance) ? $wallet->balance : 0.00;
                    }
                }
            }
        }
        return $selectedCurrency;
    }

    public function getBalanceOfFromAndToWallet()
    {
        // dd(request()->all());

        $wallet = Wallet::where(['currency_id' => request('currency_id'), 'user_id' => request('user_id')])->first(['balance', 'currency_id']); //added by parvez - for wallet balance check
        if (!empty($wallet))
        {
            return response()->json([
                'status'       => true,
                'balance'      => number_format((float) $wallet->balance, 2, '.', ''),
                'currencyCode' => $wallet->currency->code,
            ]);
        }
        else
        {
            return response()->json([
                'status'       => false,
                'balance'      => null,
                'currencyCode' => null,
            ]);
        }
    }

    public function exchangeReview()
    {
        // dd(request()->all());
        $amount     = request('amount');
        $fromWallet = request('currency_id');
        $user_id    = request('user_id');

        $wallet      = Wallet::where(['currency_id' => $fromWallet, 'user_id' => $user_id])->first(['currency_id', 'balance']);
        $feesDetails = FeesLimit::where(['transaction_type_id' => Exchange_From, 'currency_id' => $fromWallet])->first(['max_limit', 'min_limit', 'has_transaction', 'currency_id', 'charge_percentage', 'charge_fixed']);

        //Wallet Balance Limit Check Starts here
        if (@$feesDetails)
        {
            if ($feesDetails->has_transaction == 'No')
            {
                $success['reason']       = 'noHasTransaction';
                $success['currencyCode'] = $feesDetails->currency->code;
                $success['message']      = 'The currency' . ' ' . $feesDetails->currency->code . ' ' . 'fees limit is inactive';
                $success['status']       = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
            $checkAmount = $amount + $feesDetails->charge_fixed + $feesDetails->charge_percentage;
        }

        if (@$wallet)
        {
            if ((@$checkAmount) > (@$wallet->balance) || (@$wallet->balance < 0))
            {
                $success['reason']  = 'insufficientBalance';
                $success['message'] = "Sorry, not enough funds to perform the operation!";
                $success['status']  = '401';
                return response()->json(['success' => $success], $this->successStatus);
            }
        }

        //Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['reason']          = 'minLimit';
                $success['minLimit']        = @$feesDetails->min_limit;
                $success['message']         = 'Minimum amount ' . $feesDetails->min_limit;
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
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
                $success['reason']          = 'minMaxLimit';
                $success['minLimit']        = @$feesDetails->min_limit;
                $success['maxLimit']        = @$feesDetails->max_limit;
                $success['message']         = 'Minimum amount ' . $feesDetails->min_limit . ' and Maximum amount ' . $feesDetails->max_limit;
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }

        return response()->json([
            'success' => $success,
        ]);
    }

    public function getCurrenciesExchangeRate(Request $request)
    {
        // dd(request()->all());
        $toWalletCurrency = Currency::where(['id' => request('toWallet')])->first(['exchange_from', 'code', 'rate', 'symbol']);
        if (!empty($toWalletCurrency))
        {
            if ($toWalletCurrency->exchange_from == "local")
            {
                $fromWalletCurrency = Currency::where(['id' => request('fromWallet')])->first(['rate']);
                $defaultCurrency    = Currency::where(['default' => 1])->first(['rate']);
                $toWalletRate       = ($defaultCurrency->rate / $fromWalletCurrency->rate) * $toWalletCurrency->rate;
            }
            else
            {
                $toWalletRate = getCurrencyRate(request('fromWalletCode'), $toWalletCurrency->code);
            }

            //Process amount and destinationCurrencyRate to system decimal - fixed for pm v1.3 mobile app
            $getAmountMoneyFormat    = $this->processExchangeRateWithAmount($toWalletRate, request('amount'));

            $success['status']               = $this->successStatus;
            $success['toWalletRate']         = $toWalletRate;
            $success['toWalletRateHtml']     = formatNumber($toWalletRate); //just for show, not taken for further processing
            $success['toWalletCode']         = $toWalletCurrency->code;
            $success['toWalletSymbol']       = $toWalletCurrency->symbol;
            $success['getAmountMoneyFormat'] = moneyFormat($toWalletCurrency->code, formatNumber($getAmountMoneyFormat)); //just for show, not taken for further processing
            return response()->json(['success' => $success], $this->successStatus);
        }
        else
        {
            $success['status']         = $this->unauthorisedStatus;
            $success['toWalletRate']   = null;
            $success['toWalletCode']   = null;
            $success['toWalletSymbol'] = null;
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    public function reviewExchangeDetails()
    {
        // dd(request()->all());

        $user_id         = request('user_id');
        $amount          = request('amount');
        $fromWalletValue = request('fromWalletValue');
        $toWalletRate    = request('toWalletRate');

        $feesDetails          = FeesLimit::where(['transaction_type_id' => Exchange_From, 'currency_id' => $fromWalletValue])->first(['charge_percentage', 'charge_fixed']);
        $feesChargePercentage = $amount * (@$feesDetails->charge_percentage / 100);
        $totalFess            = $feesChargePercentage + (@$feesDetails->charge_fixed);

        //Process amount and destinationCurrencyRate to system decimal - fixed for pm v1.3 mobile app
        $getAmountMoneyFormat    = $this->processExchangeRateWithAmount($toWalletRate, $amount);

        $success['convertedAmnt']    = $getAmountMoneyFormat; //fixed for pm v1.3 mobile app
        $success['totalAmount']      = $amount + $totalFess;
        $success['totalFees']        = $totalFess;
        $success['totalFeesHtml']    = formatNumber($totalFess);
        $success['toWalletRateHtml'] = formatNumber($toWalletRate);
        $fromCurrency                = Currency::where(['id' => $fromWalletValue])->first(['code', 'symbol']);
        $success['fCurrencySymbol']  = $fromCurrency->symbol;
        $success['fCurrencyCode']    = $fromCurrency->code;
        $success['status']           = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function exchangeMoneyComplete()
    {
        // dd(request()->all());

        $uid                  = request('user_id');
        $fromWalletValue      = request('fromWalletValue');
        $toWalletValue        = request('toWalletValue');
        $toWalletAmount       = request('toWalletAmount');
        $toWalletExchangeRate = request('toWalletExchangeRate');
        $fromWalletAmount     = request('fromWalletAmount');
        $totalFees            = request('totalFees');
        $uuid                 = unique_code();
        // $toWalletAmount       = str_replace(",", "", request('toWalletAmount'));

        $toWallet = Wallet::where(['user_id' => $uid, 'currency_id' => $toWalletValue])->first(['id', 'balance']);
        // dd($toWallet);

        $fromWallet  = Wallet::where(['user_id' => $uid, 'currency_id' => $fromWalletValue])->first(['id', 'currency_id']);
        $feesDetails = FeesLimit::where(['transaction_type_id' => Exchange_From, 'currency_id' => $fromWalletValue])->first(['charge_percentage', 'charge_fixed']);

        try {
            \DB::beginTransaction();

            if (empty($toWallet))
            {
                //To Wallet Create
                $toWalletNew              = new Wallet();
                $toWalletNew->user_id     = $uid;
                $toWalletNew->currency_id = $toWalletValue;
                $toWalletNew->balance     = $toWalletAmount;
                $toWalletNew->is_default  = 'No';
                $toWalletNew->save();
                $to_wallet = $toWalletNew->id;
            }
            else
            {
                //To Wallet Update
                $toWallet->balance = ($toWallet->balance + $toWalletAmount);
                $toWallet->save();
                $to_wallet = $toWallet->id;
            }

            ///Create CurrencyExchange
            $currencyExchange                = new CurrencyExchange();
            $currencyExchange->user_id       = $uid;
            $currencyExchange->from_wallet   = $fromWallet->id;
            $currencyExchange->to_wallet     = $to_wallet;
            $currencyExchange->currency_id   = $toWalletValue;
            $currencyExchange->uuid          = $uuid;
            $currencyExchange->exchange_rate = $toWalletExchangeRate;
            $currencyExchange->amount        = $fromWalletAmount;
            $currencyExchange->fee           = $totalFees;
            $currencyExchange->type          = 'Out';
            $currencyExchange->status        = 'Success';
            $currencyExchange->save();

            // Deduct from base wallet
            $charge_percentage = @$feesDetails->charge_percentage ? $fromWalletAmount * (@$feesDetails->charge_percentage / 100) : 0;
            $charge_fixed      = @$feesDetails->charge_fixed ? @$feesDetails->charge_fixed : 0;
            $tAmnt             = $fromWalletAmount + $charge_percentage + $charge_fixed;

            //From Wallet Update
            $wallet          = Wallet::find($fromWallet->id, ['id', 'balance']);
            $wallet->balance = ($wallet->balance - $tAmnt);
            $wallet->save();

            //Transaction - Exchange_From - Entry
            $exchangeFrom                           = new Transaction();
            $exchangeFrom->user_id                  = $uid;
            $exchangeFrom->currency_id              = $fromWallet->currency_id;
            $exchangeFrom->uuid                     = $uuid;
            $exchangeFrom->transaction_reference_id = $currencyExchange->id;
            $exchangeFrom->transaction_type_id      = Exchange_From;
            $exchangeFrom->subtotal                 = $fromWalletAmount;
            $exchangeFrom->percentage               = @$feesDetails->charge_percentage ? @$feesDetails->charge_percentage : 0;
            $exchangeFrom->charge_percentage        = @$feesDetails->charge_percentage ? $fromWalletAmount * (@$feesDetails->charge_percentage / 100) : 0;
            $exchangeFrom->charge_fixed             = @$feesDetails->charge_fixed ? @$feesDetails->charge_fixed : 0;
            $exchangeFrom->total                    = '-' . ($exchangeFrom->subtotal + $exchangeFrom->charge_percentage + $exchangeFrom->charge_fixed);
            $exchangeFrom->status                   = 'Success';
            $exchangeFrom->uuid                     = $uuid;
            $exchangeFrom->save();

            //Transaction - Exchange_To - Entry
            $exchangeTo                           = new Transaction();
            $exchangeTo->user_id                  = $uid;
            $exchangeTo->currency_id              = $toWalletValue;
            $exchangeTo->uuid                     = $uuid;
            $exchangeTo->transaction_reference_id = $currencyExchange->id;
            $exchangeTo->transaction_type_id      = Exchange_To;
            $exchangeTo->subtotal                 = $toWalletAmount;
            $exchangeTo->percentage               = 0;
            $exchangeTo->charge_percentage        = 0;
            $exchangeTo->charge_fixed             = 0;
            $exchangeTo->total                    = $exchangeTo->subtotal;
            $exchangeTo->status                   = 'Success';
            $exchangeTo->uuid                     = $uuid;
            $exchangeTo->save();

            \DB::commit();

            //For success page
            // $success['fromWalletCode']       = request('fromWalletCode');
            // $success['fromWalletAmount']     = $fromWalletAmount;
            // $success['toWalletCode']         = request('toWalletCode');
            // $success['toWalletAmount']       = $toWalletAmount;
            // $success['toWalletExchangeRate'] = number_format((float) $toWalletExchangeRate, 2, '.', '');
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (Exception $e)
        {
            \DB::rollback();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage(); // echo print_r($e->getMessage());
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    protected function processExchangeRateWithAmount($toWalletRate, $amount)
    {
        $preference              = Preference::where(['category' => 'preference'])->whereIn('field', ['decimal_format_amount'])->get(['field', 'value'])->toArray();
        $preference              = Common::key_value('field', 'value', $preference);
        $destinationCurrencyRate = number_format($toWalletRate, $preference['decimal_format_amount'], ".", ",");
        $amount                  = number_format($amount, $preference['decimal_format_amount'], ".", ",");
        $getAmountMoneyFormat    = $destinationCurrencyRate * $amount;
        return $getAmountMoneyFormat;
    }
    //Exchange Money Ends here
}
