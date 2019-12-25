@extends('user_dashboard.layouts.app')
@section('content')
<section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                <div class="card">
                    <div class="card-header">
                        <h4>@lang('message.dashboard.send-request.request.success.title')</h4>
                    </div>
                     <div class="wap-wed mt20 mb20">
                        <div class="text-center">
                          <div class="confirm-btns"><i class="fa fa-check"></i></div>
                        </div>
                        <div class="text-center">
                            <div class="h3 mt6 text-success">@lang('message.dashboard.send-request.request.confirmation.success')!</div>
                        </div>
                         <div class="text-center"><p><strong>@lang('message.dashboard.send-request.request.success.request-complete')</strong></p></div>
                         <div class="text-center mt10 img-success">
                              @if(!empty(@$requestCreator->picture))
                                <img style="" src="{{url('public/user_dashboard/profile')}}/{{$requestCreator->picture}}">
                              @else
                                <img style="" src="{{url('public/images')}}/avatar.png">
                              @endif
                         </div>
                         <h4 class="text-center mt10"><strong>{{ $requestCreator->first_name.' '.$requestCreator->last_name }}</strong></h4>

                        <h5 class="text-center mt10">@lang('message.dashboard.send-request.request.success.accept-amount') : {{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['amount'])) }}</h5>
                     </div>

                    <div class="card-footer" style="margin-top: 10px">
                            <div class="text-center">
                                <a href="{{url('request-payment/print')}}/{{$transInfo['trans_id']}}" target="_blank" class="btn btn-cust">
                                <strong>@lang('message.dashboard.vouchers.success.print')</strong>
                                </a>
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