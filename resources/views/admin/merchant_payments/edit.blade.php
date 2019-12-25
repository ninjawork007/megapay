@extends('admin.layouts.master')
@section('title', 'Edit Merchant Payment')

@section('page_content')
	<div class="box">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="row">
								<div class="col-md-10">
									<h4 class="text-left">Edit Merchant Payment</h4>
								</div>
								<div class="col-md-2">
									@if ($merchant_payment->status)
										<h4 class="text-left">Status : @if ($merchant_payment->status == 'Success')<span class="text-green">Success</span>@endif
				                    	@if ($merchant_payment->status == 'Pending')<span class="text-blue">Pending</span>@endif
				            			@if ($merchant_payment->status == 'Refund')<span class="text-red">Refunded</span>@endif</h4>
									@endif
								</div>
							</div>
						</div>

						<div class="panel-body">
							<div class="row">
								<form action="{{ url('admin/merchant_payments/update') }}" class="form-horizontal" id="merchant_payment_form" method="POST">
										{{ csrf_field() }}
							        <input type="hidden" value="{{ $merchant_payment->id }}" name="id" id="id">

							        <input type="hidden" value="{{ base64_encode($merchant_payment->merchant->id) }}" name="merchant_id" id="merchant_id">

							        <input type="hidden" value="{{ $merchant_payment->currency->id }}" name="currency_id" id="currency_id">

							        <input type="hidden" value="{{ base64_encode($merchant_payment->payment_method->id) }}" name="payment_method_id" id="payment_method_id">

							        <input type="hidden" value="{{ $merchant_payment->user_id }}" name="paid_by_user_id" id="paid_by_user_id">

							        <input type="hidden" value="{{ base64_encode($merchant_payment->gateway_reference) }}" name="gateway_reference" id="gateway_reference">



							        <input type="hidden" value="{{ $merchant_payment->order_no }}" name="order_no" id="order_no">
							        <input type="hidden" value="{{ $merchant_payment->item_name }}" name="item_name" id="item_name">

							        <input type="hidden" value="{{ ($merchant_payment->charge_percentage)  }}" name="charge_percentage" id="charge_percentage">
							        <input type="hidden" value="{{ ($merchant_payment->charge_fixed)  }}" name="charge_fixed" id="charge_fixed">

							         @if (!empty($transaction))
										<input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
								        <input type="hidden" value="{{ $transaction->transaction_type->name }}" name="transaction_type" id="transaction_type">
								        <input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
								        <input type="hidden" value="{{ $transaction->user_type }}" name="user_type" id="user_type">
								        <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">
								        <input type="hidden" value="{{ ($transaction->percentage) }}" name="percentage" id="percentage">
									@endif

									<div class="col-md-7">
										<div class="panel panel-default">
											<div class="panel-body">

												@if ($merchant_payment->merchant_id)
													<div class="form-group">
														<label class="control-label col-sm-3" for="merchant_user">Merchant</label>
														<input type="hidden" name="merchant_user" value="{{ $merchant_payment->merchant->user->first_name.' '.$merchant_payment->merchant->user->last_name }}" >
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $merchant_payment->merchant->user->first_name.' '.$merchant_payment->merchant->user->last_name }}</p>
														</div>
													</div>
												@endif

												@if ($merchant_payment->merchant->type)
								                    <div class="form-group">
														<label class="control-label col-sm-3" for="type">Merchant Type</label>
														<div class="col-sm-9">
															<p class="form-control-static">{{ $merchant_payment->merchant->type }}</p>
														</div>
													</div>
												@endif

												@if ($merchant_payment->user_id)
													<div class="form-group">
														<label class="control-label col-sm-3" for="user">User</label>
														<input type="hidden" class="form-control" name="user" value="{{ isset($merchant_payment->user) ? $merchant_payment->user->first_name.' '.$merchant_payment->user->last_name :"-" }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ isset($merchant_payment->user) ? $merchant_payment->user->first_name.' '.$merchant_payment->user->last_name :"-" }}</p>
														</div>
													</div>
												@endif

												@if ($merchant_payment->uuid)
								                    <div class="form-group">
														<label class="control-label col-sm-3" for="mp_uuid">Transaction ID</label>
														<input type="hidden" class="form-control" name="mp_uuid" value="{{ $merchant_payment->uuid }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $merchant_payment->uuid }}</p>
														</div>
													</div>
												@endif

												@if ($merchant_payment->currency)
													<div class="form-group">
														<label class="control-label col-sm-3" for="currency">Currency</label>
														<input type="hidden" class="form-control" name="currency" value="{{ $merchant_payment->currency->code }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $merchant_payment->currency->code }}</p>
														</div>
													</div>
												@endif

												@if ($merchant_payment->payment_method)
								                    <div class="form-group">
														<label class="control-label col-sm-3" for="payment_method">Payment Method</label>
														<input type="hidden" class="form-control" name="payment_method" value="{{ ($merchant_payment->payment_method->name == "Mts") ? getCompanyName() : $merchant_payment->payment_method->name }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ ($merchant_payment->payment_method->name == "Mts") ? getCompanyName() : $merchant_payment->payment_method->name }}</p>
														</div>
													</div>
												@endif

												@if ($merchant_payment->created_at)
													<div class="form-group">
														<label class="control-label col-sm-3" for="created_at">Date</label>
														<input type="hidden" class="form-control" name="created_at" value="{{ $merchant_payment->created_at }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ dateFormat($merchant_payment->created_at) }}</p>
														</div>
													</div>
						                   		@endif

						                   		@if ($merchant_payment->status)
							                   		<div class="form-group">
														<label class="control-label col-sm-3" for="status">Change Status</label>
														<div class="col-sm-9">

															@if (isset($transactionOfRefunded) && isset($merchantPaymentOfRefunded))

									                          <p class="form-control-static"><span class="label label-success">Already Refunded</span></p>

									                          	<p class="form-control-static"><span class="label label-danger">Refund Reference: <i>
											                          	<a id="merchantPaymentOfRefunded" href="{{ url("admin/merchant_payments/edit/$merchantPaymentOfRefunded->id") }}">( {{ $transactionOfRefunded->refund_reference }} )</a>
											                          </i>
											                      </span>
											                  	</p>

									                        @else
										                        <select class="form-control select2" name="status" style="width: 60%;">

										                        	@if ($merchant_payment->status ==  'Success')
										                        		<option value="Success" {{ isset($merchant_payment->status) && $merchant_payment->status ==  'Success'? 'selected':"" }}>Success</option>
																		<option value="Pending"  {{ isset($merchant_payment->status) && $merchant_payment->status == 'Pending' ? 'selected':"" }}>Pending</option>
																		<option value="Refund"  {{ isset($merchant_payment->status) && $merchant_payment->status == 'Refund' ? 'selected':"" }}>Refund</option>
										                        	@else
										                        		<option value="Success" {{ isset($merchant_payment->status) && $merchant_payment->status ==  'Success'? 'selected':"" }}>Success</option>
																		<option value="Pending"  {{ isset($merchant_payment->status) && $merchant_payment->status == 'Pending' ? 'selected':"" }}>Pending</option>
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

												@if ($merchant_payment->amount)
								                    <div class="form-group">
														<label class="control-label col-sm-4 pull-left" for="amount">Amount</label>
														<input type="hidden" class="form-control" name="amount" value="{{ ($merchant_payment->amount) }}">
														<div class="col-sm-7">
														  <p class="form-control-static pull-right">{{  moneyFormat($merchant_payment->currency->symbol, formatNumber($merchant_payment->amount)) }}</p>
														</div>
													</div>
												@endif

							                    <div class="form-group total-deposit-feesTotal-space">
													<label class="control-label col-sm-4 pull-left" for="feesTotal">Fees
														<span>
															<small class="transactions-edit-fee">
																@if (isset($transaction))
																({{(formatNumber($transaction->percentage))}}% + {{ formatNumber($merchant_payment->charge_fixed) }})
																@else
																	({{0}}%+{{0}})
																@endif
															</small>
														</span>
													</label>

													@php
														$feesTotal = $merchant_payment->charge_percentage + $merchant_payment->charge_fixed;
													@endphp

													<input type="hidden" class="form-control" name="feesTotal" value="{{ ($feesTotal) }}">

													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($merchant_payment->currency->symbol, formatNumber($feesTotal)) }}</p>
													</div>
												</div>

												<hr class="increase-hr-height">

												@php
													$total = $feesTotal + $merchant_payment->amount;
												@endphp

												@if (isset($total))
								                    <div class="form-group total-deposit-space">
														<label class="control-label col-sm-4 pull-left" for="total">Total</label>
														<input type="hidden" class="form-control" name="total" value="{{ ($total) }}">
														<div class="col-sm-7">
														  <p class="form-control-static pull-right">{{  moneyFormat($merchant_payment->currency->symbol, formatNumber($total)) }}</p>
														</div>
													</div>
												@endif

											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-11">
											<div class="col-md-2"></div>
											<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/merchant_payments') }}">Cancel</a></div>

											@if (!isset($transactionOfRefunded->refund_reference))
												<div class="col-md-1">
													<button type="submit" class="btn button-secondary pull-right" id="merchant_payment_edit">
		                                                <i class="spinner fa fa-spinner fa-spin"></i> <span id="merchant_payment_edit_text">Update</span>
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

	// disabling submit and cancel button after form submit
	$(document).ready(function()
	{
	  $('form').submit(function()
	  {
	     	$("#merchant_payment_edit").attr("disabled", true);

	     	$('#cancel_anchor').attr("disabled","disabled");

            $(".spinner").show();

            $("#merchant_payment_edit_text").text('Updating...');

            // Click False
			$('#merchant_payment_edit').click(false);
			$('#cancel_anchor').click(false);
	  });

	  $('#merchantPaymentOfRefunded').css('color', 'white');
	});
</script>

@endpush
