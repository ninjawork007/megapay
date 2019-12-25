<?php

namespace Hexters\CoinPayment\Http\Controllers;

use App\Http\Helpers\Common;
use App\Jobs\IPNHandlerCoinPaymentJob;
use App\Jobs\coinPaymentCallbackProccedJob;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\FeesLimit;
use App\Models\Merchant;
use App\Models\MerchantPayment;
use App\Models\Transaction;
use App\Models\Wallet;
use CoinPayment;
use Hexters\CoinPayment\Entities\cointpayment_log_trx;
use Hexters\CoinPayment\Events\IPNErrorReportEvent as SendEmail;
use Hexters\CoinPayment\Http\Resources\TransactionResourceCollection;
use Hexters\CoinPayment\Jobs\webhookProccessJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Route;

class CoinPaymentController extends Controller
{
    // public function __construct()
    // {
    //     $this->helper = new Common();
    // }

    public function index($serialize)
    {
        $data['data']       = $data       = CoinPayment::get_payload($serialize);
        $data['public_key'] = $public_key = coinPaymentInfo()->public_key;
        $data['params']     = empty($data['data']['params']) ? json_encode([]) : json_encode($data['data']['params']);
        $data['payload']    = empty($data['data']['payload']) ? json_encode([]) : json_encode($data['data']['payload']);
        // dd($data['data']['payload']['currency']);

        // config(['coinpayment.default_currency' => session('coinpayment_currency')]);
        config(['coinpayment.default_currency' => $data['data']['payload']['currency']]);

        return view('coinpayment::index', $data);
    }

    public function ajax_rates(Request $req, $usd)
    {
        // dd($req->all());

        $coins   = [];
        $aliases = [];
        $rates   = CoinPayment::api_call('rates', [
            'accepted' => 1,
        ])['result'];

        $rateBtc = $rates['BTC']['rate_btc'];

        $rateUsd = $rates[$req->currency]['rate_btc'];

        $rateAmount   = $rateUsd * $usd;
        $fiat         = [];
        $coins_accept = [];
        foreach ($rates as $i => $coin)
        {
            if ((INT) $coin['is_fiat'] === 0)
            {
                if ($rates[$i]['rate_btc'] != 0)
                {
                    $rate = ($rateAmount / $rates[$i]['rate_btc']);
                }
                else
                {
                    $rate = $rateAmount;
                }
                $coins[] = [
                    'name'     => $coin['name'],
                    'rate'     => number_format($rate, 8, '.', ''),
                    'iso'      => $i,
                    'icon'     => 'https://www.coinpayments.net/images/coins/' . $i . '.png',
                    'selected' => $i == 'BTC' ? true : false,
                    'accepted' => $coin['accepted'],
                ];

                $aliases[$i] = $coin['name'];
            }

            if ((INT) $coin['is_fiat'] === 0 && $coin['accepted'] == 1)
            {
                $rate           = ($rateAmount / $rates[$i]['rate_btc']);
                $coins_accept[] = [
                    'name'     => $coin['name'],
                    'rate'     => number_format($rate, 8, '.', ''),
                    'iso'      => $i,
                    'icon'     => 'https://www.coinpayments.net/images/coins/' . $i . '.png',
                    'selected' => $i == 'BTC' ? true : false,
                    'accepted' => $coin['accepted'],
                ];
            }

            if ((INT) $coin['is_fiat'] === 1)
            {
                $fiat[$i] = $coin;
            }

        }

        return response()->json([
            'coins'        => $coins,
            'coins_accept' => $coins_accept,
            'aliases'      => $aliases,
            'fiats'        => $fiat,
        ]);
    }

    public function make_transaction(Request $req)
    {
        $err = $req->validate([
            'amount'         => 'required|numeric',
            'payment_method' => 'required',
            'currency'       => 'required',
        ]);

        if (!empty($err['message']))
        {
            return response()->json($err);
        }

        $params = [
            'amount'    => $req->amount,
            'currency1' => $req->currency,
            'currency2' => $req->payment_method,
        ];

        return CoinPayment::api_call('create_transaction', $params);
    }

    public function trx_info(Request $req)
    {
        $payment = CoinPayment::api_call('get_tx_info', [
            'txid' => $req->result['txn_id'],
        ]);

        if (auth()->check())
        {
            //logged in
            $user = auth()->user();
            // dd($user);
            if ($payment['error'] == 'ok' && (INT) $user->coinpayment_transactions()->where('payment_id', $req->result['txn_id'])->count('id') === 0)
            {
                $data    = $payment['result'];
                $payload = $req->payload;

                $saved = [
                    'payment_id'         => $req->result['txn_id'],
                    'payment_address'    => $data['payment_address'],
                    'coin'               => $data['coin'],
                    'fiat'               => isset($payload['currency']) ? $payload['currency'] : config('coinpayment.default_currency'),
                    'status_text'        => $data['status_text'],
                    'status'             => $data['status'],
                    'payment_created_at' => date('Y-m-d H:i:s', $data['time_created']),
                    'expired'            => date('Y-m-d H:i:s', $data['time_expires']),
                    'amount'             => $data['amountf'],
                    'confirms_needed'    => empty($req->result['confirms_needed']) ? 0 : $req->result['confirms_needed'],
                    'qrcode_url'         => empty($req->result['qrcode_url']) ? '' : $req->result['qrcode_url'],
                    'status_url'         => empty($req->result['status_url']) ? '' : $req->result['status_url'],
                    'payload'            => empty($req->payload) ? json_encode([]) : json_encode($req->payload),
                ];

                if (isset($req->payload['type']) && $req->payload['type'] == "deposit") //deposit
                {
                    // dd($req->payload['currency']);

                    //insert into deposit
                    $payment_method_id = Session::get('payment_method_id');
                    $coinpaymentAmount = Session::get('coinpaymentAmount');

                    //charge percentage calculation
                    $uuid       = unique_code();
                    $curr       = Currency::where('code', $req->payload['currency'])->first(['id']);
                    $currencyId = $curr->id;
                    $feeInfo    = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $currencyId, 'payment_method_id' => $payment_method_id])->first(['charge_percentage', 'charge_fixed']);
                    $p_calc     = $coinpaymentAmount * (@$feeInfo->charge_percentage / 100);

                    try
                    {
                        \DB::beginTransaction();
                        //Deposit
                        $deposit                    = new Deposit();
                        $deposit->uuid              = $uuid;
                        $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
                        $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
                        $deposit->amount            = $coinpaymentAmount;
                        $deposit->status            = 'Pending';
                        $deposit->user_id           = auth()->user()->id;
                        $deposit->currency_id       = $currencyId;
                        $deposit->payment_method_id = $payment_method_id;
                        $deposit->save();

                        //Transaction
                        $transaction                           = new Transaction();
                        $transaction->user_id                  = auth()->user()->id;
                        $transaction->currency_id              = $currencyId;
                        $transaction->payment_method_id        = $payment_method_id;
                        $transaction->uuid                     = $uuid;
                        $transaction->transaction_reference_id = $deposit->id;
                        $transaction->transaction_type_id      = Deposit;
                        $transaction->subtotal                 = $coinpaymentAmount;
                        $transaction->percentage               = @$feeInfo->charge_percentage; //fixed
                        $transaction->charge_percentage        = $deposit->charge_percentage;
                        $transaction->charge_fixed             = $deposit->charge_fixed;
                        $transaction->total                    = $coinpaymentAmount + $deposit->charge_percentage + $deposit->charge_fixed;
                        $transaction->status                   = 'Pending';
                        $transaction->save();

                        //Wallet creation if request currency wallet does not exist
                        $wallet = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $currencyId])->first(['id']);
                        if (empty($wallet))
                        {
                            $wallet              = new Wallet();
                            $wallet->user_id     = auth()->user()->id;
                            $wallet->currency_id = $currencyId;
                            $wallet->balance     = 0; // as initially, transaction status will be pending
                            $wallet->is_default  = 'No';
                            $wallet->save();
                        }
                        $payload                   = empty($req->payload) ? [] : $req->payload;
                        $payload['deposit_id']     = $deposit->id;
                        $payload['transaction_id'] = $transaction->id;
                        $payload                   = json_encode($payload);
                        $saved['payload']          = $payload;
                        $user->coinpayment_transactions()->create($saved);

                        \DB::commit();
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        // $this->helper->one_time_message('error', $e->getMessage());
                        // return redirect('deposit');
                        $exception          = [];
                        $exception['error'] = json_encode($e->getMessage());
                        return $exception;
                    }
                }
                elseif (isset($req->payload['type']) && $req->payload['type'] == "merchant") //merchant
                {
                    $saved['merchant_id'] = $req->payload['id'];
                    try
                    {
                        \DB::beginTransaction();

                        //MerchantPayment
                        $merchantPayment                    = new MerchantPayment();
                        $merchantPayment->merchant_id       = $req->payload['id'];
                        $merchantInfo                       = Merchant::find($merchantPayment->merchant_id, ['id', 'fee', 'user_id']);
                        $merchantPayment->currency_id       = Session::get('currency_id');
                        $merchantPayment->payment_method_id = Session::get('payment_method');
                        $merchantPayment->gateway_reference = $req->result['txn_id'];
                        $merchantPayment->item_name         = Session::get('item_name');
                        $merchantPayment->order_no          = Session::get('order_no');
                        $merchantPayment->uuid              = Session::get('unique_code');
                        $merchantPayment->total             = Session::get('amount');

                        //Deposit + Merchant Fee (starts)
                        $feeInfo = FeesLimit::with('currency:id,code')
                            ->where(['transaction_type_id' => Deposit, 'currency_id' => $merchantPayment->currency_id, 'payment_method_id' => $merchantPayment->payment_method_id])
                            ->first(['charge_percentage', 'charge_fixed', 'has_transaction', 'currency_id']);
                        // dd($feeInfo);
                        if ($feeInfo->has_transaction == "Yes")
                        {
                            //if fees limit is not active, both merchant fee and deposit fee will be added
                            $feeInfoChargePercentage          = @$feeInfo->charge_percentage;
                            $feeInfoChargeFixed               = @$feeInfo->charge_fixed;
                            $depositCalcPercentVal            = $merchantPayment->total * (@$feeInfoChargePercentage / 100);
                            $depositTotalFee                  = $depositCalcPercentVal+@$feeInfoChargeFixed;
                            $merchantCalcPercentValOrTotalFee = $merchantPayment->total * ($merchantInfo->fee / 100);
                            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
                        }
                        else
                        {
                            //if fees limit is not active, only merchant fee will be added
                            $feeInfoChargePercentage          = 0;
                            $feeInfoChargeFixed               = 0;
                            $depositCalcPercentVal            = 0;
                            $depositTotalFee                  = 0;
                            $merchantCalcPercentValOrTotalFee = $merchantPayment->total * ($merchantInfo->fee / 100);
                            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
                        }
                        //Deposit + Merchant Fee (ends)

                        $merchantPayment->amount            = $merchantPayment->total - $totalFee;
                        $merchantPayment->charge_percentage = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee; //new
                        $merchantPayment->charge_fixed      = $feeInfoChargeFixed;
                        $merchantPayment->status            = 'Pending';
                        $merchantPayment->save();

                        //Transaction
                        $transaction                           = new Transaction();
                        $transaction->user_id                  = $merchantInfo->user_id;
                        $transaction->currency_id              = $merchantPayment->currency_id;
                        $transaction->payment_method_id        = $merchantPayment->payment_method_id;
                        $transaction->merchant_id              = $merchantPayment->merchant_id;
                        $transaction->uuid                     = $merchantPayment->uuid;
                        $transaction->transaction_reference_id = $merchantPayment->id;
                        $transaction->transaction_type_id      = Payment_Received;
                        $transaction->subtotal                 = $merchantPayment->total - $totalFee;
                        $transaction->percentage               = $merchantInfo->fee + $feeInfoChargePercentage;
                        $transaction->charge_percentage        = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee;
                        $transaction->charge_fixed             = $feeInfoChargeFixed;
                        $transaction->total                    = $merchantPayment->charge_percentage + $merchantPayment->charge_fixed + $merchantPayment->amount; //new
                        $transaction->status                   = 'Pending';
                        $transaction->save();
                        //

                        //Wallet
                        //No wallet change at first cause transaction status is pending, when real payment will occur, transaction will be success.
                        $merchantWallet = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $merchantPayment->currency_id])->first(['id']);
                        if (empty($merchantWallet))
                        {
                            $wallet              = new Wallet();
                            $wallet->user_id     = $merchantInfo->user_id;
                            $wallet->currency_id = $merchantPayment->currency_id;
                            $wallet->balance     = 0; // as initially, transaction status will be pending
                            $wallet->is_default  = 'No';
                            $wallet->save();
                        }

                        $payload                        = empty($req->payload) ? [] : $req->payload;
                        $payload['merchant_payment_id'] = $merchantPayment->id;
                        $payload                        = json_encode($payload);
                        $saved['payload']               = $payload;
                        $user->coinpayment_transactions()->create($saved);

                        //see route - Route::get('payment/coinpayments_check', 'MerchantPaymentController@coinPaymentsCheck');
                        \DB::commit();
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        // $this->helper->one_time_message('error', $e->getMessage());
                        $exception          = [];
                        $exception['error'] = json_encode($e->getMessage());
                        return $exception;
                    }
                }
            }
        }
        else
        {
            //logged out
            if (isset($req->payload['type']) && $req->payload['type'] == "merchant")
            {
                if ($payment['error'] == 'ok' && (int) cointpayment_log_trx::where('payment_id', $req->result['txn_id'])->count('id') === 0)
                {
                    $data = $payment['result'];

                    try
                    {
                        \DB::beginTransaction();

                        //MerchantPayment
                        $merchantPayment                    = new MerchantPayment();
                        $merchantPayment->merchant_id       = $req->payload['id'];
                        $merchantInfo                       = Merchant::find($merchantPayment->merchant_id, ['id', 'fee', 'user_id']);
                        $merchantPayment->currency_id       = Session::get('currency_id');
                        $merchantPayment->payment_method_id = Session::get('payment_method');
                        $merchantPayment->gateway_reference = $req->result['txn_id'];
                        $merchantPayment->item_name         = Session::get('item_name');
                        $merchantPayment->order_no          = Session::get('order_no');
                        $merchantPayment->uuid              = Session::get('unique_code');
                        $merchantPayment->total             = Session::get('amount');

                        //Deposit + Merchant Fee (starts)
                        $feeInfo = FeesLimit::with('currency:id,code')
                            ->where(['transaction_type_id' => Deposit, 'currency_id' => $merchantPayment->currency_id, 'payment_method_id' => $merchantPayment->payment_method_id])
                            ->first(['charge_percentage', 'charge_fixed', 'has_transaction', 'currency_id']);
                        if ($feeInfo->has_transaction == "Yes")
                        {
                            //if fees limit is not active, both merchant fee and deposit fee will be added
                            $feeInfoChargePercentage          = @$feeInfo->charge_percentage;
                            $feeInfoChargeFixed               = @$feeInfo->charge_fixed;
                            $depositCalcPercentVal            = $merchantPayment->total * (@$feeInfoChargePercentage / 100);
                            $depositTotalFee                  = $depositCalcPercentVal+@$feeInfoChargeFixed;
                            $merchantCalcPercentValOrTotalFee = $merchantPayment->total * ($merchantInfo->fee / 100);
                            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
                        }
                        else
                        {
                            //if fees limit is not active, only merchant fee will be added
                            $feeInfoChargePercentage          = 0;
                            $feeInfoChargeFixed               = 0;
                            $depositCalcPercentVal            = 0;
                            $depositTotalFee                  = 0;
                            $merchantCalcPercentValOrTotalFee = $merchantPayment->total * ($merchantInfo->fee / 100);
                            $totalFee                         = $depositTotalFee + $merchantCalcPercentValOrTotalFee;
                        }

                        //Deposit + Merchant Fee (ends)
                        $merchantPayment->amount            = $merchantPayment->total - $totalFee;
                        $merchantPayment->charge_percentage = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee; //new
                        $merchantPayment->charge_fixed      = $feeInfoChargeFixed;                                        //new
                        $merchantPayment->status            = 'Pending';
                        $merchantPayment->save();

                        //Transaction
                        $transaction                           = new Transaction();
                        $transaction->user_id                  = $merchantInfo->user_id;
                        $transaction->currency_id              = $merchantPayment->currency_id;
                        $transaction->payment_method_id        = $merchantPayment->payment_method_id;
                        $transaction->merchant_id              = $merchantPayment->merchant_id;
                        $transaction->uuid                     = $merchantPayment->uuid;
                        $transaction->transaction_reference_id = $merchantPayment->id;
                        $transaction->transaction_type_id      = Payment_Received;
                        $transaction->subtotal                 = $merchantPayment->total - $totalFee;                                                             //new
                        $transaction->percentage               = $merchantInfo->fee + $feeInfoChargePercentage;                                                   //new
                        $transaction->charge_percentage        = $depositCalcPercentVal + $merchantCalcPercentValOrTotalFee;                                      //new
                        $transaction->charge_fixed             = $feeInfoChargeFixed;                                                                             //new
                        $transaction->total                    = $merchantPayment->charge_percentage + $merchantPayment->charge_fixed + $merchantPayment->amount; //new
                        $transaction->status                   = 'Pending';
                        $transaction->save();
                        //

                        //Wallet
                        //No wallet change at first cause transaction status is pending, when real payment will occur, transaction will be success.
                        $merchantWallet = Wallet::where(['user_id' => $merchantInfo->user_id, 'currency_id' => $merchantPayment->currency_id])->first(['id']);
                        if (empty($merchantWallet))
                        {
                            $wallet              = new Wallet();
                            $wallet->user_id     = $merchantInfo->user_id;
                            $wallet->currency_id = $merchantPayment->currency_id;
                            $wallet->balance     = 0; // as initially, transaction status will be pending
                            $wallet->is_default  = 'No';
                            $wallet->save();
                        }

                        $payload                        = empty($req->payload) ? [] : $req->payload;
                        $payload['merchant_payment_id'] = $merchantPayment->id;
                        $payload                        = json_encode($payload);
                        $saved                          = [
                            'merchant_id'        => $req->payload['id'],
                            'payment_id'         => $req->result['txn_id'],
                            'payment_address'    => $data['payment_address'],
                            'coin'               => $data['coin'],
                            'fiat'               => config('coinpayment.default_currency'),
                            'status_text'        => $data['status_text'],
                            'status'             => $data['status'],
                            'payment_created_at' => date('Y-m-d H:i:s', $data['time_created']),
                            'expired'            => date('Y-m-d H:i:s', $data['time_expires']),
                            'amount'             => $data['amountf'],
                            // 'amount'             => number_format($data['amountf'], 2, '.', ''),
                            'confirms_needed'    => empty($req->result['confirms_needed']) ? 0 : $req->result['confirms_needed'],
                            'qrcode_url'         => empty($req->result['qrcode_url']) ? '' : $req->result['qrcode_url'],
                            'status_url'         => empty($req->result['status_url']) ? '' : $req->result['status_url'],
                            'payload'            => $payload,
                        ];
                        cointpayment_log_trx::create($saved);

                        //see route - Route::get('payment/coinpayments_check', 'MerchantPaymentController@coinPaymentsCheck');
                        \DB::commit();

                        $send['request_type'] = 'create_transaction';
                        $send['params']       = empty($req->params) ? [] : $req->params;
                        $send['payload']      = empty($req->payload) ? [] : $req->payload;
                        $send['transaction']  = $payment['error'] == 'ok' ? $payment['result'] : [];
                        if (Route::has('coinpayment.webhook'))
                        {
                            dispatch(new webhookProccessJob($send));
                        }
                        dispatch(new coinPaymentCallbackProccedJob($send));
                        return $payment;
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        // $this->helper->one_time_message('error', $e->getMessage());
                        // return back();
                        $exception          = [];
                        $exception['error'] = json_encode($e->getMessage());
                        return $exception;
                    }
                }
            }
        }
    }

    public function transactions_list()
    {
        return view('coinpayment::list');
    }

    public function transactions_list_any(Request $req)
    {
        $transaction = auth()->user()->coinpayment_transactions()->orderby('updated_at', 'desc');
        if (!empty($req->coin))
        {
            $transaction->where('coin', $req->coin);
        }

        if ($req->status !== 'all')
        {
            $transaction->where('status', '=', (INT) $req->status);
        }

        return new TransactionResourceCollection($transaction->paginate($req->limit));
    }

    public function manual_check(Request $req)
    {
        $check = CoinPayment::api_call('get_tx_info', [
            'txid' => $req->payment_id,
        ]);
        if ($check['error'] == 'ok')
        {
            $data = $check['result'];
            $trx  = auth()->user()->coinpayment_transactions()->where('id', $req->id);
            if ($data['status'] > 0 || $data['status'] < 0)
            {
                $trx->update([
                    'status_text'     => $data['status_text'],
                    'status'          => $data['status'],
                    'confirmation_at' => ((INT) $data['status'] === 100) ? date('Y-m-d H:i:s', $data['time_completed']) : null,
                ]);
                $trx                  = $trx->first();
                $data['request_type'] = 'schedule_transaction';
                $data['payload']      = (Array) json_decode($trx->payload, true);
                if (Route::has('coinpayment.webhook'))
                {
                    dispatch(new webhookProccessJob($data));
                }
                dispatch(new coinPaymentCallbackProccedJob($data));
            }
            return response()->json($trx->first());
        }
        return response()->json([
            'message' => 'Look like the something wrong!',
        ], 401);
    }

    public function receive_webhook(Request $req)
    {
        /*
        $txn_id = $_POST['txn_id'];
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $amount1 = floatval($_POST['amount1']);
        $amount2 = floatval($_POST['amount2']);
        $currency1 = $_POST['currency1'];
        $currency2 = $_POST['currency2'];
        $status = intval($_POST['status']);
        $status_text = $_POST['status_text'];
         */

        $cp_merchant_id = coinPaymentInfo()->merchant_id;

        $cp_ipn_secret  = "";
        $cp_debug_email = "";

        /* Filtering */
        if (!empty($req->merchant) && $req->merchant != trim($cp_merchant_id))
        {
            if (!empty($cp_debug_email))
            {
                event(new SendEmail([
                    'email'   => $cp_debug_email,
                    'message' => 'No or incorrect Merchant ID passed',
                ]));
            }

            return response('No or incorrect Merchant ID passed', 401);
        }

        $request = file_get_contents('php://input');
        if ($request === false || empty($request))
        {
            if (!empty($cp_debug_email))
            {
                event(new SendEmail([
                    'email'   => $cp_debug_email,
                    'message' => 'Error reading POST data',
                ]));
            }

            return response('Error reading POST data', 401);
        }

        $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
        if (!hash_equals($hmac, $_SERVER['HTTP_HMAC']))
        {
            if (!empty($cp_debug_email))
            {
                event(new SendEmail([
                    'email'   => $cp_debug_email,
                    'message' => 'HMAC signature does not match',
                ]));
            }

            return response('HMAC signature does not match', 401);
        }

        $log = cointpayment_log_trx::where('payment_id', $req->txn_id)->first();
        if ($log != null)
        {
            $log->update([
                'status'      => $req->status,
                'status_text' => $req->status_text,
            ]);

            dispatch(new IPNHandlerCoinPaymentJob([
                'payment_id'         => $log->payment_id,
                'payment_address'    => $log->payment_address,
                'coin'               => $log->coin,
                'fiat'               => $log->fiat,
                'status_text'        => $log->status_text,
                'status'             => $log->status,
                'payment_created_at' => $log->payment_created_at,
                'confirmation_at'    => $log->confirmation_at,
                'amount'             => $log->amount,
                'confirms_needed'    => $log->confirms_needed,
                'payload'            => (Array) json_decode($log->payload),
            ]));
        }
    }
}
