<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            Transactions
        </title>
    </head>
    <style>
        body {
        font-family: "DeJaVu Sans", Helvetica, sans-serif;
        color: #121212;
        line-height: 15px;
    }

    table, tr, td {
        padding: 6px 6px;
        border: 1px solid black;
    }

    tr {
        height: 40px;
    }

    /*logo -- css*/
    .setting-img{
        overflow: hidden;
        max-width: 100%;
    }
    .img-wrap-general-logo {
        /*width: 300px;*/
        overflow: hidden;
        margin: 5px;
        background: rgba(74, 111, 197, 0.9) !important;
        /*height: 100px;*/
        max-width: 100%;
    }
    .img-wrap-general-logo > img {
        max-width: 100%;
        height: auto !important;
        max-height: 100%;
        width: auto !important;
        object-fit: contain;
    }
    /*logo -- css*/
    </style>

    <body>
        <div style="width:100%; margin:0px auto;">
            <div style="height:80px">
                <div style="width:80%; float:left; font-size:13px; color:#383838; font-weight:400;">
                    <div>
                        <strong>
                            {{ ucwords(Session::get('name')) }}
                        </strong>
                    </div>
                    <br>
                    <div>
                        Period : {{ $date_range }}
                    </div>
                    <br>
                    <div>
                        Print Date : {{ dateFormat(\Carbon\Carbon::now()->toDateString())}}
                    </div>
                </div>
                <div style="width:20%; float:left;font-size:15px; color:#383838; font-weight:400;">
                    <div>
                        <div>
                            @if (!empty($company_logo))
                                <div class="setting-img">
                                    <div class="img-wrap-general-logo">
                                        <img src="{{ url('public/images/logos/'.$company_logo) }}" class="img-responsive">
                                    </div>
                                </div>
                            @else
                                <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="200" height="70">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both">
            </div>
            <div style="margin-top:30px;">
                <table style="width:100%; border-radius:1px;  border-collapse: collapse;">
                    <tr style="background-color:#f0f0f0;text-align:center; font-size:12px; font-weight:bold;">

                        <td>Date</td>
                        <td>User</td>
                        <td>Type</td>
                        <td>Amount</td>
                        <td>Fees</td>
                        <td>Total</td>
                        <td>Currency</td>
                        <td>Receiver</td>
                        <td>Status</td>

                    </tr>

                    @foreach($transactions as $transaction)

                    <tr style="background-color:#fff; text-align:center; font-size:12px; font-weight:normal;">

                        <td>{{ dateFormat($transaction->created_at) }}</td>

                        {{-- User --}}
                        @if (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Deposit)
                            <td>{{ isset($transaction->deposit->user) ? $transaction->deposit->user->first_name.' '.$transaction->deposit->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Transferred)
                            <td>{{ isset($transaction->transfer->sender) ? $transaction->transfer->sender->first_name.' '.$transaction->transfer->sender->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Bank_Transfer)
                            <td>{{ isset($transaction->transfer->sender) ? $transaction->transfer->sender->first_name.' '.$transaction->transfer->sender->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Received)
                            <td>{{ isset($transaction->transfer->sender) ? $transaction->transfer->sender->first_name.' '.$transaction->transfer->sender->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Exchange_From)
                            <td>{{ isset($transaction->currency_exchange->user) ? $transaction->currency_exchange->user->first_name.' '.$transaction->currency_exchange->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Exchange_To)
                            <td>{{ isset($transaction->currency_exchange->user) ? $transaction->currency_exchange->user->first_name.' '.$transaction->currency_exchange->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Voucher_Created)
                            <td>{{ isset($transaction->voucher->user) ? $transaction->voucher->user->first_name.' '.$transaction->voucher->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Voucher_Activated)
                            <td>{{ isset($transaction->voucher->user) ? $transaction->voucher->user->first_name.' '.$transaction->voucher->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Request_From)
                            <td>{{ isset($transaction->request_payment->user) ? $transaction->request_payment->user->first_name.' '.$transaction->request_payment->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Request_To)
                            <td>{{ isset($transaction->request_payment->user) ? $transaction->request_payment->user->first_name.' '.$transaction->request_payment->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Withdrawal)
                            <td>{{ isset($transaction->withdrawal->user) ? $transaction->withdrawal->user->first_name.' '.$transaction->withdrawal->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Payment_Sent)
                            <td>{{ isset($transaction->user) ? $transaction->user->first_name.' '.$transaction->user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Payment_Received)
                            <td>{{ isset($transaction->end_user) ? $transaction->end_user->first_name.' '.$transaction->end_user->last_name :"-" }}</td>
                        @endif

                        <td>{{ ($transaction->transaction_type->name == "Withdrawal") ? "Payout" : str_replace('_', ' ', $transaction->transaction_type->name) }}</td>

                        <td>{{ formatNumber($transaction->subtotal) }}</td>

                        <td>{{ ($transaction->charge_percentage == 0) && ($transaction->charge_fixed == 0) ? '-' : formatNumber($transaction->charge_percentage + $transaction->charge_fixed) }}</td>

                        <td>{{ formatNumber($transaction->total) }}</td>

                        <td>{{ $transaction->currency->code }}</td>

                        {{-- Receiver --}}
                        @if (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Deposit)
                            <td>-</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Transferred)
                            <td>
                                @if ($transaction->transfer->receiver)
                                {{ $transaction->transfer->receiver->first_name.' '.$transaction->transfer->receiver->last_name }}
                                @elseif ($transaction->transfer->email)
                                    {{ $transaction->transfer->email }}
                                @elseif ($transaction->transfer->phone)
                                    {{ $transaction->transfer->phone }}
                                @else
                                    {{ '-' }}
                                @endif
                            </td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Bank_Transfer)
                            <td>-</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Received)
                             <td>
                                @if ($transaction->transfer->receiver)
                                {{ $transaction->transfer->receiver->first_name.' '.$transaction->transfer->receiver->last_name }}
                                @elseif ($transaction->transfer->email)
                                    {{ $transaction->transfer->email }}
                                @elseif ($transaction->transfer->phone)
                                    {{ $transaction->transfer->phone }}
                                @else
                                    {{ '-' }}
                                @endif
                            </td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Exchange_From)
                            <td>-</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Exchange_To)
                            <td>-</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Voucher_Created)
                            <td>-</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Voucher_Activated)
                            <td>{{ isset($transaction->voucher->activator) ? $transaction->voucher->activator->first_name.' '.$transaction->voucher->activator->last_name :"-" }}</td>


                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Request_From)
                            <td>{{ isset($transaction->request_payment->receiver) ? $transaction->request_payment->receiver->first_name.' '.$transaction->request_payment->receiver->last_name : $transaction->request_payment->email }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Request_To)
                            <td>{{ isset($transaction->request_payment->receiver) ? $transaction->request_payment->receiver->first_name.' '.$transaction->request_payment->receiver->last_name : $transaction->request_payment->email }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Withdrawal)
                            <td>-</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Payment_Sent)
                            <td>{{ isset($transaction->end_user) ? $transaction->end_user->first_name.' '.$transaction->end_user->last_name :"-" }}</td>

                        @elseif (isset($transaction->transaction_type_id) && $transaction->transaction_type_id == Payment_Received)
                            <td>{{ isset($transaction->user) ? $transaction->user->first_name.' '.$transaction->user->last_name :"-" }}</td>
                        @endif

                        <td>{{ (($transaction->status == 'Blocked') ? "Cancelled" :(($transaction->status == 'Refund') ? "Refunded" : $transaction->status)) }}</td>

                    </tr>
                    @endforeach

                </table>
            </div>
        </div>
    </body>
</html>
