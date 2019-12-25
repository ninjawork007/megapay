<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\FeesLimit;
use App\Models\Preference;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Auth;
use Illuminate\Http\Request;
use Session;
use Validator;

class ExchangeController extends Controller
{

///////////////////////////////////pm - 2.3 starts here///////////////////////////////////////////////////////////////////////////////

    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
    }

    //pm-2.3
    public function exchange()
    {
        setActionSession();
        $data['menu']          = 'exchange';
        $data['content_title'] = 'Exchange';
        $data['icon']          = 'money';

        $feesLimitCurrency = FeesLimit::with('currency:id')->where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        //Users Active, Has Transaction and Existing Currency Wallets/list
        $userCurrencyList                             = array_column(Wallet::with('currency')->where(['user_id' => Auth::user()->id])->get(['currency_id'])->toArray(), 'currency_id');
        $data['userCurrencyList']                     = $userCurrencyList                     = Currency::whereIn('id', $userCurrencyList)->where(['status' => 'Active'])->get(['id', 'code', 'status']);
        $data['activeHasTransactionUserCurrencyList'] = $activeHasTransactionUserCurrencyList = $this->activeHasTransactionUserCurrencyList($userCurrencyList, $feesLimitCurrency);
        // dd($activeHasTransactionUserCurrencyList);

        //pm_v2.3
        $data['defaultWallet'] = $defaultWallet = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);
        // dd($defaultWallet);

        return view('user_dashboard.exchange.create', $data);
    }

    //pm-2.3
    public function amountLimitCheck(Request $request)
    {
        // dd($request->all());
        $amount      = $request->amount;
        $currency_id = $request->currency_id;
        $user_id     = Auth::user()->id;
        $wallet      = Wallet::with('currency:id,code')->where(['currency_id' => $request->currency_id, 'user_id' => $user_id])->first(['currency_id', 'balance']);
        $feesDetails = FeesLimit::with('currency:id,code')->where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $currency_id])
            ->first(['max_limit', 'min_limit', 'has_transaction', 'currency_id', 'charge_percentage', 'charge_fixed']);

        //Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['message']         = __('Minimum amount ') . $feesDetails->min_limit;
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
                $success['message']         = __('Minimum amount ') . $feesDetails->min_limit . __(' and Maximum amount ') . $feesDetails->max_limit;
                $success['wallet_currency'] = $wallet->currency->code;
                $success['status']          = '401';
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
            $curr               = Currency::find($request->currency_id);
            $success['message'] = __('Please check fees limit for the currency ') . $curr->code;
            $success['status']  = '401';
        }
        else
        {
            if ($feesDetails->has_transaction == 'No')
            {
                $success['message'] = __('The currency') . ' ' . $feesDetails->currency->code . ' ' . __('fees limit is inactive');
                $success['status']  = '401';
            }
            else
            {
                $feesPercentage             = $amount * ($feesDetails->charge_percentage / 100);
                $feesFixed                  = $feesDetails->charge_fixed;
                $totalFess                  = $feesPercentage + $feesFixed;
                $totalAmount                = $amount + $totalFess;
                $success['feesPercentage']  = $feesPercentage;
                $success['feesFixed']       = $feesFixed;
                $success['totalFees']       = $totalFess;
                $success['totalFeesHtml']   = formatNumber($totalFess);
                $success['totalAmount']     = $totalAmount;
                $success['pFees']           = $feesDetails->charge_percentage;
                $success['fFees']           = $feesDetails->charge_fixed;
                $success['balance']         = @$wallet->balance ? (@$wallet->balance) : 0;
                $success['wallet_currency'] = $wallet->currency->code;
            }
        }
        //Code for Fees Limit Ends here

        return response()->json([
            'success' => $success,
        ]);
    }

    //pm-2.3
    public function getActiveHasTransactionExceptUsersExistingWalletsCurrencies(Request $request)
    {
        $feesLimitCurrency = FeesLimit::where(['transaction_type_id' => Exchange_From, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        $activeCurrency    = Currency::where('id', '!=', $request->currency_id)->where(['status' => 'Active'])->get(['id', 'code', 'status', 'rate', 'exchange_from']);
        // dd($activeCurrency);
        $currencyList = $this->currencyList($activeCurrency, $feesLimitCurrency);
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

    public function getCurrenciesExchangeRate(Request $request)
    {
        $toWalletCurrency = Currency::where(['id' => $request->toWallet])->first(['exchange_from', 'code', 'rate', 'symbol']);
        // dd($toWalletCurrency->code);

        if (!empty($toWalletCurrency))
        {
            if ($toWalletCurrency->exchange_from == "local")
            {
                // dd("local");
                //fixed below for pm 2.1 (web) and pm android(1.1)
                $fromWalletCurrency      = Currency::where(['id' => $request->fromWallet])->first(['rate']);
                $defaultCurrency         = Currency::where(['default' => 1])->first(['rate']);
                $destinationCurrencyRate = ($defaultCurrency->rate / $fromWalletCurrency->rate) * $toWalletCurrency->rate;
                // dd($destinationCurrencyRate);
            }
            else
            {
                // dd("api");
                $destinationCurrencyRate = getCurrencyRate($request->fromWalletCode, $toWalletCurrency->code);
            }

            //Process amount and destinationCurrencyRate to system decimal format only - starts
            $preference              = Preference::where(['category' => 'preference'])->whereIn('field', ['decimal_format_amount'])->get(['field', 'value'])->toArray();
            $preference              = Common::key_value('field', 'value', $preference);
            $destinationCurrencyRate = number_format($destinationCurrencyRate, $preference['decimal_format_amount'], ".", ",");
            $amount                  = number_format($request->amount, $preference['decimal_format_amount'], ".", ",");
            $getAmountMoneyFormat    = $destinationCurrencyRate * $amount;
            //Process amount and destinationCurrencyRate to system decimal format only - ends

            return response()->json([
                'status'                      => true,
                'destinationCurrencyRate'     => $destinationCurrencyRate,
                'destinationCurrencyRateHtml' => formatNumber($destinationCurrencyRate), //just for show, not taken for further processing
                'destinationCurrencyCode'     => $toWalletCurrency->code,
                'getAmountMoneyFormatHtml'        => moneyFormat($toWalletCurrency->code, formatNumber($getAmountMoneyFormat)), //just for show, not taken for further processing
            ]);
        }
        else
        {
            return response()->json([
                'status'                      => false,
                'destinationCurrencyRate'     => null,
                'destinationCurrencyRateHtml' => null,
                'destinationCurrencyCode'     => null,
                'getAmountMoneyFormat'        => null,
            ]);
        }
    }

    public function getBalanceOfToWallet(Request $request)
    {
        $wallet = Wallet::with('currency:id,code')->where(['currency_id' => $request->currency_id, 'user_id' => auth()->user()->id])->first(['balance', 'currency_id']); //added by parvez - for wallet balance check
        if (!empty($wallet))
        {
            return response()->json([
                'status'       => true,
                'balance'      => formatNumber($wallet->balance),
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

    //pm-2.3
    public function exchangeOfCurrency(Request $request)
    {
        if ($_POST)
        {
            // dd($request->all());

            //backend validation starts
            $from_currency_id = $request->from_currency_id;
            $to_currency_id   = $request->currency_id;

            //temporary swapping
            $request['currency_id']         = $from_currency_id;
            $request['transaction_type_id'] = Exchange_From;

            $amountLimitCheck       = $this->amountLimitCheck($request);
            $request['currency_id'] = $to_currency_id;

            if ($amountLimitCheck->getData()->success->status == 200)
            {
                if (!($amountLimitCheck->getData()->success->totalAmount < $amountLimitCheck->getData()->success->balance))
                {
                    return back()->withErrors(__("Not have enough balance !"))->withInput();
                }

            }
            elseif ($amountLimitCheck->getData()->success->status == 401)
            {
                return back()->withErrors($amountLimitCheck->getData()->success->message)->withInput();
            }
            //backend validation ends

            $uid        = Auth::user()->id;
            $rules      = ['amount' => 'required'];
            $fieldNames = ['amount' => 'Amount'];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $data['fromCurrency'] = $fromCurrency = Currency::where(['id' => $request->from_currency_id])->first(['code', 'symbol']);

                Session::put('destination_exchange_rate', $request->destinationCurrencyRate);

                $data['transInfo'] = $request->all();

                $data['transInfo']['dCurrencyRate']     = $request->destinationCurrencyRate;
                $data['transInfo']['dCurrencyRateHtml'] = formatNumber($request->destinationCurrencyRate); //just for show, not taken for further processing
                $data['transInfo']['currCode']          = $request->destinationCurrencyCode;

                                                                                                            // $data['transInfo']['finalAmount']       = $data['transInfo']['dCurrencyRateHtml'] * $request->amount;
                $data['transInfo']['finalAmount'] = $data['transInfo']['dCurrencyRate'] * $request->amount; //fixed for pm v2.3

                $data['transInfo']['defaultAmnt'] = $request->amount;
                $data['transInfo']['totalAmount'] = ($request->amount) + $request->fee;

                $destinationCurrencyRate = number_format((float) ($request->destinationCurrencyRate), 2, '.', '');
                // dd($destinationCurrencyRate * $request->amount);
                Session::put('finalAmountToWallet', $destinationCurrencyRate * $request->amount);

                session(['transInfo' => $request->all()]);
                return view('user_dashboard.exchange.confirmation', $data);
            }
        }
    }

    //pm-2.3
    public function exchangeOfCurrencyConfirm()
    {
        // dd($request->all());

        actionSessionCheck();

        $sessionValue = session('transInfo');
        // dd($sessionValue);

        $toWalletCurrencyId   = $sessionValue['currency_id'];
        $fromWalletCurrencyId = $sessionValue['from_currency_id'];

        $finalAmount = $sessionValue['finalAmount'];
        //$finalAmount = number_format((float) $finalAmount, 2, '.', '');

        $uid         = Auth::user()->id;
        $chkToWallet = Wallet::where(['user_id' => $uid, 'currency_id' => $toWalletCurrencyId])->first(['id', 'balance']);

        try
        {
            \DB::beginTransaction();

            if (empty($chkToWallet))
            {
                //Create a new wallet
                $toWalletNew              = new Wallet();
                $toWalletNew->user_id     = $uid;
                $toWalletNew->currency_id = $toWalletCurrencyId;
                $toWalletNew->is_default  = 'No';
                $toWalletNew->balance     = $finalAmount;
                $toWalletNew->save();
                $to_wallet = $toWalletNew->id;
            }
            else
            {
                $chkToWallet->balance = ($chkToWallet->balance + $finalAmount);
                $chkToWallet->save();
                $to_wallet = $chkToWallet->id;
            }

            //CurrencyExchange Entry
            $uuid                      = unique_code();
            $data['fromWallet']        = $fromWallet        = Wallet::where(['user_id' => $uid, 'currency_id' => $fromWalletCurrencyId])->first(['id', 'currency_id']);
            $destinationCurrencyExRate = session('destination_exchange_rate');

            ///Create CurrencyExchange
            $currencyExchange                = new CurrencyExchange();
            $currencyExchange->user_id       = $uid;
            $currencyExchange->from_wallet   = $fromWallet->id;
            $currencyExchange->to_wallet     = $to_wallet;
            $currencyExchange->currency_id   = $toWalletCurrencyId;
            $currencyExchange->uuid          = $uuid;
            $currencyExchange->exchange_rate = $destinationCurrencyExRate;
            $currencyExchange->amount        = $sessionValue['amount'];
            $currencyExchange->fee           = $sessionValue['fee'];
            $currencyExchange->type          = 'Out';
            $currencyExchange->status        = 'Success';
            $currencyExchange->save();

            $feesDetails = FeesLimit::where(['transaction_type_id' => Exchange_From, 'currency_id' => $fromWalletCurrencyId])->first(['charge_percentage', 'charge_fixed']);

            $formattedChargePercentage = $sessionValue['amount'] * (@$feesDetails->charge_percentage / 100);
            // $formattedChargePercentage = number_format((float) $formattedChargePercentage, 2, '.', ''); //fix

            //Transaction - Exchange_From - Entry
            $exchangeFrom                           = new Transaction();
            $exchangeFrom->user_id                  = $uid;
            $exchangeFrom->currency_id              = $fromWallet->currency_id;
            $exchangeFrom->uuid                     = $uuid;
            $exchangeFrom->transaction_reference_id = $currencyExchange->id;
            $exchangeFrom->transaction_type_id      = Exchange_From;
            $exchangeFrom->subtotal                 = $sessionValue['amount'];
            $exchangeFrom->percentage               = @$feesDetails->charge_percentage ? @$feesDetails->charge_percentage : 0;
            $exchangeFrom->charge_percentage        = @$feesDetails->charge_percentage ? ($formattedChargePercentage) : 0;
            $exchangeFrom->charge_fixed             = @$feesDetails->charge_fixed ? @$feesDetails->charge_fixed : 0;
            $exchangeFrom->total                    = '-' . ($exchangeFrom->subtotal + $exchangeFrom->charge_percentage + $exchangeFrom->charge_fixed);
            $exchangeFrom->status                   = 'Success';
            $exchangeFrom->uuid                     = $uuid;
            $exchangeFrom->save();

            //Transaction - Exchange_To - Entry
            $exchangeTo                           = new Transaction();
            $exchangeTo->user_id                  = $uid;
            $exchangeTo->currency_id              = $toWalletCurrencyId;
            $exchangeTo->uuid                     = $uuid;
            $exchangeTo->transaction_reference_id = $currencyExchange->id;
            $exchangeTo->transaction_type_id      = Exchange_To;
            $exchangeTo->subtotal                 = $finalAmount;
            $exchangeTo->percentage               = 0;
            $exchangeTo->charge_percentage        = 0;
            $exchangeTo->charge_fixed             = 0;
            $exchangeTo->total                    = $exchangeTo->subtotal;
            $exchangeTo->status                   = 'Success';
            $exchangeTo->uuid                     = $uuid;
            $exchangeTo->save();

            // Deduct from base wallet
            $wallet          = Wallet::find($fromWallet->id, ['id', 'balance']);
            $wallet->balance = ($wallet->balance - ($exchangeFrom->subtotal + $exchangeFrom->charge_percentage + $exchangeFrom->charge_fixed));
            $wallet->save();

            \DB::commit();

            //For success page
            $currency                         = Currency::where(['id' => $toWalletCurrencyId])->first(['code', 'symbol']);
            $data['transInfo']                = $sessionValue;
            $data['transInfo']['defaultAmnt'] = $sessionValue['amount'];

            $data['transInfo']['currCode']          = $currency->code;
            $data['transInfo']['currSymbol']        = $currency->symbol;
            $data['transInfo']['dCurrencyRate']     = $destinationCurrencyExRate;
            $data['transInfo']['dCurrencyRateHtml'] = formatNumber($destinationCurrencyExRate);

            // $data['transInfo']['finalAmount']       = formatNumber($destinationCurrencyExRate) * $sessionValue['amount'];
            $data['transInfo']['finalAmount'] = $destinationCurrencyExRate * $sessionValue['amount'];
            //fixed for pm v2.3

            $data['transInfo']['trans_ref_id'] = $exchangeTo->transaction_reference_id;
            clearActionSession();

            return view('user_dashboard.exchange.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('exchange');
        }
    }

    //pm-2.3
    public function exchangeOfPrintPdf($trans_ref_id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first();

        $data['transactionDetails'] = CurrencyExchange::where(['id' => $trans_ref_id])->first();

        // $data['transactionOfexchangeOf'] = $transactionOfexchangeOf = Transaction::where(['transaction_reference_id' => $trans_ref_id])->first();
        // dd($transactionOfexchangeOf);

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
        $mpdf->WriteHTML(view('user_dashboard.exchange.exchangeOfPaymentPdf', $data));
        $mpdf->Output('sendMoney_' . time() . '.pdf', 'I'); // this will output data
    }

    //Extended Functions below

    //pm-2.3
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

    //pm-2.3
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

                    $wallet = Wallet::where(['currency_id' => $aCurrency->id, 'user_id' => auth()->user()->id])->first(['balance']);
                    if (!empty($wallet))
                    {
                        $selectedCurrency[$aCurrency->id]['balance'] = isset($wallet->balance) ? $wallet->balance : 0.00;
                    }
                }
            }
        }
        return $selectedCurrency;
    }

    //pm-2.3
    public function userCurrencyList($userCurrencyList, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        $i                = 0;
        foreach ($userCurrencyList as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$i]['id']           = $aCurrency->id;
                    $selectedCurrency[$i]['name']         = $aCurrency->name;
                    $selectedCurrency[$i]['symbol']       = $aCurrency->symbol;
                    $selectedCurrency[$i]['code']         = $aCurrency->code;
                    $selectedCurrency[$i]['hundreds_one'] = $aCurrency->hundreds_one;
                    $selectedCurrency[$i]['rate']         = $aCurrency->rate;
                    $selectedCurrency[$i]['logo']         = $aCurrency->logo;
                    $selectedCurrency[$i]['status']       = $aCurrency->status;
                    $selectedCurrency[$i]['default']      = $aCurrency->default;
                    $i++;
                }
            }
        }

        return $selectedCurrency;
    }

///////////////////////////////////pm - 2.3 ends here///////////////////////////////////////////////////////////////////////////////
}
