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

                        <p class="mb20">@lang('message.dashboard.deposit.deposit-via')&nbsp;&nbsp;<strong><img src="{{asset("public/images/payment_gateway")}}/{{ strtolower($transInfo['payment_name']) }}"/></strong></p>
                        <div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>

                        <div class="row mt20">
                            <div class="col-md-6">@lang('message.dashboard.deposit.deposit-amount')</div>
                            <div class="col-md-6 text-right"><strong>{{ $transInfo['currSymbol'] }} {{ isset($transInfo['amount']) ? formatNumber($transInfo['amount']) : 0.00 }}</strong></div>
                        </div>

                        <div class="row mt10">
                            <div class="col-md-6">@lang('message.dashboard.confirmation.fee')</div>
                            <div class="col-md-6 text-right"><strong>{{ $transInfo['currSymbol'] }} {{ isset($transInfo['fee']) ? formatNumber($transInfo['fee']) : 0.00 }}</strong></div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6 h6"><strong>@lang('message.dashboard.confirmation.total')</strong></div>
                            <div class="col-md-6 text-right"><strong>{{ $transInfo['currSymbol'] }} {{ isset($transInfo['totalAmount']) ? formatNumber($transInfo['totalAmount']) : 0.00 }}</strong></div>
                        </div>
					 </div>

                    <div class="card-footer" style="margin-left: 0 auto">
					    <div style="float: left;">
							  <a onclick="depositBack()" href="#" class="btn btn-cust">
							     <strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
							  </a>
						</div>
					    <div style="float: right;">
                            <form action="{{url('deposit/store')}}" style="display: block;" method="POST" accept-charset="UTF-8" id="deposit_form" novalidate="novalidate" enctype="multipart/form-data">
                                <input value="{{csrf_token()}}" name="_token" id="token" type="hidden">
                                <input value="{{$transInfo['payment_method']}}" name="method" id="method" type="hidden">
                                <input value="{{$transInfo['totalAmount']}}" name="amount" id="amount" type="hidden">
								<button type="submit" class="btn btn-cust" id="send_money">
		                  			<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="send_text" style="font-weight: bolder;">@lang('message.dashboard.button.confirm')&nbsp; <i class="fa fa-angle-right"></i></span>
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
@include('user_dashboard.layouts.common.help')
@endsection

@section('js')
<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script>

    function depositBack()
    {
        localStorage.setItem("depositConfirmPreviousUrl",document.URL);
        window.history.back();
    }


    jQuery.extend(jQuery.validator.messages, {
        required: "{{__('This field is required.')}}",
    })

    $('#deposit_form').validate({
        rules: {
            amount: {
                required: false,
            },
            method: {
                required: false,
            },
        },
        submitHandler: function(form)
        {
            $("#send_money").attr("disabled", true);
            $(".spinner").show();
            var pretext=$("#send_text").text();
            $("#send_text").text('Sending...');
            form.submit();
            setTimeout(function(){
                $("#send_money").removeAttr("disabled");
                $(".spinner").hide();
                $("#send_text").text(pretext);
            },10000);
        }
    });

</script>
@endsection