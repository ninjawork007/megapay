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
						   <p class="mb20">@lang('message.dashboard.exchange.confirm.exchanging') <strong>{{ $transInfo['currCode'] }}</strong> @lang('message.dashboard.exchange.confirm.of') <strong>{{ isset($transInfo['defaultAmnt']) ? formatNumber($transInfo['defaultAmnt']) : 0.00 }}</strong> @lang('message.dashboard.exchange.confirm.equivalent-to') <strong>{{ isset($transInfo['convertedAmnt']) ? formatNumber($transInfo['convertedAmnt']) : 0.00 }} {{$defaultCurrency->code}}</strong><br/>@lang('message.dashboard.exchange.confirm.exchange-rate'):&nbsp;<strong>1 {{$transInfo['currCode']}} </strong>= {{formatNumber((1/$transInfo['dCurrencyRate']))}} {{ $defaultCurrency->code }}</p>
						   <div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>
						   <div class="confn-border">
						      <div class="row mt20">
							  <div class="col-md-6">@lang('message.dashboard.exchange.confirm.amount')</div>
							  {{-- <div class="col-md-6 text-right"><strong>{{$transInfo['currSymbol']}} {{ isset($transInfo['amount']) ? formatNumber($transInfo['amount']) : 0.00 }}</strong></div> --}}
							  <div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], isset($transInfo['amount']) ? formatNumber($transInfo['amount']) : 0.00) }}</strong></div>
							</div>
	                          <div class="row mt10">
							  <div class="col-md-6">@lang('message.dashboard.confirmation.fee')</div>

							  {{-- <div class="col-md-6 text-right"><strong>{{$transInfo['currSymbol']}} {{ isset($transInfo['fee']) ? formatNumber($transInfo['fee']) : 0.00 }}</strong></div> --}}
							  <div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], isset($transInfo['fee']) ? formatNumber($transInfo['fee']) : 0.00) }}</strong></div>

							</div>
							<hr />
							  <div class="row">
							  <div class="col-md-6 h6"><strong>@lang('message.dashboard.confirmation.total')</strong></div>
							  {{-- <div class="col-md-6 text-right"><strong>{{$transInfo['currSymbol']}} {{ isset($transInfo['totalAmount']) ? formatNumber($transInfo['totalAmount']) : 0.00 }}</strong></div> --}}
							  <div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], isset($transInfo['totalAmount']) ? formatNumber($transInfo['totalAmount']) : 0.00) }}</strong></div>
							</div>
			  				</div>
						 </div>

	                    <div class="card-footer">
						    <div class="text-center">
								  <a onclick="window.history.back();" href="#" class="btn btn-cust float-left">
								     <strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
								  </a>

								  <a href="{{url('exchange/exchange-to-base-currency-confirm')}}" class="btn btn-cust float-right">
								     <strong>@lang('message.dashboard.button.confirm') &nbsp; <i class="fa fa-angle-right"></i></strong>
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