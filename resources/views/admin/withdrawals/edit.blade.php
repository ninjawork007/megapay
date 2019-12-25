@extends('admin.layouts.master')
@section('title', 'Edit Payout')

@section('page_content')

<div class="box">
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="col-md-10">
								<h4 class="text-left">Payout Details</h4>
							</div>
							<div class="col-md-2">
								@if ($withdrawal->status)
									<h4 class="text-left">Status : @if ($withdrawal->status == 'Success')<span class="text-green">Success</span>@endif
		                        	@if ($withdrawal->status == 'Pending')<span class="text-blue">Pending</span>@endif
                        			@if ($withdrawal->status == 'Blocked')<span class="text-red">Cancelled</span>@endif</h4>
								@endif
							</div>
						</div>
					</div>

					<div class="panel-body">
						<div class="row">
							<form action="{{ url('admin/withdrawals/update') }}" class="form-horizontal" id="withdrawal_form" method="POST">
								{{ csrf_field() }}

						        <input type="hidden" value="{{ $withdrawal->id }}" name="id" id="id">
						        <input type="hidden" value="{{ $withdrawal->user_id }}" name="user_id" id="user_id">
						        <input type="hidden" value="{{ $withdrawal->currency->id }}" name="currency_id" id="currency_id">
						        <input type="hidden" value="{{ $withdrawal->uuid }}" name="uuid" id="uuid">

						        <input type="hidden" value="{{ $transaction->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
						        <input type="hidden" value="{{ $transaction->transaction_type->name }}" name="transaction_type" id="transaction_type">
						        <input type="hidden" value="{{ $transaction->status }}" name="transaction_status" id="transaction_status">
						        <input type="hidden" value="{{ $transaction->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">


								<div class="col-md-7">
									<div class="panel panel-default">
										<div class="panel-body">

											@if ($withdrawal->user_id)
												<div class="form-group">
													<label class="control-label col-sm-3" for="user">User</label>
													<input type="hidden" class="form-control" name="user" value="{{ isset($withdrawal->user) ? $withdrawal->user->first_name.' '.$withdrawal->user->last_name :"-" }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ isset($withdrawal->user) ? $withdrawal->user->first_name.' '.$withdrawal->user->last_name :"-" }}</p>
													</div>
												</div>
											@endif

											@if ($withdrawal->uuid)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="withdrawal_uuid">Transaction ID</label>
													<input type="hidden" class="form-control" name="withdrawal_uuid" value="{{ $withdrawal->uuid }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $withdrawal->uuid }}</p>
													</div>
												</div>
											@endif

											@if ($withdrawal->currency)
												<div class="form-group">
													<label class="control-label col-sm-3" for="currency">Currency</label>
													<input type="hidden" class="form-control" name="currency" value="{{ $withdrawal->currency->code }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $withdrawal->currency->code }}</p>
													</div>
												</div>
											@endif

											@if ($withdrawal->payment_method)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="payment_method">Payment Method</label>
													<input type="hidden" class="form-control" name="payment_method" value="{{ ($withdrawal->payment_method->name == "Mts") ? getCompanyName() : $withdrawal->payment_method->name }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ ($withdrawal->payment_method->name == "Mts") ? getCompanyName() : $withdrawal->payment_method->name }}</p>
													</div>
												</div>
											@endif


											@if (isset($withdrawal->withdrawal_detail))
												@if ($withdrawal->payment_method->name == 'Bank')
													<div class="form-group">
														<label class="control-label col-sm-3" for="account_name">Account Name</label>
														<input type="hidden" class="form-control" name="account_name" value="{{ $withdrawal->withdrawal_detail->account_name }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $withdrawal->withdrawal_detail->account_name }}</p>
														</div>
													</div>

													<div class="form-group">
														<label class="control-label col-sm-3" for="account_number">Account Number/IBAN</label>
														<input type="hidden" class="form-control" name="account_number" value="{{ $withdrawal->withdrawal_detail->account_number }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $withdrawal->withdrawal_detail->account_number }}</p>
														</div>
													</div>

													<div class="form-group">
														<label class="control-label col-sm-3" for="swift_code">SWIFT Code</label>
														<input type="hidden" class="form-control" name="swift_code" value="{{ $withdrawal->withdrawal_detail->swift_code }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $withdrawal->withdrawal_detail->swift_code }}</p>
														</div>
													</div>

													<div class="form-group">
														<label class="control-label col-sm-3" for="bank_name">Bank Name</label>
														<input type="hidden" class="form-control" name="bank_name" value="{{ $withdrawal->withdrawal_detail->bank_name }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $withdrawal->withdrawal_detail->bank_name }}</p>
														</div>
													</div>
												@endif
											@endif


											@if ($withdrawal->created_at)
												<div class="form-group">
													<label class="control-label col-sm-3" for="created_at">Date</label>
													<input type="hidden" class="form-control" name="created_at" value="{{ $withdrawal->created_at }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ dateFormat($withdrawal->created_at) }}</p>
													</div>
												</div>
					                   		@endif

					                   		@if ($withdrawal->status)
						                   		<div class="form-group">
													<label class="control-label col-sm-3" for="status">Change Status</label>
													<div class="col-sm-9">
														<select class="form-control select2" name="status" style="width: 60%;">
															<option value="Success" {{ $withdrawal->status ==  'Success'? 'selected':"" }}>Success</option>
															<option value="Pending"  {{ $withdrawal->status == 'Pending' ? 'selected':"" }}>Pending</option>
															<option value="Blocked"  {{ $withdrawal->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
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

											@if ($withdrawal->amount)
							                    <div class="form-group">
													<label class="control-label col-sm-4 pull-left" for="amount">Amount</label>
													<input type="hidden" class="form-control" name="amount" value="{{ ($withdrawal->amount) }}">
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($withdrawal->currency->symbol, formatNumber($withdrawal->amount)) }}</p>
													</div>
												</div>
											@endif



							                    <div class="form-group total-deposit-feesTotal-space">
													<label class="control-label col-sm-4 pull-left" for="feesTotal">Fees
														<span>
															<small class="transactions-edit-fee">
																@if (isset($transaction))
																({{(formatNumber($transaction->percentage))}}% + {{ formatNumber($withdrawal->charge_fixed) }})
																@else
																	({{0}}%+{{0}})
																@endif
															</small>
														</span>
													</label>

													@php
														$feesTotal = $withdrawal->charge_percentage + $withdrawal->charge_fixed;
													@endphp

													<input type="hidden" class="form-control" name="feesTotal" value="{{ ($feesTotal) }}">
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($withdrawal->currency->symbol, formatNumber($feesTotal)) }}</p>
													</div>
												</div>
											<hr class="increase-hr-height">

											@php
												$total = $feesTotal + $withdrawal->amount;
											@endphp

											@if (isset($total))
							                    <div class="form-group total-deposit-space">
													<label class="control-label col-sm-4 pull-left" for="total">Total</label>
													<input type="hidden" class="form-control" name="total" value="{{ ($total) }}">
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($withdrawal->currency->symbol, formatNumber($total)) }}</p>
													</div>
												</div>
											@endif

										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-11">
										<div class="col-md-2"></div>
										<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/withdrawals') }}">Cancel</a></div>
										<div class="col-md-1">
											<button type="submit" class="btn button-secondary pull-right" id="withdrawal_edit">
                                                <i class="spinner fa fa-spinner fa-spin"></i> <span id="withdrawal_edit_text">Update</span>
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
	$(document).ready(function()
	{
		$('form').submit(function()
		{
		 	$("#withdrawal_edit").attr("disabled", true);
		 	$('#cancel_anchor').attr("disabled","disabled");
		    $(".spinner").show();
		    $("#withdrawal_edit_text").text('Updating...');

		    // Click False
			$('#withdrawal_edit').click(false);
			$('#cancel_anchor').click(false);
		});
	});
</script>
@endpush

