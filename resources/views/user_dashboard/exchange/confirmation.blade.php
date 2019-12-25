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
						<p class="mb20">@lang('message.dashboard.exchange.confirm.exchanging') <strong>{{ $fromCurrency->code }}</strong>
							@lang('message.dashboard.exchange.confirm.of') <strong>{{ isset($transInfo['defaultAmnt']) ? formatNumber($transInfo['defaultAmnt']) : 0.00 }}</strong>
							@lang('message.dashboard.exchange.confirm.equivalent-to') <strong>{{ isset($transInfo['finalAmount']) ? formatNumber($transInfo['finalAmount']) : 0.00 }} {{ $transInfo['currCode'] }}</strong><br/>@lang('message.dashboard.exchange.confirm.exchange-rate'):  &nbsp;<strong>1 {{$fromCurrency->code}} </strong>= <strong>{{ ($transInfo['dCurrencyRateHtml']) }} {{ $transInfo['currCode'] }}</strong></p>

						<div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>
						<div class="confn-border">
							<div class="row mt20">
								<div class="col-md-6">@lang('message.dashboard.exchange.confirm.amount')</div>
								<div class="col-md-6 text-right"><strong>{{  moneyFormat($fromCurrency->symbol, isset($transInfo['defaultAmnt']) ? formatNumber($transInfo['defaultAmnt']) : 0.00) }}</strong></div>
							</div>

							<div class="row mt10">
								<div class="col-md-6">@lang('message.dashboard.confirmation.fee')</div>
								<div class="col-md-6 text-right"><strong>{{  moneyFormat($fromCurrency->symbol, isset($transInfo['fee']) ? formatNumber($transInfo['fee']) : 0.00) }}</strong></div>
							</div>
							<hr />
							<div class="row">
								<div class="col-md-6 h6"><strong>@lang('message.dashboard.confirmation.total')</strong></div>
								<div class="col-md-6 text-right"><strong>{{  moneyFormat($fromCurrency->symbol, isset($transInfo['totalAmount']) ? formatNumber($transInfo['totalAmount']) : 0.00) }}</strong></div>
							</div>
						</div>
					</div>

					<div class="card-footer">
						<div class="text-center">
							<a onclick="exchangeBack()" href="#" class="btn btn-cust float-left">
								<strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
							</a>
							<form action="{{url('exchange-of-money-success')}}" method="GET" accept-charset="UTF-8" id="exchange-confirm" novalidate="novalidate">
                                <button type="submit" class="btn btn-cust float-right" id="send_money">
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
@endsection

@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">
	function exchangeBack()
	{
		localStorage.setItem("previousUrl",document.URL);
		window.history.back();
	}

	$('#exchange-confirm').validate(
    {
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