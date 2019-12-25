@extends('admin.layouts.master')
@section('title', 'Edit Identity Verification')

@section('page_content')
	<div class="box">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="row">
								<div class="col-md-10">
									<h4 class="text-left">Identity Verification Details</h4>
								</div>
								<div class="col-md-2">
									@if ($documentVerification->status)
										<h4 class="text-left">Status : @if ($documentVerification->status == 'approved')<span class="text-green">Approved</span>@endif
				                    	@if ($documentVerification->status == 'pending')<span class="text-blue">Pending</span>@endif
				            			@if ($documentVerification->status == 'rejected')<span class="text-red">Rejected</span>@endif</h4>
									@endif
								</div>
							</div>
						</div>

						<div class="panel-body">
							<div class="row">
								<form action="{{ url('admin/identity-proofs/update') }}" class="form-horizontal" id="deposit_form" method="POST">
										{{ csrf_field() }}
							        <input type="hidden" value="{{ $documentVerification->id }}" name="id" id="id">
							        <input type="hidden" value="{{ $documentVerification->user_id }}" name="user_id" id="user_id">
							        <input type="hidden" value="{{ $documentVerification->verification_type }}" name="verification_type" id="verification_type">

									<div class="col-md-7">
										<div class="panel panel-default">
											<div class="panel-body">

												@if ($documentVerification->user_id)
													<div class="form-group">
														<label class="control-label col-sm-3" for="user">User</label>
														<input type="hidden" class="form-control" name="user" value="{{ isset($documentVerification->user) ? $documentVerification->user->first_name.' '.$documentVerification->user->last_name :"-" }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ isset($documentVerification->user) ? $documentVerification->user->first_name.' '.$documentVerification->user->last_name :"-" }}</p>
														</div>
													</div>
												@endif

												@if ($documentVerification->identity_type)
								                    <div class="form-group">
														<label class="control-label col-sm-3" for="identity_type">Identity Type</label>
														<input type="hidden" class="form-control" name="identity_type" value="{{ $documentVerification->identity_type }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ str_replace('_', ' ', ucfirst($documentVerification->identity_type)) }}</p>
														</div>
													</div>
												@endif

												@if ($documentVerification->identity_number)
								                    <div class="form-group">
														<label class="control-label col-sm-3" for="identity_number">Identity Number</label>
														<input type="hidden" class="form-control" name="identity_number" value="{{ $documentVerification->identity_number }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ $documentVerification->identity_number }}</p>
														</div>
													</div>
												@endif

												@if ($documentVerification->created_at)
													<div class="form-group">
														<label class="control-label col-sm-3" for="created_at">Date</label>
														<input type="hidden" class="form-control" name="created_at" value="{{ $documentVerification->created_at }}">
														<div class="col-sm-9">
														  <p class="form-control-static">{{ dateFormat($documentVerification->created_at) }}</p>
														</div>
													</div>
						                   		@endif

						                   		@if ($documentVerification->status)
							                   		<div class="form-group">
														<label class="control-label col-sm-3" for="status">Change Status</label>
														<div class="col-sm-9">
															<select class="form-control select2" name="status" style="width: 60%;">
																<option value="approved" {{ $documentVerification->status ==  'approved'? 'selected':"" }}>Approved</option>
																<option value="pending"  {{ $documentVerification->status == 'pending' ? 'selected':"" }}>Pending</option>
																<option value="rejected"  {{ $documentVerification->status == 'rejected' ? 'selected':"" }}>Rejected</option>
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

	                                            @if ($documentVerification->file)
	                                            <div>

	                                            	<input type="hidden" class="form-control" name="identity_file" value="{{ $documentVerification->file->filename }}">
	                                                <ul style="list-style-type: none;">
	                                                	<h4 style="text-decoration: underline;">Identity Proof</h4>
													    <li> {{ $documentVerification->file->filename }}
															<a class="text-info pull-right" href="{{ url('public/uploads/user-documents/identity-proof-files').'/'.$documentVerification->file->filename }}">
																<i class="fa fa-download"></i>
			                                                </a>
													    </li>
													</ul>
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-11">
											<div class="col-md-2"></div>
											<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/identity-proofs') }}">Cancel</a></div>
											<div class="col-md-1">
												<button type="submit" class="btn button-secondary pull-right" id="deposits_edit">
	                                                <i class="spinner fa fa-spinner fa-spin"></i> <span id="deposits_edit_text">Update</span>
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

	// disabling submit and cancel button after form submit
	$(document).ready(function()
	{
	  $('form').submit(function()
	  {
	     	$("#deposits_edit").attr("disabled", true);

	     	$('#cancel_anchor').attr("disabled","disabled");

            $(".spinner").show();

            $("#deposits_edit_text").text('Updating...');

            // Click False
			$('#deposits_edit').click(false);
			$('#cancel_anchor').click(false);
	  });
	});
</script>
@endpush
