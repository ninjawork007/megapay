@extends('admin.layouts.master')
@section('title', 'Edit Transaction')

@section('page_content')

<div class="box">
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="col-md-8">
								<h4 class="text-left">Transaction Details</h4>
							</div>

							<div class="col-md-2">

								@if (isset($dispute))
									@if( $transactions->transaction_type_id == Payment_Sent && $transactions->status == 'Success' && $dispute->status != 'Open')
	                                    <a id="dispute_{{$transactions->id}}" href="{{ url('admin/dispute/add/'.$transactions->id) }}" class="btn button-secondary btn-sm pull-right">Open Dispute</a>
	                                @endif
								@endif

							</div>
							<div class="col-md-2">
								@if ($transactions->status)
									<h4 class="text-left">Status :

									@if ($transactions->transaction_type_id == Deposit)
		                        			@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif

		                        	@elseif ($transactions->transaction_type_id == Withdrawal)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif

									@elseif ($transactions->transaction_type_id == Transferred)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Refund')<span class="text-orange">Refunded</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif

		                        	@elseif ($transactions->transaction_type_id == Bank_Transfer)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif

									@elseif ($transactions->transaction_type_id == Received)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Refund')<span class="text-orange">Refunded</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif


									@elseif ($transactions->transaction_type_id == Exchange_From)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif

									@elseif ($transactions->transaction_type_id == Exchange_To)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif

									@elseif ($transactions->transaction_type_id == Voucher_Created)
		                        			@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			{{-- @if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif --}}

									@elseif ($transactions->transaction_type_id == Voucher_Activated)
		                        			@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif

									@elseif ($transactions->transaction_type_id == Request_From)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif
		                        			@if ($transactions->status == 'Refund')<span class="text-orange">Refunded</span>@endif

									@elseif ($transactions->transaction_type_id == Request_To)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Blocked')<span class="text-red">Cancelled</span>@endif
		                        			@if ($transactions->status == 'Refund')<span class="text-orange">Refunded</span>@endif

									@elseif ($transactions->transaction_type_id == Payment_Sent)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Refund')<span class="text-orange">Refunded</span>@endif

									@elseif ($transactions->transaction_type_id == Payment_Received)
											@if ($transactions->status == 'Success')<span class="text-green">Success</span>@endif
		                        			@if ($transactions->status == 'Pending')<span class="text-blue">Pending</span>@endif
		                        			@if ($transactions->status == 'Refund')<span class="text-orange">Refunded</span>@endif
									@endif</h4>
								@endif
							</div>
						</div>
					</div>

					<div class="panel-body">
						<div class="row">
							<form action="{{ url('admin/transactions/update/'.$transactions->id) }}" class="form-horizontal" id="transactions_form" method="POST">
								{{ csrf_field() }}
						        <input type="hidden" value="{{ $transactions->id }}" name="id" id="id">
						        <input type="hidden" value="{{ $transactions->transaction_type_id }}" name="transaction_type_id" id="transaction_type_id">
						        <input type="hidden" value="{{ $transactions->transaction_reference_id }}" name="transaction_reference_id" id="transaction_reference_id">
						        <input type="hidden" value="{{ $transactions->uuid }}" name="uuid" id="uuid">
						        <input type="hidden" value="{{ $transactions->user_id }}" name="user_id" id="user_id">
						        <input type="hidden" value="{{ $transactions->end_user_id }}" name="end_user_id" id="end_user_id">
						        <input type="hidden" value="{{ $transactions->currency->id }}" name="currency_id" id="currency_id">
						        <input type="hidden" value="{{ ($transactions->percentage) }}" name="percentage" id="percentage">
						        <input type="hidden" value="{{ ($transactions->charge_percentage) }}" name="charge_percentage" id="charge_percentage">
						        <input type="hidden" value="{{ ($transactions->charge_fixed) }}" name="charge_fixed" id="charge_fixed">
						        <input type="hidden" value="{{ base64_encode($transactions->payment_method_id) }}" name="payment_method_id" id="payment_method_id">

						        <input type="hidden" value="{{ base64_encode($transactions->merchant_id) }}" name="merchant_id" id="merchant_id">

						        <!--MerchantPaymentTable info's-->
								@if (isset($transactions->merchant_payment))
									<input type="hidden" value="{{ base64_encode($transactions->merchant_payment->gateway_reference) }}" name="gateway_reference" id="gateway_reference">
							        <input type="hidden" value="{{ $transactions->merchant_payment->order_no }}" name="order_no" id="order_no">
							        <input type="hidden" value="{{ $transactions->merchant_payment->item_name }}" name="item_name" id="item_name">
								@endif

								<div class="col-md-7">
									<div class="panel panel-default">
										<div class="panel-body">

											{{-- User --}}
									        {{-- @if ($transactions->user_id) --}}
												<div class="form-group">

														@if($transactions->transaction_type_id == Deposit)
														<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Transferred)
															<label class="control-label col-sm-3" for="user">Paid By</label>

														@elseif($transactions->transaction_type_id == Bank_Transfer)
															<label class="control-label col-sm-3" for="user">Transferred By</label>

														@elseif($transactions->transaction_type_id == Received)
															<label class="control-label col-sm-3" for="user">Paid By</label>

														@elseif($transactions->transaction_type_id == Exchange_From)
															<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Exchange_To)
															<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Voucher_Created)
															<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Voucher_Activated)
															<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Request_From)
															<label class="control-label col-sm-3" for="user">Request From</label>

														@elseif($transactions->transaction_type_id == Request_To)
															<label class="control-label col-sm-3" for="user">Request From</label>

														@elseif($transactions->transaction_type_id == Withdrawal)
															<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Payment_Sent)
															<label class="control-label col-sm-3" for="user">User</label>

														@elseif($transactions->transaction_type_id == Payment_Received)
															<label class="control-label col-sm-3" for="user">User</label>
														@endif

													<input type="hidden" class="form-control" name="user" value="

														@if($transactions->transaction_type_id == Deposit)
														{{ isset($transactions->deposit->user) ? $transactions->deposit->user->first_name.' '.$transactions->deposit->user->last_name : '-' }}

														@elseif($transactions->transaction_type_id == Transferred)
														{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Bank_Transfer)
														{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Received)
														{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Exchange_From)
														{{ isset($transactions->currency_exchange->user) ? $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Exchange_To)
														{{ isset($transactions->currency_exchange->user) ? $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Voucher_Created)
														{{ isset($transactions->voucher->user) ? $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Voucher_Activated)
														{{ isset($transactions->voucher->user) ? $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Request_From)
														{{ isset($transactions->request_payment->user) ? $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Request_To)
														{{ isset($transactions->request_payment->user) ? $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Withdrawal)
														{{ isset($transactions->withdrawal->user) ? $transactions->withdrawal->user->first_name.' '.$transactions->withdrawal->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Payment_Sent)
														{{ isset($transactions->user) ? $transactions->user->first_name.' '.$transactions->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Payment_Received)
														{{ isset($transactions->end_user) ? $transactions->end_user->first_name.' '.$transactions->end_user->last_name :"-" }}

														@endif">

													<div class="col-sm-9">
													  <p class="form-control-static">

													  	@if($transactions->transaction_type_id == Deposit)
														{{ isset($transactions->deposit->user) ? $transactions->deposit->user->first_name.' '.$transactions->deposit->user->last_name : '-' }}

														@elseif($transactions->transaction_type_id == Transferred)
														{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Bank_Transfer)
														{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Received)
														{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Exchange_From)
														{{ isset($transactions->currency_exchange->user) ? $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Exchange_To)
														{{ isset($transactions->currency_exchange->user) ? $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Voucher_Created)
														{{ isset($transactions->voucher->user) ? $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Voucher_Activated)
														{{ isset($transactions->voucher->user) ? $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Request_From)
														{{ isset($transactions->request_payment->user) ? $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Request_To)
														{{ isset($transactions->request_payment->user) ? $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Withdrawal)
														{{ isset($transactions->withdrawal->user) ? $transactions->withdrawal->user->first_name.' '.$transactions->withdrawal->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Payment_Sent)
														{{ isset($transactions->user) ? $transactions->user->first_name.' '.$transactions->user->last_name :"-" }}

														@elseif($transactions->transaction_type_id == Payment_Received)
														{{ isset($transactions->end_user) ? $transactions->end_user->first_name.' '.$transactions->end_user->last_name :"-" }}
														@endif</p>
													</div>
												</div>
											{{-- @endif --}}

											{{-- Receiver --}}
											{{-- @if ($transactions->end_user_id) --}}
												<div class="form-group">

													@if($transactions->transaction_type_id == Deposit)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Transferred)
													<label class="control-label col-sm-3" for="receiver">Paid To</label>

													@elseif($transactions->transaction_type_id == Received)
													<label class="control-label col-sm-3" for="user">Paid to</label>

													@elseif($transactions->transaction_type_id == Exchange_From)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Exchange_To)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Voucher_Created)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Voucher_Activated)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Request_From)
													<label class="control-label col-sm-3" for="receiver">Request To</label>

													@elseif($transactions->transaction_type_id == Request_To)
													<label class="control-label col-sm-3" for="receiver">Request To</label>

													@elseif($transactions->transaction_type_id == Withdrawal)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Payment_Sent)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>

													@elseif($transactions->transaction_type_id == Payment_Received)
													<label class="control-label col-sm-3" for="receiver">Receiver</label>
													@endif

													<input type="hidden" class="form-control" name="receiver" value="
															@if($transactions->transaction_type_id == Deposit)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Transferred)
															{{ (($transactions->receiver) ? $transactions->receiver->first_name.' '.$transactions->receiver->last_name :
															(($transactions->email) ? $transactions->email : $transactions->phone)) }}

															@elseif($transactions->transaction_type_id == Received)
															{{ isset($transactions->transfer->receiver) ? $transactions->transfer->receiver->first_name.' '.$transactions->transfer->receiver->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Exchange_From)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Exchange_To)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Voucher_Created)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Voucher_Activated)
															{{ isset($transactions->voucher->activator) ? $transactions->voucher->activator->first_name.' '.$transactions->voucher->activator->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Request_From)
															{{ isset($transactions->request_payment->receiver) ? $transactions->request_payment->receiver->first_name.' '.$transactions->request_payment->receiver->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Request_To)
															{{ isset($transactions->request_payment->receiver) ? $transactions->request_payment->receiver->first_name.' '.$transactions->request_payment->receiver->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Withdrawal)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Payment_Sent)
															{{ isset($transactions->end_user) ? $transactions->end_user->first_name.' '.$transactions->end_user->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Payment_Received)
															{{ isset($transactions->user) ? $transactions->user->first_name.' '.$transactions->user->last_name :"-" }}
															@endif"
													>

													<div class="col-sm-9">
													  <p class="form-control-static">@if($transactions->transaction_type_id == Deposit)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Transferred)
															{{ (($transactions->receiver) ? $transactions->receiver->first_name.' '.$transactions->receiver->last_name :
															(($transactions->email) ? $transactions->email : $transactions->phone)) }}

															@elseif($transactions->transaction_type_id == Received)
															{{ (($transactions->receiver) ? $transactions->receiver->first_name.' '.$transactions->receiver->last_name :
															(($transactions->email) ? $transactions->email : $transactions->phone)) }}


															@elseif($transactions->transaction_type_id == Exchange_From)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Exchange_To)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Voucher_Created)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Voucher_Activated)
															{{ isset($transactions->voucher->activator) ? $transactions->voucher->activator->first_name.' '.$transactions->voucher->activator->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Request_From)
															{{ isset($transactions->request_payment->receiver) ? $transactions->request_payment->receiver->first_name.' '.$transactions->request_payment->receiver->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Request_To)
															{{ isset($transactions->request_payment->receiver) ? $transactions->request_payment->receiver->first_name.' '.$transactions->request_payment->receiver->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Withdrawal)
															{{ '-' }}

															@elseif($transactions->transaction_type_id == Payment_Sent)
															{{ isset($transactions->end_user) ? $transactions->end_user->first_name.' '.$transactions->end_user->last_name :"-" }}

															@elseif($transactions->transaction_type_id == Payment_Received)
															{{ isset($transactions->user) ? $transactions->user->first_name.' '.$transactions->user->last_name :"-" }}
															@endif</p>
													</div>
												</div>
											{{-- @endif --}}


											@if ($transactions->uuid)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="transactions_uuid">Transaction ID</label>
													<input type="hidden" class="form-control" name="transactions_uuid" value="{{ $transactions->uuid }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $transactions->uuid }}</p>
													</div>
												</div>
											@endif

											@if ($transactions->transaction_type_id)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="type">Type</label>
													<input type="hidden" class="form-control" name="type" value="{{ str_replace('_', ' ', $transactions->transaction_type->name) }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ ($transactions->transaction_type->name == "Withdrawal") ? "Payout" : str_replace('_', ' ', $transactions->transaction_type->name) }}</p>
													</div>
												</div>
											@endif

											@if ($transactions->currency)
												<div class="form-group">
													<label class="control-label col-sm-3" for="currency">Currency</label>
													<input type="hidden" class="form-control" name="currency" value="{{ $transactions->currency->code }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $transactions->currency->code }}</p>
													</div>
												</div>
											@endif

											@if (isset($transactions->payment_method_id))
												<div class="form-group">
													<label class="control-label col-sm-3" for="payment_method">Payment Method</label>
													<input type="hidden" class="form-control" name="payment_method" value="{{ ($transactions->payment_method->name == "Mts") ? getCompanyName() : $transactions->payment_method->name }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ ($transactions->payment_method->name == "Mts") ? getCompanyName() : $transactions->payment_method->name }}</p>
													</div>
												</div>
											@endif


											@if ($transactions->bank)
							                    <div class="form-group">
													<label class="control-label col-sm-3" for="bank_name">Bank Name</label>
													<input type="hidden" class="form-control" name="bank_name" value="{{ $transactions->bank->bank_name }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $transactions->bank->bank_name }}</p>
													</div>
												</div>

							                    <div class="form-group">
													<label class="control-label col-sm-3" for="bank_branch_name">Branch Name</label>
													<input type="hidden" class="form-control" name="bank_branch_name" value="{{ $transactions->bank->bank_branch_name }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $transactions->bank->bank_branch_name }}</p>
													</div>
												</div>

							                    <div class="form-group">
													<label class="control-label col-sm-3" for="account_name">Account Name</label>
													<input type="hidden" class="form-control" name="account_name" value="{{ $transactions->bank->account_name }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ $transactions->bank->account_name }}</p>
													</div>
												</div>
											@endif

											@if ($transactions->file)
												<div class="form-group">
													<label class="control-label col-sm-3" for="attached_file">Attached File</label>
													<div class="col-sm-9">
													  <p class="form-control-static">
										                  <a href="{{ url('public/uploads/files/bank_attached_files').'/'.$transactions->file->filename }}" download={{ $transactions->file->filename }}><i class="fa fa-fw fa-download"></i>
										                  	{{ $transactions->file->originalname }}
										                  </a>
													  </p>
													</div>
												</div>
											@endif


											@if ($transactions->transaction_type_id == Withdrawal)
												@if ($transactions->withdrawal->payment_method->name == 'Bank')
													<div class="form-group">
														<label class="control-label col-sm-3" for="account_name">Account Name</label>
														<input type="hidden" class="form-control" name="account_name" value="{{ $transactions->withdrawal->withdrawal_detail->account_name }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $transactions->withdrawal->withdrawal_detail->account_name }}</p>
														</div>
													</div>

													<div class="form-group">
														<label class="control-label col-sm-3" for="account_number">Account Number/IBAN</label>
														<input type="hidden" class="form-control" name="account_number" value="{{ $transactions->withdrawal->withdrawal_detail->account_number }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $transactions->withdrawal->withdrawal_detail->account_number }}</p>
														</div>
													</div>

													<div class="form-group">
														<label class="control-label col-sm-3" for="swift_code">SWIFT Code</label>
														<input type="hidden" class="form-control" name="swift_code" value="{{ $transactions->withdrawal->withdrawal_detail->swift_code }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $transactions->withdrawal->withdrawal_detail->swift_code }}</p>
														</div>
													</div>

													<div class="form-group">
														<label class="control-label col-sm-3" for="bank_name">Bank Name</label>
														<input type="hidden" class="form-control" name="bank_name" value="{{ $transactions->withdrawal->withdrawal_detail->bank_name }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $transactions->withdrawal->withdrawal_detail->bank_name }}</p>
														</div>
													</div>
												@endif
											@endif


											@if ($transactions->created_at)
												<div class="form-group">
													<label class="control-label col-sm-3" for="created_at">Date</label>
													<input type="hidden" class="form-control" name="created_at" value="{{ $transactions->created_at }}">
													<div class="col-sm-9">
													  <p class="form-control-static">{{ dateFormat($transactions->created_at) }}</p>
													</div>
												</div>
						               		@endif

						               		@if ($transactions->status)
						                   		<div class="form-group">
													<label class="control-label col-sm-3" for="status">Change Status</label>
													<div class="col-sm-9">

														@if (isset($transactions->refund_reference) && isset($transactionOfRefunded))
								                          	<p class="form-control-static"><span class="label label-success">Already Refunded</span></p>
								                          	<p class="form-control-static"><span class="label label-danger">Refund Reference: <i>
										                          	<a id="transactionOfRefunded" href="{{ url("admin/transactions/edit/$transactionOfRefunded->id") }}">( {{ $transactions->refund_reference }} )</a>
										                          </i>
										                      </span>
										                  	</p>
									                    @else
															<select class="form-control select2" name="status" style="width: 60%;">

											                        @if ($transactions->transaction_type_id == Deposit)
																		<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                        <option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
											                            <option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																	@elseif ($transactions->transaction_type_id == Transferred)
											                            	@if ($transactions->status == 'Success')
																				<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                            	<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                            	<option value="Refund" {{ $transactions->status ==  'Refund' ? 'selected':"" }}>Refund</option>
												                            	<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
												                        	@else
												                        		<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                            	<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                            	<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
												                        	@endif

																	@elseif ($transactions->transaction_type_id == Received)
																			@if ($transactions->status == 'Success')
																				<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                            	<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                            	<option value="Refund" {{ $transactions->status ==  'Refund' ? 'selected':"" }}>Refund</option>
												                            	<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
												                        	@else
												                        		<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                            	<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                            	<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>
												                        	@endif

												                    @elseif ($transactions->transaction_type_id == Bank_Transfer)
																			<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
											                            	<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
											                            	<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																	@elseif ($transactions->transaction_type_id == Exchange_From)
																			<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                            <option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																	@elseif ($transactions->transaction_type_id == Exchange_To)
																			<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
												                            <option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																	@elseif ($transactions->transaction_type_id == Voucher_Created)
																		    <option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																			<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>

																	@elseif ($transactions->transaction_type_id == Voucher_Activated)
																			<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																			<option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>

																	@elseif ($transactions->transaction_type_id == Request_From)
																	    @if ($transactions->status == 'Pending')
												                        	<option value="Pending" {{ $transactions->status ==  'Pending'? 'selected':"" }}>Pending</option>
																			<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																		@elseif ($transactions->status == 'Blocked')
												                        	<option value="Pending" {{ $transactions->status ==  'Pending'? 'selected':"" }}>Pending</option>
																			<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																		@elseif ($transactions->status == 'Success')
												                        	<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																			<option value="Refund"  {{ $transactions->status == 'Refund' ? 'selected':"" }}>Refund</option>
																		@endif

																	@elseif ($transactions->transaction_type_id == Request_To)
																		@if ($transactions->status == 'Pending')
												                        	<option value="Pending" {{ $transactions->status ==  'Pending'? 'selected':"" }}>Pending</option>
																			<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																		@elseif ($transactions->status == 'Blocked')
												                        	<option value="Pending" {{ $transactions->status ==  'Pending'? 'selected':"" }}>Pending</option>
																			<option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

																		@elseif ($transactions->status == 'Success')
												                        	<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																			<option value="Refund"  {{ $transactions->status == 'Refund' ? 'selected':"" }}>Refund</option>
																		@endif

																	@elseif ($transactions->transaction_type_id == Withdrawal)
																			<option value="Success" {{ $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
													                        <option value="Pending"  {{ $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                            <option value="Blocked"  {{ $transactions->status == 'Blocked' ? 'selected':"" }}>Cancel</option>

												                    @elseif ($transactions->transaction_type_id == Payment_Sent)
														                    @if ($transactions->status ==  'Success')
												                        		<option value="Success" {{ isset($transactions->status) && $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																				<option value="Pending"  {{ isset($transactions->status) && $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
																				<option value="Refund"  {{ isset($transactions->status) && $transactions->status == 'Refund' ? 'selected':"" }}>Refund</option>
												                        	@else
												                        		<option value="Success" {{ isset($transactions->status) && $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																				<option value="Pending"  {{ isset($transactions->status) && $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                        	@endif

										                        	@elseif ($transactions->transaction_type_id == Payment_Received)
																			@if ($transactions->status ==  'Success')
												                        		<option value="Success" {{ isset($transactions->status) && $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																				<option value="Pending"  {{ isset($transactions->status) && $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
																				<option value="Refund"  {{ isset($transactions->status) && $transactions->status == 'Refund' ? 'selected':"" }}>Refund</option>
												                        	@else
												                        		<option value="Success" {{ isset($transactions->status) && $transactions->status ==  'Success'? 'selected':"" }}>Success</option>
																				<option value="Pending"  {{ isset($transactions->status) && $transactions->status == 'Pending' ? 'selected':"" }}>Pending</option>
												                        	@endif

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

											@if ($transactions->subtotal)
							                    <div class="form-group">
													<label class="control-label col-sm-4 pull-left" for="subtotal">Amount</label>
													<input type="hidden" class="form-control" name="subtotal" value="{{ $transactions->subtotal }}">
													<div class="col-sm-7">
													  <p class="form-control-static pull-right">{{  moneyFormat($transactions->currency->symbol, formatNumber($transactions->subtotal)) }}</p>
													</div>
												</div>
											@endif

						                    <div class="form-group total-deposit-feesTotal-space">
												<label class="control-label col-sm-4 pull-left" for="fee">Fees
													<span>
														<small class="transactions-edit-fee">
															@if (isset($transactions))
																({{(($transactions->transaction_type->name == "Payment_Sent") ? "0" : formatNumber($transactions->percentage))}}% + {{formatNumber($transactions->charge_fixed)}})
															@else
																({{0}}%+{{0}})
															@endif
														</small>
													</span>
												</label>

												@php
													$total_transaction_fees = $transactions->charge_percentage + $transactions->charge_fixed;
												@endphp
												<input type="hidden" class="form-control" name="fee" value="{{ ($total_transaction_fees) }}">
												<div class="col-sm-7">
												  <p class="form-control-static pull-right">{{  moneyFormat($transactions->currency->symbol, formatNumber($total_transaction_fees)) }}</p>
												</div>
											</div>

											<hr class="increase-hr-height">

											@if ($transactions->total)
							                    <div class="form-group total-deposit-space">
													<label class="control-label col-sm-4 pull-left" for="total">Total</label>
													<input type="hidden" class="form-control" name="total" value="{{ ($transactions->total) }}">
													<div class="col-sm-7">
													  {{-- <p class="form-control-static pull-right">{{  moneyFormat($transactions->currency->symbol, formatNumber($transactions->total)) }}</p> --}}
													  <p class="form-control-static pull-right">{{  moneyFormat($transactions->currency->symbol, str_replace("-",'',formatNumber($transactions->total)) ) }}</p>
													</div>
												</div>
											@endif

										</div>
									</div>
								</div>


								<div class="row">
									<div class="col-md-11">
										<div class="col-md-2"></div>
										<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/transactions') }}">Cancel</a></div>
										@if (!isset($transactions->refund_reference))
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
	     	$("#transactions_edit").attr("disabled", true);
	     	$('#cancel_anchor').attr("disabled","disabled");
            $(".spinner").show();
            $("#transactions_edit_text").text('Updating...');

            // Click False
			$('#transactions_edit').click(false);
			$('#cancel_anchor').click(false);
	  });

	  $('#transactionOfRefunded').css('color', 'white');
	});

</script>
@endpush
