<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Country;
use App\Models\Currency;
use App\Models\FeesLimit;
use App\Models\PaymentMethod;
use App\Models\PayoutSetting;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\WithdrawalDetail;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Validator;

class WithdrawalController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    //Payout Setting starts
    //Payout Setting -  starts
    public function payouts()
    {
        setActionSession();
        $data['menu']    = 'payout';
        $data['payouts'] = Withdrawal::with(['payment_method:id,name', 'withdrawal_detail:id,withdrawal_id,account_name,account_number,bank_name', 'currency:id,code'])//optimized by parvez
            ->where(['user_id' => Auth::user()->id])->orderBy('withdrawals.created_at', 'desc')
            ->select('id', 'created_at', 'payment_method_id', 'amount', 'subtotal', 'currency_id', 'status', 'payment_method_info')
            ->paginate(10);

        // if no payout setting
        $data['payoutSettings'] = PayoutSetting::where(['user_id' => auth()->user()->id])->get(['id']);
        return view('user_dashboard.withdrawal.payouts', $data);
    }

    public function payoutSetting()
    {
        $data['menu']           = 'payout';
        $data['payoutSettings'] = PayoutSetting::with(['paymentMethod:id,name'])
        ->where(['user_id' => Auth::user()->id])
        ->select('id', 'type', 'email', 'account_number', 'account_name', 'bank_name')//optimized by parvez
        ->paginate(10);
        $data['countries']      = Country::get(['id', 'name']);
        $data['paymentMethods'] = PaymentMethod::whereNotIn('name', ['Mts', 'Stripe', '2Checkout', 'PayUMoney', 'Coinpayments', 'Payeer'])->where(['status' => 'Active'])->get(['id', 'name']); //mod by parvez
        return view('user_dashboard.withdrawal.payoutSetting', $data);
    }

    public function payoutSettingStore(Request $request)
    {

        $rules = array(
            'type'  => 'required',
            'email' => 'nullable|email',
        );
        $fieldNames = array(
            'type'  => 'Type',
            'email' => 'Email',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {

            $type                   = $request->type;
            $payoutSetting          = new PayoutSetting();
            $payoutSetting->type    = $type;
            $payoutSetting->user_id = Auth::user()->id;
            if ($type == 6)
            {
                $payoutSetting->account_name        = $request->account_name;
                $payoutSetting->account_number      = $request->account_number;
                $payoutSetting->swift_code          = $request->swift_code;
                $payoutSetting->bank_name           = $request->bank_name;
                $payoutSetting->bank_branch_name    = $request->branch_name;
                $payoutSetting->bank_branch_city    = $request->branch_city;
                $payoutSetting->bank_branch_address = $request->branch_address;
                $payoutSetting->country             = $request->country;
            }
            elseif ($type == 8)
            {
                $payoutSetting->account_number = $request->payeer_account_no;
            }
            elseif ($type == 9)
            {
                $payoutSetting->account_number = $request->perfect_money_account_no;
            }
            else
            {
                $payoutSetting->email = $request->email;
            }
            $payoutSetting->save();

            $this->helper->one_time_message('success', __('Payout Setting Created Successfully!'));
            return back();
        }
    }

    public function payoutSettingUpdate(Request $request)
    {
        $id      = $request->setting_id;
        $setting = PayoutSetting::find($id);
        if (!$setting)
        {
            $this->helper->one_time_message('error', __('Payout Setting not found !'));
            return back();
        }
        if ($setting->type == 6)
        {
            $setting->account_name        = $request->account_name;
            $setting->account_number      = $request->account_number;
            $setting->bank_branch_name    = $request->bank_name;
            $setting->bank_branch_city    = $request->branch_city;
            $setting->bank_branch_address = $request->branch_address;
            $setting->country             = $request->country;
            $setting->swift_code          = $request->swift_code;
            $setting->bank_name           = $request->bank_name;
        }
        elseif ($setting->type == 8)
        {
            $setting->account_number = $request->payeer_account_no;
        }
        elseif ($setting->type == 9)
        {
            $setting->account_number = $request->perfect_money_account_no;
        }
        else
        {
            $setting->email = $request->email;
        }
        $setting->save();

        $this->helper->one_time_message('success', __('Payout Setting Updated Successfully!'));
        return back();
    }

    public function payoutSettingDestroy(Request $request)
    {
        $id = $request->id;
        //used auth to verify payout of auth user
        $payout = auth()->user()->payoutSettings->where('id', $id)->first();
        $payout->delete();

        $this->helper->one_time_message('success', __('Payout Setting Deleted Successfully!'));
        return back();
    }
    //Payout Setting - ends
//Payout Setting ends

    //Payout - starts
    public function withdrawalCreate(Request $request)
    {
        setActionSession();
        $data['menu'] = 'withdrawal';

        if (!$_POST)
        {
            $data['payment_methods'] = $payment_methods = PayoutSetting::with(['paymentMethod:id,name'])
            ->where(['user_id' => Auth::user()->id])
            ->get(['id','type','email','account_name','account_number', 'bank_name']);
            // dd($payment_methods);

            $data['defaultCurrency'] = Wallet::where('user_id', Auth::user()->id)->where('is_default', 'Yes')->first(['id', 'currency_id']);
            return view('user_dashboard.withdrawal.create', $data);
        }
        else
        {
            // dd($request->all());
            $rules = array(
                'amount'            => 'required|numeric|check_wallet_balance',
                'payout_setting_id' => 'required',
                'currency_id'       => 'required',
            );
            $fieldNames = array(
                'amount'            => 'Amount',
                'payout_setting_id' => 'Payment method',
                'currency_id'       => 'Currency',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                //backend validation starts
                $request['transaction_type_id'] = Withdrawal;
                $myResponse = $this->withdrawalAmountLimitCheck($request);
                // dd($myResponse->getData()->success->status);
                if ($myResponse)
                {
                    if ($myResponse->getData()->success->status == 200)
                    {
                        if($myResponse->getData()->success->totalAmount > $myResponse->getData()->success->balance){
                            return back()->withErrors(__("Not have enough balance !"))->withInput();
                        }
                    }
                    elseif ($myResponse->getData()->success->status == 401)
                    {
                        return back()->withErrors($myResponse->getData()->success->message)->withInput();
                    }
                }
                //backend valdation ends


                $wallet = Wallet::with(['currency:id,symbol'])->where(['user_id' => auth()->user()->id, 'currency_id' => $request->currency_id])->first(['currency_id']);
                if ($wallet)
                {
                    $data['transInfo']['currSymbol'] = $wallet->currency->symbol;
                    $data['transInfo']['amount']     = $request->amount;

                    $feesInfo = FeesLimit::where(['transaction_type_id' => Withdrawal, 'currency_id' => $wallet->currency_id, 'payment_method_id' => $request->payment_method_id])
                        ->first(['charge_percentage', 'charge_fixed']);
                    // dd($feesInfo);

                    $percentageCalc = $request->amount * ($feesInfo->charge_percentage / 100);
                    $fee = $percentageCalc + $feesInfo->charge_fixed;


                    $data['transInfo']['fee']            = $fee;
                    $data['transInfo']['totalAmount']    = $request->amount + $fee;
                    $data['transInfo']['payout_setting'] = $payout_setting = PayoutSetting::find($request->payout_setting_id);
                    // dd($payout_setting->paymentMethod->name);

                    //saving in sessions
                    Session::put('payout_setting_id', $request->payout_setting_id);
                    Session::put('currency_id', $request->currency_id);
                    Session::put('totalAmount', $request->amount + $fee);
                    Session::put('amount', $request->amount);
                    Session::put('payment_method_info', $request->payment_method_info);
                    Session::put('payment_method_id', $request->payment_method_id); //new

                    return view('user_dashboard.withdrawal.confirmation', $data);
                }
            }
        }
    }

    //get Withdrawal FeesLimits Active Currencies
    public function getWithdrawalFeesLimitsActiveCurrencies(Request $request)
    {
        $payment_met_id      = $request->payment_method_id;
        $transaction_type_id = $request->transaction_type_id;

        $wallets = Wallet::where(['user_id' => auth()->user()->id])->whereHas('active_currency', function ($q) use ($payment_met_id, $transaction_type_id)
        {
            $q->whereHas('fees_limit', function ($query) use ($payment_met_id, $transaction_type_id)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', $transaction_type_id)->where('payment_method_id', $payment_met_id);
            });
        })
            ->with(['active_currency:id,code', 'active_currency.fees_limit:id,currency_id']) //Optimized
            ->get(['currency_id', 'is_default']);
        // dd($wallets);

        $arr        = [];
        $currencies = $wallets->map(function ($wallet) //map acts as foreach but we can customize the index as preferred
            {
                $arr['id']             = $wallet->active_currency->id;
                $arr['code']           = $wallet->active_currency->code;
                $arr['default_wallet'] = $wallet->is_default;
                return $arr;
            });
        // dd($currencies);
        $success['currencies'] = $currencies;
        return response()->json(['success' => $success]);
    }

    //Code for withdrawal Amount Limit Check
    public function withdrawalAmountLimitCheck(Request $request)
    {
        // dd($request->all());

        $amount      = $request->amount;
        $user_id     = Auth::user()->id;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'payment_method_id' => $request->payment_method_id, 'currency_id' => $request->currency_id])
            ->first(['max_limit', 'min_limit', 'charge_percentage', 'charge_fixed']);
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
            $success['totalHtml']      = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesPercentage;
            $success['fFees']          = $feesFixed;
            $success['pFeesHtml']      = formatNumber($feesPercentage);
            $success['fFeesHtml']      = formatNumber($feesFixed);
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
            $success['totalHtml']      = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage);
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $wallet                    = Wallet::where(['currency_id' => $request->currency_id, 'user_id' => $user_id])->first(['balance']);
            $success['balance']        = @$wallet->balance ? @$wallet->balance : 0;
        }
        return response()->json(['success' => $success]);
    }

    public function withdrawalConfirmation(Request $request)
    {
        // dd($request->all());

        actionSessionCheck();

        $uid                 = Auth::user()->id;
        $uuid                = unique_code();
        $payout_setting_id   = Session::get('payout_setting_id');
        $currency_id         = Session::get('currency_id');
        $totalAmount         = Session::get('totalAmount');
        $amount              = Session::get('amount');
        $payment_method_info = Session::get('payment_method_info');
        $payment_method_id   = Session::get('payment_method_id'); //new

        Session::forget('payout_setting_id');
        Session::forget('currency_id');
        Session::forget('totalAmount');
        Session::forget('amount');
        Session::forget('payment_method_info');
        Session::forget('payment_method_id');

        try
        {
            $payout_setting = PayoutSetting::with(['paymentMethod:id'])->find($payout_setting_id);
            $walletIns      = Wallet::where(['user_id' => $uid, 'currency_id' => $currency_id])->first(['id', 'balance', 'currency_id']);
            $feeInfo        = FeesLimit::where(['transaction_type_id' => Withdrawal, 'currency_id' => $walletIns->currency_id, 'payment_method_id' => $payment_method_id])
                ->first(['charge_percentage', 'charge_fixed']);
            $feePercentage = $amount * (@$feeInfo->charge_percentage / 100); //correct calc

            DB::beginTransaction();
            //withdrawal
            $withdrawal                      = new Withdrawal();
            $withdrawal->user_id             = $uid;
            $withdrawal->currency_id         = $walletIns->currency_id;
            $withdrawal->payment_method_id   = $payout_setting->paymentMethod->id;
            $withdrawal->uuid                = $uuid;
            $withdrawal->charge_percentage   = $feePercentage;
            $withdrawal->charge_fixed        = $feeInfo->charge_fixed;
            $withdrawal->subtotal            = $amount - ($withdrawal->charge_percentage + $withdrawal->charge_fixed);
            $withdrawal->amount              = $amount;
            $withdrawal->payment_method_info = $payment_method_info;
            $withdrawal->status              = 'Pending';
            $withdrawal->save();

            //withdrawalDetail
            $withdrawalDetail                = new WithdrawalDetail();
            $withdrawalDetail->withdrawal_id = $withdrawal->id;
            $withdrawalDetail->type          = $payout_setting->type;
            $withdrawalDetail->email         = $payout_setting->email;
            if ($withdrawal->payment_method->name == "Bank")
            {
                $withdrawalDetail->account_name        = $payout_setting->account_name;
                $withdrawalDetail->account_number      = $payout_setting->account_number;
                $withdrawalDetail->bank_branch_name    = $payout_setting->bank_branch_name;
                $withdrawalDetail->bank_branch_city    = $payout_setting->bank_branch_city;
                $withdrawalDetail->bank_branch_address = $payout_setting->bank_branch_address;
                $withdrawalDetail->country             = $payout_setting->country;
                $withdrawalDetail->swift_code          = $payout_setting->swift_code;
                $withdrawalDetail->bank_name           = $payout_setting->bank_name;
            }
            $withdrawalDetail->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $uid;
            $transaction->currency_id              = $walletIns->currency_id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $withdrawal->id;
            $transaction->transaction_type_id      = Withdrawal;
            $transaction->subtotal                 = $withdrawal->amount;
            $transaction->percentage               = $feeInfo->charge_percentage;
            $transaction->charge_percentage        = $feePercentage;
            $transaction->charge_fixed             = $feeInfo->charge_fixed;
            $transaction->total                    = '-' . ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
            $transaction->status                   = 'Pending';
            $transaction->payment_method_id        = $payout_setting->paymentMethod->id;
            $transaction->save();

            //Wallet
            $walletIns->balance = ($walletIns->balance - $totalAmount);
            $walletIns->save();

            DB::commit();

            $data['currencySymbol'] = Currency::find($currency_id, ['id', 'symbol'])->symbol;
            $data['amount']         = $transaction->subtotal;
            $data['transaction']    = $transaction;

            clearActionSession();
            return view('user_dashboard.withdrawal.success', $data);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            clearActionSession();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('payouts');
        }
    }

    public function withdrawalPrintPdf($trans_id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first(['value']);
        $data['transactionDetails'] = Transaction::with(['payment_method:id,name', 'currency:id,symbol'])
        ->where(['id' => $trans_id])
        ->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);
        // dd($data['transactionDetails']);

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
        $mpdf->WriteHTML(view('user_dashboard.withdrawal.withdrawalPaymentPdf', $data));
        $mpdf->Output('sendMoney_' . time() . '.pdf', 'I'); //
    }
    //Payout - ends
}
