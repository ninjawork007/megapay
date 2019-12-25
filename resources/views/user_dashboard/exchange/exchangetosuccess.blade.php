@extends('user_dashboard.layouts.app')
@section('content')
<section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                <div class="card">
                    <div class="card-header">
                        <h4>@lang('message.dashboard.exchange.confirm.title')</h4>
                    </div>
                     <div class="wap-wed mt20 mb20">
                        <div class="text-center">
                          <div class="confirm-btns"><i class="fa fa-check"></i></div>
                        </div>
                        <div class="text-center">
                            <div class="h3 mt6 text-success">@lang('message.dashboard.deposit.success')!</div>
                        </div>
                         <!-- <div class="text-center"><p><strong>Your Money Successfully Sent.</strong></p></div>
                         <div class="text-center mt10 img-success">
                                <img style="" src="http://parvez-pc/paymoney2/public/images/avatar.png">
                         </div>
                         <h4 class="text-center mt10"><strong>Aminul</strong></h4> -->
                        <p style="text-align: center;"><strong>{{$transInfo['currCode']}}</strong> of <strong>{{ formatNumber($transInfo['defaultAmnt']) }}</strong> @lang('message.dashboard.exchange.confirm.has-exchanged-to') <strong>
                        {{  moneyFormat($defaultCurrency->code, isset($transInfo['convertedAmnt']) ? formatNumber($transInfo['convertedAmnt']) : 0.00) }}

                        </strong><br/>@lang('message.dashboard.exchange.confirm.exchange-rate'):<strong>1 {{$transInfo['currCode']}}</strong> = {{formatNumber((1/$transInfo['dCurrencyRate']))}} {{$defaultCurrency->code}}</p>
                        <!-- <h5 class="text-center mt10">Total Amount : {{$defaultCurrency->symbol}} {{$transInfo['totalAmount']}}</h5>  -->
                     </div>

                    <div class="card-footer" style="margin-top: 10px">
                        <div class="text-center">
                          <a href="{{url('exchange-to-money/print')}}/{{$transInfo['trans_ref_id']}}" target="_blank" class="btn btn-cust">
                                      <strong>@lang('message.dashboard.vouchers.success.print')</strong>
                                    </a>
                          <a href="{{url('exchange')}}" class="btn btn-cust">
                                      <strong>@lang('message.dashboard.exchange.confirm.exchange-money-again')</strong>
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
     $(document).ready(function() {
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
    });

    //For displaying warning on reload and disabling F5 and ctril+R
    // $(document).ready(function() {
    //   $(window).bind('beforeunload', function(){
    //     return '';
    //   });
    // });


    //disabling F5
    function disable_f5(e)
    {
      if ((e.which || e.keyCode) == 116)
      {
          e.preventDefault();
      }
    }
    $(document).ready(function(){
        $(document).bind("keydown", disable_f5);
    });

    //disabling ctrl+r
    function disable_ctrl_r(e)
    {
      if(e.keyCode == 82 && e.ctrlKey)
      {
        e.preventDefault();
      }
    }
    $(document).ready(function(){
        $(document).bind("keydown", disable_ctrl_r);
    });
</script>
@endsection