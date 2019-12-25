@extends('admin.layouts.master')
@section('title', 'Edit Request Payment')

@section('page_content')

<div class="box">
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="col-md-10">
								<h4 class="text-left">Request Payment Details</h4>
							</div>
							<div class="col-md-2">
								@if ($request_payments->status)
									<h4 class="text-left">Status : @if ($request_payments->status == 'Success')<span class="text-green">Success</span>@endif
		                        	@if ($request_payments->status == 'Pending')<span class="text-blue">Pending</span>@endif
                        			@if ($request_payments->status == 'Refund')<span class="text-warning">Refunded</span>@endif
                        			@if ($request_payments->status == 'Blocked')<span class="text-red">Cancelled</span>@endif</h4>
								@endif
							</div>
						</div>
					</div>

					<div class="panel-body">
						<div class="row">
							<form action="{{ url('admin/request_payments/update') }}" class="form-horizontal" method="POST">
								{{ csrf_field() }}

						        <input type="hidden" value="{{ $request_payments->id }}" name="id" id="id">
						        <input type="hidden" value="{{ $request_payments->uuid }}" name="uuid" id="uuid">
						        <input type="hidden" value="{{ $request_payments->user_id }}" name="user_id" id="user_id">
						        <input type="hidden" value="{{ $request_payments->currency->id }}" name="currency_id" id="currency_id">
						        <input type="hidden" value="{{ $request_payments->note }}" name="note" id="note">

								@if (isset($transaction))
									<input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
									<input type="hidden" value="{{ $transaction->transaction_type->name }}" name="transaction_type" id="transaction_type">
									<input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
									<input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">

									<input type="hidden" value="{{ $transaction->user_type }}" name="user_type" id="user_type">

									<input type="hidden" value="{{ ($transaction->percentage) }}" name="percentage" id="percentage">
									<input type="hidden" value="{{ ($transaction->charge_percentage) }}" name="charge_percentage" id="charge_percentage">
						        	<input type="hidden" value="{{ ($transaction->charge_fixed) }}" name="charge_fixed" id="charge_fixed">
								{{-- @else
									{{ "-" }} --}}
								@endif


								<div class="col-md-7">
									<div class="panel panel-default">
										<div class="panel-body">

						                    @if ($request_payments->user)
												<div class="form-group">
													<label class="control-label col-sm-3" for="user">Request From</label>
													<input type="hidden" class="form-control" name="user" value="{{ isset($request_payments->user) ? $request_payments->user->first_name.' '.$request_payments->user->last_name :"-" }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ isset($request_payments->user) ? $request_payments->user->first_name.' '.$request_payments->user->last_name :"-" }}</p>
													</div>
												</div>
											@endif

											@if ($request_payments->receiver)
												<div class="form-group">
													<label class="control-label col-sm-3" for="receiver">Request To</label>
													<input type="hidden" class="form-control" name="receiver" value="{{ isset($request_payments->receiver) ? $request_payments->receiver->first_name.' '.$request_payments->receiver->last_name :"-" }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ isset($request_payments->receiver) ? $request_payments->receiver->first_name.' '.$request_payments->receiver->last_name :"-" }}</p>
													</div>
												</div>
											@endif

											@if ($request_payments->uuid)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="request_payments_uuid">Transaction ID</label>
													<input type="hidden" class="form-control" name="request_payments_uuid" value="{{ $request_payments->uuid }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $request_payments->uuid }}</p>
													</div>
												</div>
											@endif

											@if ($request_payments->email)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="request_payments_email">Email</label>
													<input type="hidden" class="form-control" name="request_payments_email" value="{{ $request_payments->email }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $request_payments->email }}</p>
													</div>
												</div>
											@endif


											@if ($request_payments->currency)
												<div class="form-group">
													<label class="control-label col-sm-3" for="currency">Currency</label>
													<input type="hidden" class="form-control" name="currency" value="{{ $request_payments->currency->code }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $request_payments->currency->code }}</p>
													</div>
												</div>
											@endif

											@if ($request_payments->created_at)
												<div class="form-group">
													<label class="control-label col-sm-3" for="created_at">Date</label>
													<input type="hidden" class="form-control" name="created_at" value="{{ $request_payments->created_at }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ dateFormat($request_payments->created_at) }}</p>
													</div>
												</div>
						               		@endif

						               		@if ($request_payments->status)
						                   		<div class="form-group">
													<label class="control-label col-sm-3" for="status">Change Status</label>
													<div class="col-sm-9">

														@if (isset($transactionOfRefunded) && isset($requestPaymentsOfRefunded))
								                          <p class="form-control-static"><span class="label label-success">Already Refunded</span></p>

								                          <p class="form-control-static"><span class="label label-danger">Refund Reference: <i>
										                          	<a id="requestPaymentsOfRefunded" href="{{ url("admin/request_payments/edit/$requestPaymentsOfRefunded->id") }}">( {{ $transactionOfRefunded->refund_reference }} )</a>
										                          </i>
										                      </span>
										                  </p>
								                        @else
									                        <select class="form-control select2" name="status" style="width: 60%;">

																@if (isset($transaction->status) && $transaction->status == 'Success')
																	<option value="Success" {{ isset($request_payments->status) && $request_payments->status ==  'Success'? 'selected':"" }}>Success</option>
																	<option value="Refund"  {{ isset($request_payments->status) && $request_payments->status == 'Refund' ? 'selected':"" }}>Refund</option>

																@elseif ($request_payments->status == 'Pending')
										                        	<option value="Pending" {{ isset($request_payments->status) && $request_payments->status ==  'Pending'? 'selected':"" }}>Pending</option>
																	<option value="Blocked"  {{ isset($request_payments->status) && $request_payments->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																@elseif ($request_payments->status == 'Blocked')
										                        	<option value="Pending" {{ isset($request_payments->status) && $request_payments->status ==  'Pending'? 'selected':"" }}>Pending</option>
																	<option value="Blocked"  {{ isset($request_payments->status) && $request_payments->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
							                        			@endif
															</select>
								                        @endif
													</div>
												</div>
											@endif

										</div>
									</div>
								</div>

								<div class="col-md-5">
									<div class="panel panel-default">
										<div class="panel-body">

											@if ($request_payments->amount)
							                    <div class="form-group">
													<label class="control-label col-sm-5 pull-left" for="amount">Requested Amount</label>
													<input type="hidden" class="form-control" name="amount" value="{{ ($request_payments->amount) }}">
													<div class="col-sm-6">
													  <p class="form-control-static pull-right">{{  moneyFormat($request_payments->currency->symbol, formatNumber($request_payments->amount)) }}</p>
													</div>
												</div>
											@endif

							                    <div class="form-group total-deposit-feesTotal-space">
													<label class="control-label col-sm-5 pull-left" for="accept_amount">Accepted Amount</label>
													<input type="hidden" class="form-control" name="accept_amount" value="{{ ($request_payments->accept_amount) }}">
													<div class="col-sm-6">
													  <p class="form-control-static pull-right">{{  moneyFormat($request_payments->currency->symbol, formatNumber($request_payments->accept_amount)) }}</p>
													</div>
												</div>

							                    <div class="form-group total-deposit-feesTotal-space-request-payment">
													<label class="control-label col-sm-5 pull-left" for="fee">Fees
														<span>
															<small class="transactions-edit-fee">
																@if (isset($transaction) && $transaction->transaction_type_id == Request_To)
																	({{(formatNumber($transaction->percentage))}}% + {{ formatNumber($transaction->charge_fixed) }})
																@else
																	({{0}}%+{{0}})
																@endif
															</small>
														</span>
													</label>
													<input type="hidden" class="form-control" name="fee" value="{{ isset($transaction) ? ($transaction->charge_percentage + $transaction->charge_fixed) :"0" }}">

													<div class="col-sm-6">
													  <p class="form-control-static pull-right">{{ isset($transaction) ? moneyFormat($request_payments->currency->symbol, formatNumber($transaction->charge_percentage + $transaction->charge_fixed)) :  moneyFormat($request_payments->currency->symbol, formatNumber(0.00)) }}</p>
													</div>
												</div>

												<hr class="increase-hr-height-request-payment">
												@php
													if (isset($transaction))
													{
														$total = $transaction->charge_percentage + $transaction->charge_fixed + $request_payments->accept_amount ;
													}
													else
													{
														$total = $request_payments->amount;
													}
												@endphp

							                    <div class="form-group total-deposit-space-request-payment">
													<label class="control-label col-sm-5 pull-left" for="total">Total</label>
													<input type="hidden" class="form-control" name="total" value="{{ ($total) }}">
													<div class="col-sm-6">
													  <p class="form-control-static pull-right">{{  moneyFormat($request_payments->currency->symbol, formatNumber($total)) }}</p>
													</div>
												</div>

										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-11">
										<div class="col-md-2"></div>
										<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/request_payments') }}">Cancel</a></div>

										@if (!isset($transactionOfRefunded->refund_reference))
											<div class="col-md-1">
												<button type="submit" class="btn button-secondary pull-right" id="request_payment">
							                        <i class="spinner fa fa-spinner fa-spin"></i> <span id="request_payment_text">Update</span>
							                    </button>
											</div>
										@endif
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

@endsection

@push('extra_body_scripts')
<script type="text/javascript">

	$(".select2").select2({});

	// disabling submit and cancel button after clicking it
	$(document).ready(function() {
		$('form').submit(function() {
			$("#request_payment").attr("disabled", true);
			$('#cancel_anchor').attr("disabled","disabled");
			$(".spinner").show();
			$("#request_payment_text").text('Updating...');

			// Click False
			$('#request_payment').click(false);
			$('#cancel_anchor').click(false);
		});

		$('#requestPaymentsOfRefunded').css('color', 'white');
	});
</script>
@endpush


