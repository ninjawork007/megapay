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
								<h4 class="float-left">Accept Request Payment</h4>
							</div>
						</div>
					</div>

					<div class="card-body" style="overflow: auto;">
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-default">
									<div class="panel-body">
										<form action="{{url('request_payment/accepted')}}"  method="post" accept-charset="utf-8">

											<input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
											<input type="hidden" value="{{$requestPayment->id}}" name="id" id="id">
											<input type="hidden" value="{{$requestPayment->currency_id}}" name="currency_id" id="currency_id" >

											<input type="hidden" value="{{ $requestPayment->amount * $transfer_fee->charge_percentage/100 }}" name="percentage_fee" id="percentage_fee" >
											<input type="hidden" value="{{ $transfer_fee->charge_fixed }}" name="fixed_fee" id="fixed_fee">

										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label>Amount</label>
													<input class="form-control" name="amount" id="amount" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" placeholder="0.00" type="text" value="{{ $requestPayment->amount }}">
							                      @if($errors->has('amount'))
							                          <span class="help-block">
							                              <strong class="text-danger">{{ $errors->first('amount') }}</strong>
							                          </span>
							                      @endif
												</div>
											</div>

											<div class="col-md-2">
												<div class="form-group">
													<label>Fee</label>

													<input class="form-control" name="fee" id="fee" type="text" value="{{ (($requestPayment->amount * $transfer_fee->charge_percentage)/100 + $transfer_fee->charge_fixed) }}" readonly>
													@if($errors->has('fee'))
													  <span class="help-block">
													      <strong class="text-danger">{{ $errors->first('fee') }}</strong>
													  </span>
													@endif
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<label>Currency</label>
													<input class="form-control" name="currency" id="currency" type="text" value="{{$requestPayment->currency->code}}" readonly>

												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label>Invoice No</label>
													<input class="form-control" name="invoice_no" id="invoice_no"  placeholder="INV-123" type="text" value="{{$requestPayment->invoice_no}}" readonly>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label>Email</label>
													<input class="form-control" name="email" id="email" type="email" value="{{$requestPayment->email}}" readonly>
												</div>
											</div>
											<div class="col-md-8">
												<div class="form-group">
													<label>Purpose of payment</label>
													<input class="form-control" name="purpose" id="purpose" type="text" value="{{$requestPayment->purpose}}" readonly>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label>Note</label>
								                    <textarea name="note" class="form-control" id="note" readonly>{{$requestPayment->note}}</textarea>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="pull-right">
													<br>
													<button type="submit" class="btn btn-primary btn-flat">
													Accept</button>
												</div>
											</div>
										</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

@include('frontend.layouts.common.help')
@endsection


@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">
	$('#requestpayment_create_form').validate({
	rules: {
			amount: {
				required: true,
			},
			invoice_no: {
				// required: true,
			},
			email: {
				required: true,
				email: true,
			},
			purpose: {
				required: true,
				// letters_with_spaces: true,
			},
			note: {
				required: true,
				// letters_with_spaces: true,
			},
		},
	});

	$(document).ready(function() {
		$('#amount').on('keyup keypress', function(e) {
			if (e.type=="keyup") {
				var token = $("#token").val();
		    	$.ajax({
					method: "POST",
					url: SITE_URL+"/request_payment/fee",
					dataType: "json",
					data: { "_token":token,'type':'Transfer' }
				})
				.done(function(response) {
					// console.log(response);

					if(response.status == 1)
					{
						var feeInfo = response.fees;

						var amount = $("#amount").val();

						$("#percentage_fee").val(percentage_fee());

						$("#fixed_fee").val(fixed_fee());

						$("#fee").val(total_fees());

						function percentage_fee() {
							var percentage_fee  = parseInt(feeInfo.charge_percentage);
							var p_calculated = (amount*percentage_fee)/100;
							return roundValues(p_calculated,2);
						}

						function fixed_fee() {
							var fixed_fee = parseInt(feeInfo.charge_fixed);
							return roundValues(fixed_fee,2);
						}

						// total_fees
						function total_fees() {
							var int_perc_fee  = parseInt(feeInfo.charge_percentage);
							var int_fixed_fee = parseInt(feeInfo.charge_fixed);

							var p_calc = (amount*int_perc_fee)/100;
							var total_fees = p_calc + int_fixed_fee;
							return roundValues(total_fees,2);
						}
					}
				});
				//roundValues
				function roundValues(value,decimals)
				{
					return Number(Math.round(value+'e'+decimals)+'e-'+decimals).toFixed(3).slice(0, -1);
				}
			} else {
				var token = $("#token").val();
		    	$.ajax({
					method: "POST",
					url: SITE_URL+"/request_payment/fee",
					dataType: "json",
					data: { "_token":token,'type':'Transfer' }
				})
				.done(function(response) {
					if(response.status == 1)
					{
						var feeInfo = response.fees;

						var amount = $("#amount").val();

						$("#percentage_fee").val(percentage_fee());

						$("#fixed_fee").val(fixed_fee());

						$("#fee").val(total_fees());

						function percentage_fee() {
							var percentage_fee  = parseInt(feeInfo.charge_percentage);
							var p_calculated = (amount*percentage_fee)/100;
							return roundValues(p_calculated,2);
						}

						function fixed_fee() {
							var fixed_fee = parseInt(feeInfo.charge_fixed);
							return roundValues(fixed_fee,2);
						}

						// total_fees
						function total_fees() {
							var int_perc_fee  = parseInt(feeInfo.charge_percentage);
							var int_fixed_fee = parseInt(feeInfo.charge_fixed);

							var p_calc = (amount*int_perc_fee)/100;
							var total_fees = p_calc + int_fixed_fee;
							return roundValues(total_fees,2);
						}
					}
				});
				//roundValues
				function roundValues(value,decimals)
				{
					return Number(Math.round(value+'e'+decimals)+'e-'+decimals).toFixed(3).slice(0, -1);
				}
			}
		});
	});
</script>

@endsection
