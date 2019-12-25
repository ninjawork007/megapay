<?php

namespace App\Http\Responses\Admin\Transaction;

use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Contracts\Support\Responsable;

class DepositUpdateResponse implements Responsable
{
    protected $transaction;
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->deposit     = new Deposit();
        $this->wallet      = new Wallet();
        $this->helper      = new Common();
        $this->email       = new EmailController();
    }

    public function toResponse($request)
    {
        $this->depositUpdateLogic($request);
        return redirect('admin/transactions');
    }

    protected function depositUpdateLogic($request)
    {
        $t = $this->transaction->find($request->id);
        // dd($t);

        if ($request->type == 'Deposit')
        {
            if ($request->status == 'Pending') //requested status
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Pending');
                    $deposits         = $this->deposit->find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    // dd($deposits);
                    $deposits->save();

                    $transactions         = $this->transaction->find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();
                    // dd($current_balance);

                    $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - $request->subtotal,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('current status: blocked, doing pending');
                    $deposits         = $this->deposit->find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = $this->transaction->find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                }
                elseif ($t->status == 'Blocked') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $deposits         = $this->deposit->find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = $this->transaction->find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    $update_wallet_for_deposit = $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + $request->subtotal,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                }
                elseif ($t->status == 'Pending')
                {
                    // dd('current status: Pending, doing Success');
                    $deposits         = $this->deposit->find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = $this->transaction->find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + $request->subtotal,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('current status: Pending, doing Blocked');
                    $deposits         = $this->deposit->find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = $this->transaction->find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $deposits         = $this->deposit->find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = $this->transaction->find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    $this->wallet->where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - $request->subtotal,
                    ]);
                    // dd('done');
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                }
            }
        }
    }
}

?>