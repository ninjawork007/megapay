@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    <div class="card">
                        <div class="card-header">
                            <h4>@lang('message.dashboard.vouchers.success.title')</h4>
                        </div>
                        <div class="wap-wed mt20 mb20">
                            <div class="text-center">
                                <div class="confirm-btns"><i class="fa fa-check"></i></div>
                            </div>
                            <div class="text-center">
                                <div class="h3 mt6 text-success">@lang('message.dashboard.vouchers.success.success')!</div>
                            </div>
                            <div class="text-center"><p><strong>{{$message}}</strong></p></div>

                            {{-- <h5 class="text-center mt10">@lang('message.dashboard.vouchers.success.amount') : {{$currency_code}} {{decimalFormat($totalAmount)}}</h5> --}}
                            <h5 class="text-center mt10">@lang('message.dashboard.vouchers.success.amount') : {{  moneyFormat($currency_code, formatNumber($totalAmount)) }}</h5>
                        </div>
                        <div class="card-footer" style="margin-top: 10px">
                            <div class="text-center">
                                <a href="{{ url("voucher/print/$transaction_id") }}" target="_blank" class="btn btn-cust"><strong>@lang('message.dashboard.vouchers.success.print')</strong></a>

                                <a href="{{url('vouchers')}}" class="btn btn-cust"><strong>{{$btnText}}</strong></a>
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
@section('js')
    <script type="text/javascript">

        function printFunc(){
            window.print();
        }
        $(document).ready(function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        });
    </script>
@endsection