<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Reason;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\Wallet;
use Auth;
use Illuminate\Http\Request;

class UserTransactionController extends Controller
{

    public function index()
    {
        $transaction      = new Transaction();
        $data['menu']     = 'transactions';
        $data['sub_menu'] = 'transactions';

        $status = 'all';
        $type   = 'all';
        $wallet = 'all';

        if (isset($_GET['status']))
        {
            $status = $_GET['status'];
        }

        if (isset($_GET['wallet']))
        {
            $wallet = $_GET['wallet'];
        }

        if (isset($_GET['type']))
        {
            $type = $_GET['type'];
        }

        if (isset($_GET['from']))
        {
            $from = $_GET['from'];
        }
        else
        {
            $from = null;
        }

        if (isset($_GET['to']))
        {
            $to = $_GET['to'];
            $to = date("d-m-Y", strtotime($to));
        }
        else
        {
            $to = null;
        }
        $data['from'] = $from;
        $data['to']   = $to;

        $data['transactions'] = $transaction->getTransactions($from, $to, $type, $wallet, $status);
        $data['status']       = $status;
        $data['wallet']       = $wallet;
        $data['wallets']      = Wallet::with(['currency:id,code'])->where(['user_id' => Auth::user()->id])->get(['currency_id']);
        if ($type == Deposit || $type == Withdrawal || $type == 'all')
        {
            $data['type'] = $type;
        }
        else
        {
            if ($type == 'sent')
            {
                $data['type'] = 'sent';
            }
            elseif ($type == 'request')
            {
                $data['type'] = 'request';
            }
            elseif ($type == 'received')
            {
                $data['type'] = 'received';
            }
            elseif ($type == 'voucher')
            {
                $data['type'] = 'voucher';
            }
            elseif ($type == 'exchange')
            {
                $data['type'] = 'exchange';
            }
        }
        return view('user_dashboard.transactions.index', $data);
    }

    public function showDetails($id)
    {
        $data['menu']          = 'transactions';
        $data['sub_menu']      = 'transactions';
        $data['icon']          = 'desktop';
        $data['content_title'] = 'Transaction Information';
        $defendant             = [];
        $data['transactions']  = $transaction  = Transaction::find($id);
        $data['reasons']       = Reason::all();

        if (in_array($transaction->transaction_type_id, [Transferred, Received]) && $transaction->status == 'Success')
        {
            $transfer     = Transfer::find($transaction->transaction_reference_id);
            $defendant[0] = $transfer->sender_id;
            $defendant[1] = $transfer->receiver_id;

            foreach ($defendant as $key => $value)
            {
                if ($value != $transaction->user_id)
                {
                    $data['defendant_id'] = $value;
                }
            }
        }

        return view('user_dashboard.transactions.view', $data);
    }

    public function getTransactionsByType($option = '')
    {
        if ($option == 'deposits')
        {
            $data['menu']     = 'transactions';
            $data['sub_menu'] = 'deposits';

            $data['deposits'] = $deposits = Transaction::where(['transaction_type_id' => Deposit, 'user_id' => Auth::user()->id])->orderBy('transaction_type_id', 'desc')->get();
            return view('users.transactions.deposits', $data);
        }
        elseif ($option == 'transferred-payments')
        {
            $data['menu']     = 'transactions';
            $data['sub_menu'] = 'transferred-payments';

            $data['transferred_payments'] = Transaction::where(['transaction_type_id' => Transferred, 'user_id' => Auth::user()->id])->orderBy('transaction_type_id', 'desc')->get();
            return view('users.transactions.transfers', $data);
        }
        elseif ($option == 'received-payments')
        {
            $data['menu']     = 'transactions';
            $data['sub_menu'] = 'received-payments';

            $data['received_payments'] = Transaction::where(['transaction_type_id' => Received, 'user_id' => Auth::user()->id])->orderBy('transaction_type_id', 'desc')->get();
            return view('users.transactions.receivables', $data);
        }
    }

    public function getTransaction(Request $request)
    {

        $data['status'] = 0;

        // $transaction    = Transaction::find($request->id);
        $transaction    = Transaction::with([
            'payment_method:id,name',
            'transaction_type:id,name',
            'currency:id,code,symbol',
            'transfer:id,sender_id',
            'transfer.sender:id,first_name,last_name',
            'end_user:id,first_name,last_name',
            'merchant:id,business_name',
        ])->find($request->id);
        // dd($transaction);

        if ($transaction->count() > 0)
        {
            if ($transaction->transaction_type_id == Deposit)
            {
                if ($transaction->payment_method->name == 'Mts')
                {
                    // $pm = 'Pay Money';
                    $pm = getCompanyName();
                }
                else
                {
                    $pm = $transaction->payment_method->name; //r1
                }

                $data['html'] = "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.deposit.deposited-to') . "</label>" .
                "<div class=''>" . $transaction->currency->code . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                "<div class=''>" . $transaction->uuid . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.deposit.payment-method') . "</label>" .
                "<div  class=''>" . $pm . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                "<div class='clearfix'></div>" .
                "<div class='left '>" . __('message.dashboard.left-table.deposit.deposited-amount') . "</div>" .
                "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) . "</div>" .//r2
                "<div class='clearfix'></div>" .
                "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
                "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total - $transaction->subtotal)) . "</div>" .
                "<div class='clearfix'></div>" .
                "<hr/>" .
                "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
                "<div class='clearfix'></div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<a href='" . url('deposit-money/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                    "</div>";
            }
            else if ($transaction->transaction_type_id == Withdrawal)
            {
                if ($transaction->payment_method->name == 'Mts')
                {
                    // $pm = 'Pay Money';
                    $pm = getCompanyName();
                }
                else
                {
                    $pm = $transaction->payment_method->name;
                }
                // $fee = formatNumber(abs($transaction->total) - abs($transaction->subtotal));
                $fee = abs($transaction->total) - abs($transaction->subtotal);
                if ($fee > 0)
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.withdrawal.withdrawan-with') . "</label>" .
                    // "<div class=''>" . $transaction->payment_method->name . "</div>" .
                    "<div  class=''>" . $pm . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.withdrawal.withdrawan-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($fee)) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('withdrawal-money/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";
                }
                else
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.withdrawal.withdrawan-with') . "</label>" .
                    "<div class=''>" . $transaction->payment_method->name . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.withdrawal.withdrawan-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('withdrawal-money/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";
                }
            }
            else if ($transaction->transaction_type_id == Transferred)
            {
                $userEmail = '';

                if ($transaction->user_type == 'unregistered')
                {
                    $userEmail = "<br><label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.email') . "</label>" .
                    "<div>" . $transaction->email . "</div>";
                     // "<div>" . ($transaction->email) ? $transaction->email : $transaction->phone . "</div>";
                }

                // $fee = formatNumber(abs($transaction->total) - abs($transaction->subtotal));
                $fee = abs($transaction->total) - abs($transaction->subtotal);
                if ($fee > 0)
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.paid-with') . "</label>" .
                    "<div class=''>" . $transaction->currency->code . "</div>" . $userEmail .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left'>" . __('message.dashboard.left-table.transferred.transferred-amount') . "</div>" .
                    "<div class='right'>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left'>" . __('message.dashboard.left-table.fee') . "</div>" .
                    "<div class='right'>" . moneyFormat($transaction->currency->symbol, formatNumber($fee)) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left'><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right'><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                    "<div  class='act-detail-font'>" . $transaction->note . "</div>" .
                    "</div>" .

                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('moneytransfer/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";
                }
                else
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.paid-with') . "</label>" .
                    "<div class=''>" . $transaction->currency->code . "</div>" . $userEmail .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.transferred.transferred-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                    "<div class='act-detail-font'>" . $transaction->note . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('moneytransfer/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";
                }
            }
            // else if ($transaction->transaction_type_id == Bank_Transfer)
            // {
            //     $fee = abs($transaction->total) - abs($transaction->subtotal);

            //     $data['html'] = "<div class='form-group trans_details'>" .
            //     "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.bank-transfer.transferred-with') . "</label>" .
            //     "<div class=''>" . $transaction->currency->code . "</div>" .
            //     "</div>" .
            //     "<div class='form-group trans_details'>" .
            //     "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
            //     "<div class=''>" . $transaction->uuid . "</div>" .
            //     "</div>" .
            //     "<div class='form-group trans_details'>" .
            //     "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.bank-transfer.bank-details') . "</label>" .
            //     "<div class='clearfix'></div>" .
            //     "<div class='left'>" . __('message.dashboard.left-table.bank-transfer.bank-name') . "</div>" .
            //     "<div class='right'>" . $transaction->bank->bank_name . "</div>" .
            //     "<div class='clearfix'></div>" .
            //     "<div class='left'>" . __('message.dashboard.left-table.bank-transfer.bank-branch-name') . "</div>" .
            //     "<div class='right'>" . $transaction->bank->bank_branch_name . "</div>" .
            //     "<div class='clearfix'></div>" .
            //     "<div class='left'>" . __('message.dashboard.left-table.bank-transfer.bank-account-name') . "</div>" .
            //     "<div class='right'>" . $transaction->bank->account_name . "</div>" .
            //     "<div class='clearfix'></div>" .
            //     "</div>" .
            //     "<div class='form-group trans_details'>" .
            //     "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
            //     "<div class='clearfix'></div>" .
            //     "<div class='left'>" . __('message.dashboard.left-table.bank-transfer.transferred-amount') . "</div>" .
            //     "<div class='right'>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
            //     "<div class='clearfix'></div>" .
            //     "<div class='left'>" . __('message.dashboard.left-table.fee') . "</div>" .
            //     "<div class='right'>" . moneyFormat($transaction->currency->symbol, formatNumber($fee)) . "</div>" .
            //     "<div class='clearfix'></div>" .
            //     "<hr/>" .
            //     "<div class='left'><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
            //     "<div class='right'><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
            //     "<div class='clearfix'></div>" .
            //     "</div>" .
            //     "<div class='form-group trans_details'>" .
            //     "<a href='" . url('bank-transfer/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
            //         "</div>";
            // }
            else if ($transaction->transaction_type_id == Received)
            {
                $data['html'] = "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.received.paid-by') . "</label>" .
                "<div class=''>" . $transaction->transfer->sender->first_name . ' ' . $transaction->transfer->sender->last_name . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                "<div class=''>" . $transaction->uuid . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                "<div class='clearfix'></div>" .
                "<div class='left '>" . __('message.dashboard.left-table.received.received-amount') . "</div>" .
                "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) . "</div>" .
                "<div class='clearfix'></div>" .
                "<hr/>" .
                "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
                "<div class='clearfix'></div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                "<div class='act-detail-font'>" . $transaction->note . "</div>" .
                "</div>" .

                "<div class='form-group trans_details'>" .
                "<a href='" . url('moneytransfer/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                    "</div>";
            }
            else if ($transaction->transaction_type_id == Exchange_From)
            {
                $fee = abs($transaction->total) - abs($transaction->subtotal);
                if ($fee > 0)
                {

                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.exchange-from.from-wallet') . "</label>" .
                    "<div class=''>" . $transaction->currency->code . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.exchange-from.exchange-from-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($fee)) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .

                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('transactions/exchangeTransactionPrintPdf/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";
                }
                else
                {

                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.exchange-from.from-wallet') . "</label>" .
                    "<div class=''>" . $transaction->currency->code . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.exchange-from.exchange-from-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('transactions/exchangeTransactionPrintPdf/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";

                }
            }
            else if ($transaction->transaction_type_id == Exchange_To)
            {

                $data['html'] = "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.exchange-to.to-wallet') . "</label>" .
                "<div class=''>" . $transaction->currency->code . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                "<div class=''>" . $transaction->uuid . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                "<div class='clearfix'></div>" .
                "<div class='left '>" . __('message.dashboard.left-table.exchange-from.exchange-from-amount') . "</div>" .
                "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                "<div class='clearfix'></div>" .
                "<hr/>" .
                "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) . "</strong></div>" .
                "<div class='clearfix'></div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<a href='" . url('transactions/exchangeTransactionPrintPdf/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                    "</div>";
            }
            // else if (in_array($transaction->transaction_type_id, [Voucher_Created]))
            // {
            //     $fee = abs($transaction->total) - abs($transaction->subtotal);
            //     if ($fee > 0)
            //     {
            //         $data['html'] = "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.voucher-created.voucher-code') . "</label>" .
            //         "<div class=''>" . $transaction->voucher->code . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
            //         "<div class=''>" . $transaction->uuid . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
            //         "<div class='clearfix'></div>" .
            //         "<div class='left '>" . __('message.dashboard.left-table.voucher-created.voucher-amount') . "</div>" .
            //         "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
            //         "<div class='clearfix'></div>" .
            //         "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
            //         "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($fee)) . "</div>" .
            //         "<div class='clearfix'></div>" .
            //         "<hr/>" .
            //         "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
            //         "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
            //         "<div class='clearfix'></div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<a href='" . url('voucher/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
            //             "</div>";
            //     }
            //     else
            //     {

            //         $data['html'] = "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.voucher-created.voucher-code') . "</label>" .
            //         "<div class=''>" . $transaction->voucher->code . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
            //         "<div class=''>" . $transaction->uuid . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
            //         "<div class='clearfix'></div>" .
            //         "<div class='left '>" . __('message.dashboard.left-table.voucher-created.voucher-amount') . "</div>" .
            //         "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
            //         "<div class='clearfix'></div>" .
            //         "<hr/>" .
            //         "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
            //         "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
            //         "<div class='clearfix'></div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<a href='" . url('voucher/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
            //             "</div>";

            //     }
            // }
            // else if (in_array($transaction->transaction_type_id, [Voucher_Activated]))
            // {
            //     $fee = abs($transaction->total) - abs($transaction->subtotal);
            //     if ($fee > 0)
            //     {
            //         $data['html'] = "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.voucher-created.voucher-code') . "</label>" .
            //         "<div class=''>" . $transaction->voucher->code . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
            //         "<div class=''>" . $transaction->uuid . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
            //         "<div class='clearfix'></div>" .
            //         "<div class='left '>" . __('message.dashboard.left-table.voucher-created.voucher-amount') . "</div>" .
            //         "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
            //         "<div class='clearfix'></div>" .
            //         "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
            //         "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($fee)) . "</div>" .
            //         "<div class='clearfix'></div>" .
            //         "<hr/>" .
            //         "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
            //         "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
            //         "<div class='clearfix'></div>" .
            //         "</div>" .

            //         "<div class='form-group trans_details'>" .
            //         "<a href='" . url('voucher/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
            //             "</div>";
            //     }
            //     else
            //     {

            //         $data['html'] = "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.voucher-created.voucher-code') . "</label>" .
            //         "<div class=''>" . $transaction->voucher->code . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
            //         "<div class=''>" . $transaction->uuid . "</div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
            //         "<div class='clearfix'></div>" .
            //         "<div class='left '>" . __('message.dashboard.left-table.voucher-created.voucher-amount') . "</div>" .
            //         "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
            //         "<div class='clearfix'></div>" .
            //         "<hr/>" .
            //         "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
            //         "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
            //         "<div class='clearfix'></div>" .
            //         "</div>" .
            //         "<div class='form-group trans_details'>" .
            //         "<a href='" . url('voucher/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
            //             "</div>";
            //     }
            // }
            else if ($transaction->transaction_type_id == Request_From)
            {
                $conditionForRequestToPhoneAndEMail = !empty($transaction->email) ? $transaction->email : $transaction->phone;
                $cancel_btn = '';
                if ($transaction->status == 'Pending')
                {
                    $cancel_btn = "<button class='btn btn-secondary btn-sm trxnreqfrom' data-notificationType='{$conditionForRequestToPhoneAndEMail}' data='{$transaction->id}' data-type='{$transaction->transaction_type_id}' id='btn_{$transaction->id}'>" . __('message.form.cancel') . "</button>";
                }

                if ($transaction->user_type == 'registered')
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.form.name') . "</label>" .
                    "<div class=''>" . $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.send-request.request.confirmation.requested-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                    "<div  class='act-detail-font'>" . $transaction->note . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('request-payment/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" . $cancel_btn .
                        "</div>";
                }
                else
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.email') . "</label>" .
                    "<div class=''>" . $transaction->email . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.send-request.request.confirmation.requested-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                    "<div  class='act-detail-font'>" . $transaction->note . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('request-payment/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" . $cancel_btn .
                        "</div>";
                }
            }
            else if ($transaction->transaction_type_id == Request_To)
            {
                $conditionForRequestToPhoneAndEMail = !empty($transaction->email) ? $transaction->email : $transaction->phone;
                $twoButtons = '';
                if ($transaction->status == 'Pending')
                {
                    // $twoButtons = "<button class='btn btn-secondary btn-sm trxn' data='" . $transaction->id . "' data-type='" . $transaction->transaction_type_id . "' id='btn_" . $transaction->id . "'>" . __('message.form.cancel') . "</button>";
                    $twoButtons = "<button class='btn btn-secondary btn-sm trxn' data-notificationType='{$conditionForRequestToPhoneAndEMail}' data='{$transaction->id}' data-type='{$transaction->transaction_type_id}'
                    id='btn_{$transaction->id}'>" . __('message.form.cancel') . "</button>";

                    $twoButtons .= " <button class='btn btn-secondary btn-sm trxn_accept' data-rel='" . $transaction->transaction_reference_id . "' data='" . $transaction->id . "' id='acceptbtn_" . $transaction->id . "'> " . __('message.dashboard.left-table.request-to.accept') . " </button>";
                }
                if ($transaction->user_type == 'registered')
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.form.name') . "</label>" .
                    // "<div class=''>" . $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name . "</div>" .
                    "<div class=''>" . $transaction->end_user->first_name . ' ' . $transaction->end_user->last_name . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.send-request.request.confirmation.requested-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->charge_percentage + $transaction->charge_fixed)) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                    "<div class='act-detail-font'>" . $transaction->note . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('request-payment/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" . $twoButtons .
                        "</div>";
                }
                else
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.email') . "</label>" .
                    // "<div class=''>" . $transaction->end_user->email . "</div>" .
                    "<div class=''>" . $transaction->email . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.send-request.request.confirmation.requested-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transferred.note') . "</label>" .
                    "<div class='act-detail-font'>" . $transaction->note . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('request-payment/print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" . $twoButtons .
                        "</div>";
                }
            }
            else if ($transaction->transaction_type_id == Payment_Sent)
            {
                $fee = abs($transaction->total) - abs($transaction->subtotal);
                if ($fee > 0)
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.merchant.payment.merchant') . "</label>" .
                    "<div class=''>" . $transaction->merchant->business_name . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.payment-Sent.payment-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group'>" .
                    "<a href='' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .

                    "<a href='" . url('transactions/merchant-payment-print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";
                }
                else
                {
                    $data['html'] = "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.merchant.payment.merchant') . "</label>" .
                    "<div class=''>" . $transaction->merchant->business_name . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                    "<div class=''>" . $transaction->uuid . "</div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                    "<div class='clearfix'></div>" .
                    "<div class='left '>" . __('message.dashboard.left-table.payment-Sent.payment-amount') . "</div>" .
                    "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                    "<div class='clearfix'></div>" .
                    "<hr/>" .
                    "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                    "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->total))) . "</strong></div>" .
                    "<div class='clearfix'></div>" .
                    "</div>" .
                    "<div class='form-group trans_details'>" .
                    "<a href='" . url('transactions/merchant-payment-print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                        "</div>";

                }
            }
            else if ($transaction->transaction_type_id == Payment_Received)
            {
                $data['html'] = "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.transaction-id') . "</label>" .
                "<div class=''>" . $transaction->uuid . "</div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<label for='exampleInputEmail1'>" . __('message.dashboard.left-table.details') . "</label>" .
                "<div class='clearfix'></div>" .
                "<div class='left '>" . __('message.dashboard.left-table.payment-Sent.payment-amount') . "</div>" .
                "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber(abs($transaction->subtotal))) . "</div>" .
                "<div class='clearfix'></div>" .
                "<div class='left '>" . __('message.dashboard.left-table.fee') . "</div>" .
                "<div class='right '>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->charge_percentage + $transaction->charge_fixed)) . "</div>" .
                "<div class='clearfix'></div>" .
                "<hr/>" .
                "<div class='left '><strong>" . __('message.dashboard.left-table.total') . "</strong></div>" .
                "<div class='right '><strong>" . moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) . "</strong></div>" .
                "<div class='clearfix'></div>" .
                "</div>" .
                "<div class='form-group trans_details'>" .
                "<a href='" . url('transactions/merchant-payment-print/' . $transaction->id) . "' target='_blank' class='btn btn-secondary btn-sm'>" . __('message.dashboard.vouchers.success.print') . "</a> &nbsp;&nbsp;" .
                    "</div>";
            }
            else
            {
                $data['html'] = '';
            }
        }
        return json_encode($data);
    }

    /**
     * Generate pdf print for exchangeTransaction entries
     */
    public function exchangeTransactionPrintPdf($id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first();

        // $data['transaction'] = $transaction = Transaction::where(['id' => $id])->first();
        $data['transaction'] = $transaction = Transaction::with([
            'currency:id,code,symbol',
        ])->where(['id' => $id])->first();
        // dd($transaction);

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
        $mpdf->WriteHTML(view('user_dashboard.transactions.exchangeTransactionPrintPdf', $data));
        $mpdf->Output('exchange_' . time() . '.pdf', 'I'); // this will output data
    }

    /**
     * Generate pdf print for merchant payment entries
     */
    public function merchantPaymentTransactionPrintPdf($id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first();

        // $data['transaction'] = $transaction = Transaction::where(['id' => $id])->first();
        $data['transaction'] = $transaction = Transaction::with([
            'merchant:id,business_name',
            'currency:id,symbol',
        ])->where(['id' => $id])->first();
        // dd($transaction);

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
        $mpdf->WriteHTML(view('user_dashboard.transactions.merchantPaymentTransactionPrintPdf', $data));
        $mpdf->Output('merchant-payment_' . time() . '.pdf', 'I'); // this will output data
    }
}


//DB transaction template below

/* WITH MAIL ROLLBACK
try
{
    \DB::beginTransaction();

    //Save to tables

    //Mail or SMS try catch
    try
    {
        //send mail or sms
    }
    catch (\Exception $e)
    {
        \DB::rollBack();
        clearActionSession();
        $this->helper->one_time_message('error', $e->getMessage());
        return redirect('');
    }

    \DB::commit();
    // return;
}
catch (\Exception $e)
{
    \DB::rollBack();
    $this->helper->one_time_message('error', $e->getMessage());
    return redirect('');
}
*/

///////////////////////////////////////////////////////////////

/* USUAL APPROACH
try
{
    \DB::beginTransaction();
    \DB::commit();
}
catch (\Exception $e)
{
    \DB::rollBack();
    $this->helper->one_time_message('error', $e->getMessage());
    return redirect('');
}
*/

