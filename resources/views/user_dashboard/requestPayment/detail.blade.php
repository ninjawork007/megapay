@extends('frontend.layouts.app')
@section('content')
@include('frontend.layouts.common.content_title')
<section class="section-02 history padding-30">
	<div class="container">
		<div class="row">
			@include('frontend.layouts.common.dashboard_menu')
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col-md-6">
								<h4 class="float-left">Request Payment</h4>
							</div>
						</div>
					</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Date</label>
								<p class="form-control-static">{{dateFormat($requestPayment->created_at)}}</p>
							</div>
						</div>

						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Amount</label>
								@if($requestPayment->user_id == Auth::user()->id)
								<p class="form-control-static"><strong>+ {{ moneyFormat($requestPayment->symbol, decimalFormat($requestPayment->amount)) }}</strong></p>
								@else
								<p class="form-control-static"><strong>- {{ moneyFormat($requestPayment->symbol, decimalFormat($requestPayment->amount)) }}</strong></p>
								@endif
							</div>
						</div>
						@if($requestPayment->user_id == Auth::user()->id)
						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Request To</label>
								<p class="form-control-static">
									{{ isset($requestPayment->receiver_id) ? $requestPayment->receiver_first_name.' '.$requestPayment->receiver_first_name : $requestPayment->email }}
								</p>
							</div>
						</div>
						@else
						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Request From</label>
								<p class="form-control-static">
									{{ isset($requestPayment->receiver_id) ? $requestPayment->sender_first_name.' '.$requestPayment->sender_last_name : $requestPayment->email }}
								</p>
							</div>
						</div>
						@endif
						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Invoice No</label>
								<p class="form-control-static">
									{{$requestPayment->invoice_no}}
								</p>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Purpose</label>
								<p class="form-control-static">{{$requestPayment->purpose}}</p>
							</div>
						</div>
						<div class="col-md-3 col-xs-6">
							<div class="form-group">
								<label>Status</label>
								<p class="form-control-static">{{$requestPayment->status}}</p>
							</div>
						</div>
						<div class="col-md-6 col-xs-6">
							<div class="form-group">
								<label>Note</label>
								<p class="form-control-static">{{$requestPayment->note}}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</section>
@endsection
@push('extra_body_scripts')
<script type="text/javascript">
</script>
@endpush