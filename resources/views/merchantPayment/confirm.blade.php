<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{!isset($exception) ? meta(Route::current()->uri(),'description'):$exception->description}}">
        <meta name="keywords" content="{{!isset($exception) ? meta(Route::current()->uri(),'keyword'):$exception->keyword}}">
        <title>{{!isset($exception) ? meta(Route::current()->uri(),'title'):$exception->title}} <?= isset($additionalTitle)?'| '.$additionalTitle :'' ?></title>
        <link rel="javascript" href="{{asset('public/frontend/js/respond.js')}}">
        <link rel="shortcut icon" href="{{asset('public/images/logos/'.getfavicon())}}" />
        @include('user_dashboard.layouts.common.style')
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3 marginTopPlus">
                
              		<h3 style="margin-bottom:15px;">Transaction Confirm </h3>
                    <div class="card">
                        <div class="card-header">

                        	<h4>@lang('message.express-payment.pay-with') {{$transInfo->currency}}</h4>
                        </div>

                        <div class="wap-wed mt20 mb20">

                            <p class="mb20"><strong>@lang('message.express-payment.about-to-make')&nbsp;{{$transInfo->currency}}&nbsp;<strong>
                            </strong></strong></p>

                            <div class="h5"><strong>@lang('message.dashboard.left-table.details')</strong></div>
                            <div class="row mt20">
                                <div class="col-md-6">@lang('message.dashboard.left-table.amount')</div>
                                <div class="col-md-6 text-right">
                                    <strong>{{$currSymbol}} {{ formatNumber($total_amount) }}</strong>
                                </div>
                            </div>
                            <br>

                            {{-- @php
                                $p_calc = ($transInfo->app->merchant->fee * $transInfo->amount) / 100;
                                $p_calc = number_format((float) $p_calc, 2, '.', ''); //fixed in PayMoney v2.1
                            @endphp

                            <div class="row mt10">
                                <div class="col-md-6">@lang('message.dashboard.left-table.fee')</div>
                                <div class="col-md-6 text-right">
                                    <strong>{{$currSymbol}} {{ formatNumber($p_calc) }}</strong>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="col-md-6 h6"><strong>@lang('message.dashboard.left-table.total')</strong></div>
                                <div class="col-md-6 text-right">
                                    <strong>{{$currSymbol}} {{ formatNumber($transInfo->amount + $p_calc) }}</strong>
                                </div>
                            </div> --}}
                        </div>

                        <div class="card-footer">
                            <div style="float: left;">
                                <form action="{{ url('merchant/payment/cancel') }}" method="get">
                                	{{ csrf_field() }}
                                    <button class="btn btn-cust">
                                        <strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.form.cancel')</strong>
                                    </button>
                                </form>
                            </div>

                            <div style="float: right;">
                                <form action="{{url('merchant/payment/confirm')}}" method="get">
                                	{{ csrf_field() }}
                                    <button type="submit" class="btn btn-cust">
                                        <strong>@lang('message.dashboard.button.confirm') &nbsp; <i class="fa fa-angle-right"></i></strong>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>