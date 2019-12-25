@extends('admin.layouts.master')

@section('title', 'Transactions Details')

@section('page_content')

<div class="box">
    <div class="box-body">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Transactions ID: {{ $transactions->uuid }}</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table text-center" id="transactions">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Payment Method</th>
                                    <th>Transaction Type</th>
                                    <th>SubTotal</th>
                                    <th>Fees</th>
                                    <th>Total</th>
                                    <th>Currency</th>

                                    <th>Sender</th>
                                    <th>Receiver</th>

                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $transactions->id }}</td>

                                    @if ($transactions->payment_method == null)
                                        <td><span>-</span></td>
                                    @else
                                        <td><span>{{ $transactions->payment_method->name }}</span></td>
                                    @endif
                                    <td>{{ $transactions->type }}</td>
                                    <td>{{ $transactions->subtotal }}</td>
                                    <td>{{ $transactions->fee }}</td>
                                    <td>{{ $transactions->total }}</td>

                                    <td>{{ $transactions->currency->code }}</td>

                                    @if ($transactions->type == 'Deposit')
                                        <td>{{ $transactions->deposit->user->first_name.' '.$transactions->deposit->user->last_name }}</td>
                                        <td>-</td>

                                    @elseif ($transactions->type == 'Transferred')
                                        <td>{{ $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name }}</td>
                                        <td>{{ $transactions->transfer->receiver->first_name.' '.$transactions->transfer->receiver->last_name }}</td>

                                    @elseif ($transactions->type == 'Received')
                                        <td>{{ $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name }}</td>
                                        <td>{{ $transactions->transfer->receiver->first_name.' '.$transactions->transfer->receiver->last_name }}</td>

                                    @elseif ($transactions->type == 'Exchange_From')
                                        <td>{{ $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name }}</td>
                                        <td>-</td>

                                    @elseif ($transactions->type == 'Exchange_To')
                                        <td>{{ $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name }}</td>
                                        <td>-</td>

                                    @elseif ($transactions->type == 'Voucher')
                                        <td>{{ $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name }}</td>
                                        <td>-</td>

                                    @elseif ($transactions->type == 'Request_Payment')
                                        <td>{{ $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name }}</td>
                                        <td>{{ $transactions->request_payment->receiver->first_name.' '.$transactions->request_payment->receiver->last_name }}</td>
                                    @endif

                                    @if ($transactions->status == 'Success')
                                            <td><span class="label label-success">Success</span></td>
                                    @elseif ($transactions->status == 'Pending')
                                        <td><span class="label label-primary">Pending</span></td>
                                    @elseif ($transactions->status == 'Refund')
                                        <td><span class="label label-success">Refund</span></td>
                                    @elseif ($transactions->status == 'Blocked')
                                        <td><span class="label label-danger">Blocked</span></td>
                                    @endif

                                    <td>{{ dateFormat($transactions->created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@push('extra_body_scripts')
<script type="text/javascript">
</script>
@endpush
