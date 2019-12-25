@extends('user_dashboard.layouts.app')
@section('content')
<section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                <div class="card">
                    <div class="card-header">
                        <h4>@lang('message.dashboard.deposit.title')</h4>
                    </div>
                     <div class="wap-wed mt20 mb20">
                        <div class="text-center">
                          <div class="confirm-btns"><i class="fa fa-check"></i></div>
                        </div>
                        <div class="text-center">
                            <div class="h3 mt6 text-success"> @lang('message.dashboard.deposit.success')!</div>
                        </div>
                         <div class="text-center"><p><strong> @lang('message.dashboard.deposit.completed-success')</strong></p></div>
                          <h5 class="text-center mt10">@lang('message.dashboard.deposit.deposit-amount') : {{$transaction->currency->symbol}} {{ formatNumber($transaction->subtotal) }}</h5>
                     </div>
                    <div class="card-footer" style="margin-top: 10px">
                        <div class="text-center">
                          <a href="{{url('deposit-money/print')}}/{{$transaction->id}}" target="_blank" class="btn btn-cust"><strong>@lang('message.dashboard.vouchers.success.print')</strong></a>

                          <a href="{{url('deposit')}}" class="btn btn-cust"><strong>@lang('message.dashboard.deposit.deposit-again')</strong></a>
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