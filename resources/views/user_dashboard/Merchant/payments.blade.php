@extends('user_dashboard.layouts.app')
@section('content')
    <!--Start Section-->
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    <div class="right mb10">
                        <a href="{{url('/merchant/add')}}" class="btn btn-cust ticket-btn"><i class="fa fa-user"></i>&nbsp;
                            @lang('message.dashboard.button.new-merchant')</a>
                    </div>
                    <div class="clearfix"></div>

                    <div class="card">
                        <div class="card-header">
                            <div class="chart-list float-left">
                                <ul>
                                    <li>
                                        <a href="{{url('/merchants')}}">@lang('message.dashboard.merchant.menu.merchant')</a>
                                    </li>
                                    <li class="active"><a href="#">@lang('message.dashboard.merchant.menu.payment')</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="">
                            <div class="table-responsive">
                                @if($merchant_payments->count() > 0)
                                    <table class="table recent_activity" id="merchant">
                                        <thead>
                                        <tr>
                                            <td><strong>@lang('message.dashboard.merchant.payment.created-at')</strong>
                                            </td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.merchant')</strong>
                                            </td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.method')</strong></td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.order-no')</strong>
                                            </td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.amount')</strong></td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.fee')</strong></td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.total')</strong></td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.currency')</strong>
                                            </td>
                                            <td><strong>@lang('message.dashboard.merchant.payment.status')</strong></td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($merchant_payments as $result)
                                            <tr>
                                                <td>{{ dateFormat($result->created_at) }}</td>
                                                <td>{{ $result->merchant->business_name }}</td>

                                                <td>{{ ($result->payment_method->name == "Mts") ? getCompanyName() : $result->payment_method->name }}</td>

                                                <td>{{ !empty($result->order_no) ? $result->order_no : "-" }}</td>

                                                <td>{{ formatNumber($result->amount)}}</td>

                                                <td>{{ formatNumber($result->charge_percentage + $result->charge_fixed) }}</td>

                                                <td>{{ formatNumber($result->total) }}</td>

                                                <td>{{ $result->currency->code}}</td>

                                                @if($result->status == 'Pending')
                                                    <td>
                                                        <span class="badge badge-primary">@lang('message.dashboard.merchant.payment.pending')</span>
                                                    </td>
                                                @elseif($result->status == 'Success')
                                                    <td>
                                                        <span class="badge badge-success">@lang('message.dashboard.merchant.payment.success')</span>
                                                    </td>
                                                @elseif($result->status == 'Blocked')
                                                    <td>
                                                        <span class="badge badge-danger">@lang('message.dashboard.merchant.payment.block')</span>
                                                    </td>
                                                @elseif($result->status == 'Refund')
                                                    <td>
                                                        <span class="badge badge-warning">@lang('message.dashboard.transaction.refund')</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <h5 style="padding:15px 10px;">@lang('message.dashboard.merchant.table.not-found')</h5>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer">
                            {{ $merchant_payments->links('vendor.pagination.bootstrap-4') }}
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--End Section-->
@endsection
@section('js')
    <script>
    </script>
@endsection