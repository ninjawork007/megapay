@extends('user_dashboard.layouts.app')
@section('content')
	<section class="section-06 history padding-30">
		<div class="container">
			<div class="row">
				<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
					<div class="card">
						<div class="card-header">
							<h4>@lang('message.dashboard.send-request.send.confirmation.title')</h4>
						</div>
						<div class="wap-wed mt20 mb20">
							<p class="mb20">@lang('message.dashboard.send-request.send.confirmation.send-to')&nbsp;&nbsp;<strong>{{ isset($transInfo['receiver']) ? $transInfo['receiver'] : '' }}</strong></p>
							<div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>
							<div class="row mt20">
								<div class="col-md-6">@lang('message.dashboard.send-request.send.confirmation.transfer-amount')</div>
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
								{{-- <a href="#" class="sendMoneyPaymentBackLink">
									<button class="btn btn-cust float-left sendMoneyPaymentBack"><strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong></button>
								</a> --}}

								<a onclick="window.history.back();" href="#" class="btn btn-cust float-left">
								 	<strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
								</a>

								<a href="{{url('send-money-confirm')}}" class="sendMoneyPaymentConfirmLink">
									<button class="btn btn-cust float-right sendMoneyConfirm">
								    	<i class="fa fa-spinner fa-spin" style="display: none;" id="spinner"></i>
								    	<strong>
								    		<span class="sendMoneyConfirmText">
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
	$(document).on('click', '.sendMoneyConfirm', function (e)
    {
    	$(".fa-spin").show()
    	$('.sendMoneyConfirmText').text('Confirming...');
    	$(this).attr("disabled", true);
    	$('.sendMoneyPaymentConfirmLink').click(false);
    	// $('.sendMoneyPaymentBack').attr("disabled", true).click(false);
    	// $('.sendMoneyPaymentBackLink').click(false);
    });

    // $(document).on('click', '.sendMoneyPaymentBackLink', function (e)
    // {
    // 	window.history.back();
    // });
</script>
@endsection
