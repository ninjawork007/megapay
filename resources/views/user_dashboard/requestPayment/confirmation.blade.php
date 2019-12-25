@extends('user_dashboard.layouts.app')
@section('content')
	<section class="section-06 history padding-30">
		<div class="container">
			<div class="row">
				<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
					<div class="card">
						<div class="card-header">
							<h4>@lang('message.dashboard.send-request.request.confirmation.title')</h4>
						</div>
						<div class="wap-wed mt20 mb20">
							<p class="mb20">@lang('message.dashboard.send-request.request.confirmation.request-money-from')&nbsp;&nbsp;<strong>{{ isset($transInfo['email']) ? $transInfo['email'] : '' }}</strong></p>
							<div class="row mt20 mb20">
								<div class="col-md-6"><strong>@lang('message.dashboard.send-request.request.confirmation.requested-amount')</strong></div>
								<div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['amount'])) }}</strong></div>
							</div>

						</div>

						<div class="card-footer">
							<div class="text-center">
								<a onclick="window.history.back();" href="#" class="btn btn-cust float-left">
									<strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
								</a>
								<a href="{{url('request-money-confirm')}}" class="btn btn-cust float-right">
									<strong>@lang('message.dashboard.button.confirm') &nbsp; <i class="fa fa-angle-right"></i></strong>
								</a>
								<br>
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