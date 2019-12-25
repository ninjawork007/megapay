<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeesLimit;
use App\Models\PayoutSetting;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\WithdrawalDetail;
use Illuminate\Http\Request;

class PayoutMoneyController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;

    //Check User Payout Settings
    public function checkPayoutSettingsApi()
    {
        $payoutSettings = PayoutSetting::where(['user_id' => request('user_id')])->get(['id']);
        return response()->json([
            'status'         => $this->successStatus,
            'payoutSettings' => $payoutSettings,
        ]);
    }

    //Withdrawal Money Starts here
    public function getWithdrawalPaymentMethod()
    {
        // dd(request()->all());
        $paymentMethod = PayoutSetting::where(['user_id' => request('user_id')])->get(['id', 'user_id', 'type', 'email', 'account_name']);
        $pm            = [];
        for ($i = 0; $i < count($paymentMethod); $i++)
        {
            $pm[$i]['id']                      = $paymentMethod[$i]->id;
            $pm[$i]['user_id']                 = $paymentMethod[$i]->user_id;
            $pm[$i]['paymentMethod']           = $paymentMethod[$i]->paymentMethod->name;
            $pm[$i]['paymentMethodId']         = $paymentMethod[$i]->type;
            $pm[$i]['paymentMethodCredential'] = $paymentMethod[$i]->email ? $paymentMethod[$i]->email : $paymentMethod[$i]->account_name;
        }
        $success['status']        = $this->successStatus;
        $success['paymentmethod'] = $pm;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function getWithdrawalCurrencyBasedOnPaymentMethod()
    {
        // dd(request()->all());
        $payment_met_id = request('paymentMethodId');
        $wallets        = Wallet::where(['user_id' => request('user_id')])->whereHas('active_currency', function ($q) use ($payment_met_id)
        {
            $q->whereHas('fees_limit', function ($query) use ($payment_met_id)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', Withdrawal)->where('payment_method_id', $payment_met_id);
            });
        })
        ->with(['active_currency:id,code', 'active_currency.fees_limit:id,currency_id']) //Optimized
        ->get(['currency_id','is_default']);

        //map wallets
        $arr        = [];
        $currencies = $wallets->map(function ($wallet)
        {
            $arr['id']             = $wallet->active_currency->id;
            $arr['code']           = $wallet->active_currency->code;
            $arr['default_wallet'] = $wallet->is_default;
            return $arr;
        });
        //
        $success['currencies'] = $currencies;
        $success['status']     = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function getWithdrawDetailsWithAmountLimitCheck()
    {
        // dd(request()->all());
        $user_id         = request('user_id');
        $amount          = request('amount');
        $currency_id     = request('currency_id');
        $payoutSettingId = request('payoutSetId');
        $paymentMethodId = request('paymentMethodId');

        $payoutSetting             = PayoutSetting::with(['paymentMethod:id,name'])->where(['id' => $payoutSettingId])->first(['account_name', 'account_number', 'type', 'swift_code', 'bank_name']);
        $success['account_name']   = $payoutSetting->account_name;
        $success['account_number'] = $payoutSetting->account_number;
        $success['type']           = $payoutSetting->paymentMethod->name;
        $success['swift_code']     = $payoutSetting->swift_code;
        $success['bank_name']      = $payoutSetting->bank_name;

        $wallets     = Wallet::where(['user_id' => $user_id, 'currency_id' => $currency_id])->first(['balance']);
        $feesDetails = FeesLimit::with('currency:id,symbol,code')->where(['transaction_type_id' => Withdrawal, 'currency_id' => $currency_id, 'payment_method_id' => $paymentMethodId])
            ->first(['charge_percentage', 'charge_fixed', 'min_limit', 'max_limit', 'currency_id']);
        // dd($feesDetails);

        //Wallet Balance Limit Check Starts here
        $checkAmount = $amount + $feesDetails->charge_fixed + $feesDetails->charge_percentage;
        if (@$wallets)
        {
            //if((@$wallets->balance) < (@$amount)){
            if ((@$checkAmount) > (@$wallets->balance) || (@$wallets->balance < 0))
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
            $totalFess                    = (@$feesDetails->charge_percentage * $amount / 100) + (@$feesDetails->charge_fixed);
            $success['amount']            = $amount;
            $success['totalFees']         = $totalFess;
            $success['totalHtml']         = formatNumber($totalFess);
            $success['currency_id']       = $feesDetails->currency_id;
            $success['payout_setting_id'] = $payoutSettingId;
            $success['currSymbol']        = $feesDetails->currency->symbol;
            $success['currCode']          = $feesDetails->currency->code;
            $success['totalAmount']       = $amount + $totalFess;

            $success['status'] = $this->successStatus;

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
            return response()->json(['success' => $success], $this->successStatus);
        }
        else
        {
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }

        //Code for Fees Limit Starts here
        if (empty($feesDetails))
        {
            $feesPercentage               = 0;
            $feesFixed                    = 0;
            $totalFess                    = ($feesPercentage * $amount / 100) + ($feesFixed);
            $success['amount']            = $amount;
            $success['totalFees']         = $totalFess;
            $success['totalHtml']         = formatNumber($totalFess);
            $success['currency_id']       = $feesDetails->currency_id;
            $success['payout_setting_id'] = $payoutSettingId;
            $success['currSymbol']        = $feesDetails->currency->symbol;
            $success['currCode']          = $feesDetails->currency->code;
            $success['totalAmount']       = $amount + $totalFess;
            $success['status']            = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        //Amount Limit Check Ends here
    }

    public function withdrawMoneyConfirm()
    {
        // dd(request()->all());

        $uid               = request('user_id');
        $uuid              = unique_code();
        $payout_setting_id = request('payout_setting_id');
        $currency_id       = request('currency_id');
        $amount            = request('amount');
        $totalAmount       = request('amount') + request('totalFees');

        //PayoutSetting
        $payoutSetting       = PayoutSetting::with(['paymentMethod:id,name'])->find($payout_setting_id);
        $payment_method_info = $payoutSetting->email ? $payoutSetting->email : $payoutSetting->paymentMethod->name;

        //FeesLimit
        $feeInfo = FeesLimit::where(['transaction_type_id' => Withdrawal, 'currency_id' => $currency_id, 'payment_method_id' => $payoutSetting->type])->first(['charge_percentage', 'charge_fixed']);
        $feePercentage = $amount * ($feeInfo->charge_percentage / 100);

        try
        {
            \DB::beginTransaction();

            //Save to Withdrawal
            $withdrawal                      = new Withdrawal();
            $withdrawal->user_id             = $uid;
            $withdrawal->currency_id         = $currency_id;
            $withdrawal->payment_method_id   = $payoutSetting->type;
            $withdrawal->uuid                = $uuid;
            $withdrawal->charge_percentage   = $feePercentage;
            $withdrawal->charge_fixed        = $feeInfo->charge_fixed;
            $withdrawal->subtotal            = $amount - ($withdrawal->charge_percentage + $withdrawal->charge_fixed);
            $withdrawal->amount              = $amount;
            $withdrawal->payment_method_info = $payment_method_info;
            $withdrawal->status              = 'Pending';
            $withdrawal->save();

            //Save to withdrawalDetail
            $withdrawalDetail                = new WithdrawalDetail();
            $withdrawalDetail->withdrawal_id = $withdrawal->id;
            $withdrawalDetail->type          = $payoutSetting->type;
            $withdrawalDetail->email         = $payoutSetting->email;
            if ($withdrawal->payment_method->name == "Bank")
            {
                $withdrawalDetail->account_name        = $payoutSetting->account_name;
                $withdrawalDetail->account_number      = $payoutSetting->account_number;
                $withdrawalDetail->bank_branch_name    = $payoutSetting->bank_branch_name;
                $withdrawalDetail->bank_branch_city    = $payoutSetting->bank_branch_city;
                $withdrawalDetail->bank_branch_address = $payoutSetting->bank_branch_address;
                $withdrawalDetail->country             = $payoutSetting->country;
                $withdrawalDetail->swift_code          = $payoutSetting->swift_code;
                $withdrawalDetail->bank_name           = $payoutSetting->bank_name;
            }
            $withdrawalDetail->save();

            //Save to Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $uid;
            $transaction->currency_id              = $currency_id;
            $transaction->payment_method_id        = $payoutSetting->type;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $withdrawal->id;
            $transaction->transaction_type_id      = Withdrawal;
            $transaction->subtotal                 = $withdrawal->amount;
            $transaction->percentage               = $feeInfo->charge_percentage;
            $transaction->charge_percentage        = $feePercentage;
            $transaction->charge_fixed             = $feeInfo->charge_fixed;
            $transaction->total                    = '-' . ($transaction->subtotal + $transaction->charge_percentage + $transaction->charge_fixed);
            $transaction->status                   = 'Pending';
            $transaction->save();

            //Subtract From wallet (amount + fees)
            $walletIns          = Wallet::where(['user_id' => $uid, 'currency_id' => $currency_id])->first(['id', 'balance']);
            $walletIns->balance = ($walletIns->balance - $totalAmount);
            $walletIns->save();

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
    //Withdrawal Money Ends here
}
