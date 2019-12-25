@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    <div class="card">
                        <div class="card-header">
                            <h4>@lang('message.dashboard.vouchers.confirmation.title')</h4>
                        </div>
                        <div class="wap-wed mt20 mb20">
                            <p class="mb20">@lang('message.dashboard.vouchers.confirmation.sub-title')</p>
                             <div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>
                            <div class="row mt20">
                                <div class="col-md-6">@lang('message.dashboard.vouchers.confirmation.amount')</div>
                                {{-- <div class="col-md-6 text-right"><strong>{{ $currency }} {{ isset($amount) ? decimalFormat($amount) : 0.00 }}</strong></div> --}}

                                <div class="col-md-6 text-right"><strong>{{  moneyFormat($currency, isset($amount) ? formatNumber($amount) : 0.00) }}</strong></div>
                            </div>
                            <div class="row mt10">
                                <div class="col-md-6">@lang('message.dashboard.confirmation.fee')</div>
                                {{-- <div class="col-md-6 text-right"><strong>{{ $currency }} {{ isset($fees) ? decimalFormat($fees) : 0.00 }}</strong></div> --}}

                                <div class="col-md-6 text-right"><strong>{{  moneyFormat($currency, isset($fees) ? formatNumber($fees) : 0.00) }}</strong></div>
                            </div>
                            <hr />
                            <div class="row">
                                <div class="col-md-6 h6"><strong>@lang('message.dashboard.confirmation.total')</strong></div>
                                {{-- <div class="col-md-6 text-right"><strong>{{ $currency }} {{ isset($totalAmount) ? decimalFormat($totalAmount) : 0.00 }}</strong></div> --}}

                                <div class="col-md-6 text-right"><strong>{{  moneyFormat($currency, isset($totalAmount) ? formatNumber($totalAmount) : 0.00) }}</strong></div>
                            </div>

                        </div>

                        <div class="card-footer">
                            <div class="text-center">
                                <a onclick="window.history.back();" href="#" class="btn btn-cust float-left">
                                    <strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
                                </a>
                                <form action="{{url('voucher/store')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="amount" value="{{$amount}}">
                                    <input type="hidden" name="currency_id" value="{{$currency_id}}">
                                    <button type="submit" class="btn btn-cust float-right">
                                        <strong>@lang('message.dashboard.button.confirm') &nbsp; <i class="fa fa-angle-right"></i></strong>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>
    </section>

@endsection
