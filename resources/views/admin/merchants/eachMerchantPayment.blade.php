@extends('admin.layouts.master')

@section('title', 'Edit Merchant Payment')

@section('head_style')

<!-- dataTables -->
<link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')

<div class="box">
   <div class="panel-body">
        <ul class="nav nav-tabs cus" role="tablist">
            <li>
              <a href='{{url("admin/merchant/edit/$merchant->id")}}'>Profile</a>
            </li>

            <li class="active">
              <a href="{{url("admin/merchant/payments/$merchant->id")}}">Payments</a>
            </li>
       </ul>
      <div class="clearfix"></div>
   </div>
</div>

<div class="row">
    <div class="col-md-10">
        <h4 class="pull-left">{{ $merchant->business_name }}</h4>
    </div>
    <div class="col-md-2">
        @if ($merchant->status)
            <h4 class="pull-right">@if ($merchant->status == 'Approved')<span class="text-green">Approved</span>@endif
            @if ($merchant->status == 'Moderation')<span class="text-blue">Moderation</span>@endif
            @if ($merchant->status == 'Disapproved')<span class="text-red">Disapproved</span>@endif</h4>
        @endif
    </div>
</div>

<div class="box">
  <div class="box-body">
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-hover" id="eachMerchantPayment">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Fees</th>
                        <th>Total</th>
                        <th>Currency</th>
                        <th>Status</th>
                        @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_merchant_payment'))
                        <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if ($merchant_payments)
                        @foreach($merchant_payments as $merchant_payment)
                            <tr>
                                <td>{{ dateFormat($merchant_payment->created_at) }}</td>

                                <td>{{ isset($merchant_payment->user) ? $merchant_payment->user->first_name.' '.$merchant_payment->user->last_name :"-" }}</td>

                                <td>{{ $merchant_payment->payment_method->name }}</td>

                                <td>{{ formatNumber($merchant_payment->amount) }}</td>

                                <td>{{ ($merchant_payment->charge_percentage == 0) && ($merchant_payment->charge_fixed == 0) ? "-" : formatNumber($merchant_payment->charge_percentage + $merchant_payment->charge_fixed) }}</td>

                                @php
                                    $total = $merchant_payment->charge_percentage + $merchant_payment->charge_fixed + $merchant_payment->amount;
                                @endphp

                                @if ($total > 0)
                                    <td><span class="text-green">+ {{ formatNumber($total) }} </span></td>
                                @else
                                    <td><span class="text-red"> {{ ($total) }} </span></td>
                                @endif

                                <td>{{ $merchant_payment->currency->code }}</td>

                                @if ($merchant_payment->status == 'Success')
                                    <td><span class="label label-success">Success</span></td>
                                @elseif ($merchant_payment->status == 'Pending')
                                    <td><span class="label label-primary">Pending</span></td>
                                @elseif ($merchant_payment->status == 'Refund')
                                    <td><span class="label label-warning">Refunded</span></td>
                                @elseif ($merchant_payment->status == 'Blocked')
                                    <td><span class="label label-danger">Cancelled</span></td>
                                @endif

                                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_merchant_payment'))
                                <td>
                                    <a class="btn btn-xs btn-primary" href="{{url('admin/merchant_payments/edit/'.$merchant_payment->id)}}"><i class="glyphicon glyphicon-edit"></i></a>
                                </td>
                                @endif

                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
      $("#eachMerchantPayment").DataTable({
            "order": [],
            "columnDefs": [
            {
                "className": "dt-center",
                "targets": "_all"
            }
            ],
            "language": '{{Session::get('dflt_lang')}}',
            "pageLength": '{{Session::get('row_per_page')}}'
        });
    });
</script>
@endpush
