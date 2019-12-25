@extends('admin.layouts.master')
@section('title', 'Edit Voucher')

@section('page_content')

<div class="box">
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="col-md-10">
								<h4 class="text-left">Voucher Details</h4>
							</div>
							<div class="col-md-2">
								@if ($voucher->status)
									<h4 class="text-left">Status : @if ($voucher->status == 'Success')<span class="text-green">Success</span>@endif
			                        	@if ($voucher->status == 'Pending')<span class="text-blue">Pending</span>@endif
	                        			@if ($voucher->status == 'Blocked')<span class="text-red">Cancelled</span>@endif
	                        		</h4>
								@endif
							</div>
						</div>
					</div>

					<div class="panel-body">
						<div class="row">
							<form action="{{ url('admin/vouchers/update') }}" class="form-horizontal" id="exchange_form" method="POST">
								{{ csrf_field() }}
						        <input type="hidden" value="{{ $voucher->id }}" name="id" id="id">
						        <input type="hidden" value="{{ $voucher->uuid }}" name="uuid" id="uuid">
						        <input type="hidden" value="{{ $voucher->user_id }}" name="user_id" id="user_id">
						        <input type="hidden" value="{{ $voucher->currency->id }}" name="currency_id" id="currency_id">

						        <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
								<input type="hidden" value="{{ $transaction->transaction_type->name }}" name="transaction_type" id="transaction_type">
								<input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
								<input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">


								<div class="col-md-7">
									<div class="panel panel-default">
										<div class="panel-body">

						                    @if ($voucher->user_id)
												<div class="form-group">
													<label class="control-label col-sm-3" for="user">User</label>
													<input type="hidden" class="form-control" name="user" value="{{ isset($voucher->user) ? $voucher->user->first_name.' '.$voucher->user->last_name :"-" }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ isset($voucher->user) ? $voucher->user->first_name.' '.$voucher->user->last_name :"-" }}</p>
													</div>
												</div>
											@endif

											@if ($voucher->uuid)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="voucher_uuid">Transaction ID</label>
													<input type="hidden" class="form-control" name="voucher_uuid" value="{{ $voucher->uuid }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $voucher->uuid }}</p>
													</div>
												</div>
											@endif

											@if ($voucher->code)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="code">Code</label>
													<input type="hidden" class="form-control" name="code" value="{{ $voucher->code  }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $voucher->code  }}</p>
													</div>
												</div>
											@endif

											@if ($voucher->currency_id)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="currency">Currency</label>
													<input type="hidden" class="form-control" name="currency" value="{{ $voucher->currency->code }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $voucher->currency->code }}</p>
													</div>
												</div>
											@endif

											@if ($voucher->redeemed)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="redeemed">Redeemed</label>
													<input type="hidden" class="form-control" name="redeemed" value="{{ $voucher->redeemed }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $voucher->redeemed }}</p>
													</div>
												</div>
											@endif

											@if ($voucher->created_at)
												<div class="form-group">
													<label class="control-label col-sm-3" for="created_at">Date</label>
													<input type="hidden" class="form-control" name="created_at" value="{{ $voucher->created_at }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ dateFormat($voucher->created_at) }}</p>
													</div>
												</div>
					                   		@endif

					                   		@if ($voucher->status)
						                   		<div class="form-group">
													<label class="control-label col-sm-3" for="status">Change Status</label>
													<div class="col-sm-9">
														<select class="form-control select2" name="status" style="width: 50%;">
									                        @if ($voucher->status == 'Success' && $transaction->status == 'Success' && $transaction->transaction_type_id == Voucher_Activated)
																<option value="Success" {{ isset($voucher->status) && $voucher->status ==  'Success'? 'selected':"" }}>Success</option>
									                        	<option value="Pending" {{ isset($voucher->status) && $voucher->status ==  'Pending'? 'selected':"" }}>Pending</option>

									                        @elseif ($voucher->status == 'Pending' && $transaction->status == 'Pending' && $transaction->transaction_type_id == Voucher_Activated)
																<option value="Success" {{ isset($voucher->status) && $voucher->status ==  'Success'? 'selected':"" }}>Success</option>
									                        	<option value="Pending" {{ isset($voucher->status) && $voucher->status ==  'Pending'? 'selected':"" }}>Pending</option>

															@elseif ($voucher->status == 'Pending' && $transaction->status == 'Pending' && $transaction->transaction_type_id == Voucher_Created)
																<option value="Pending" {{ isset($voucher->status) && $voucher->status ==  'Pending'? 'selected':"" }}>Pending</option>
																<option value="Blocked"  {{ isset($voucher->status) && $voucher->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

															@elseif ($voucher->status == 'Success' && $transaction->status == 'Success' && $transaction->transaction_type_id == Voucher_Created)
																<option value="Success" {{ isset($voucher->status) && $voucher->status ==  'Success'? 'selected':"" }}>Success</option>
																<option value="Pending" {{ isset($voucher->status) && $voucher->status ==  'Pending'? 'selected':"" }}>Pending</option>
																{{-- <option value="Blocked"  {{ isset($voucher->status) && $voucher->status == 'Blocked' ? 'selected':"" }}>Cancel</option> --}}

															@elseif ($voucher->status == 'Blocked' && $transaction->status == 'Blocked' && $transaction->transaction_type_id == Voucher_Created)
																<option value="Pending" {{ isset($voucher->status) && $voucher->status ==  'Pending'? 'selected':"" }}>Pending</option>
																<option value="Blocked"  {{ isset($voucher->status) && $voucher->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
									                        @endif
													  	</select>
													</div>
												</div>
											@endif

										</div>
									</div>
								</div>

								<div class="col-md-5">
									<div class="panel panel-default">
										<div class="panel-body">

											@if ($voucher->amount)
							                    <div class="form-group">
													<label class="control-label col-sm-4 pull-left" for="amount">Amount</label>
													<input type="hidden" class="form-control" name="amount" value="{{ ($voucher->amount) }}">
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($voucher->currency->symbol, formatNumber($voucher->amount)) }}</p>
													</div>
												</div>
											@endif

							                    <div class="form-group total-deposit-feesTotal-space">
													<label class="control-label col-sm-4 pull-left" for="fee">Fees
														<span>
															<small class="transactions-edit-fee">
																@if (isset($transaction) && $transaction->transaction_type_id == Voucher_Created)
																	({{($transaction->percentage)}}% + {{ $transaction->charge_fixed }})
																@else
																	({{0}}%+{{0}})
																@endif
															</small>
														</span>
													</label>
													<input type="hidden" class="form-control" name="fee" value="{{ ($transaction->charge_percentage + $transaction->charge_fixed) }}">

													@if (isset($transaction))
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($voucher->currency->symbol, formatNumber($transaction->charge_percentage + $transaction->charge_fixed)) }}</p>
													</div>
													@endif
												</div>

											<hr class="increase-hr-height">

											@php
												$total = ($transaction->charge_percentage + $transaction->charge_fixed) + $voucher->amount;
											@endphp

											@if (isset($total))
							                    <div class="form-group total-deposit-space">
													<label class="control-label col-sm-4 pull-left" for="total">Total</label>
													<input type="hidden" class="form-control" name="total" value="{{ ($total) }}">
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($voucher->currency->symbol, formatNumber($total)) }}</p>
													</div>
												</div>
											@endif

										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-11">
										<div class="col-md-2"></div>
										<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/vouchers') }}">Cancel</a></div>
										<div class="col-md-1">
											<button type="submit" class="btn button-secondary pull-right" id="voucher_edit">
				                                <i class="spinner fa fa-spinner fa-spin"></i> <span id="voucher_edit_text">Update</span>
				                            </button>
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
@endsection

@push('extra_body_scripts')
<script type="text/javascript">

	$(".select2").select2({});

	// disabling submit and cancel button after clicking it
	$(document).ready(function() {
		$('form').submit(function() {
			$("#voucher_edit").attr("disabled", true);
			$('#cancel_anchor').attr("disabled","disabled");
			$(".spinner").show();
			$("#voucher_edit_text").text('Updating...');

			// Click False
			$('#voucher_edit').click(false);
			$('#cancel_anchor').click(false);
		});
	});
</script>
@endpush

