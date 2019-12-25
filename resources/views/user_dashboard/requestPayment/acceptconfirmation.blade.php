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
						   	   <div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>
							   <div class="row mt20">
									<div class="col-md-6">@lang('message.dashboard.send-request.request.success.accept-amount')</div>
									<div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['amount'])) }}</strong></div>
								</div>
		                        <div class="row mt10">
									<div class="col-md-6">@lang('message.dashboard.confirmation.fee')</div>
									<div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['fee'])) }}</strong></div>
								</div>
								<hr />
								<div class="row">
									<div class="col-md-6 h6"><strong>@lang('message.dashboard.confirmation.total')</strong></div>
									<div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['totalAmount'])) }}</strong></div>
								</div>
							</div>

		                    <div class="card-footer">
							    <div class="text-center">
								  <a href="{{url('request_payment/accept/'.$requestPaymentId)}}" class="acceptRequestPaymentBackLink">
								  	<button class="btn btn-cust float-left acceptRequestPaymentBack"><strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong></button>
								  </a>

								  <a href="{{url('request_payment/accept-money-confirm')}}" class="acceptRequestPaymentConfirmLink">
								    <button class="btn btn-cust float-right acceptRequestPaymentConfirm">
								    	<i class="fa fa-spinner fa-spin" style="display: none;" id="spinner"></i>
								    	<strong>
								    		<span class="acceptRequestPaymentConfirmText">
								    			@lang('message.dashboard.button.confirm') &nbsp; <i class="fa fa-angle-right"></i>
								    		</span>
								    	</strong>
								    </button>
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

	$(document).on('click', '.acceptRequestPaymentConfirm', function (e)
    {
    	// e.preventDefault();
    	$(".fa-spin").show()
    	$('.acceptRequestPaymentConfirmText').text('Confirming...');
    	$(this).attr("disabled", true);
    	$('.acceptRequestPaymentConfirmLink').click(false);
    	$('.acceptRequestPaymentBack').attr("disabled", true).click(false);
    	$('.acceptRequestPaymentBackLink').click(false);
    	// window.location.href = '{{url('request_payment/accept-money-confirm')}}';
    });


</script>

@endsection