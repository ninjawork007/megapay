@extends('user_dashboard.layouts.app')

@section('content')
<section class="section-06 history padding-30">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-xs-12 col-sm-12 mb20 marginTopPlus">
                <div class="flash-container">
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>@lang('message.dashboard.dispute.discussion.sidebar.header')</h4>
                    </div>
                    <div>

					<div class="ticket-line mt10">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.dispute-id')</div>
					   <div class="generalcolor-txt">{{ $dispute->code }}</div>
					</div>
                    <hr>

                    <div class="ticket-line mb20">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.discussion.sidebar.title')</div>
					   <div class="generalcolor-txt">{{ $dispute->title }}</div>
					</div>
                    <hr>

					<div class="ticket-line mb20">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.claimant')</div>
					   <div class="generalcolor-txt">{{ $dispute->claimant->first_name .' '.$dispute->claimant->last_name}}</div>
					</div>
                    <hr>


					<div class="ticket-line mb20">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.defendant')</div>
					   <div class="generalcolor-txt">{{ $dispute->defendant->first_name .' '.$dispute->defendant->last_name}}</div>
					</div>
                    <hr>

					<div class="ticket-line mt10">
					   {{-- <div class="titlecolor-txt">@lang('general.date')</div> --}}
					   <div class="titlecolor-txt">@lang('message.form.date')</div>
					   <div class="generalcolor-txt">{{ dateFormat($dispute->created_at) }}</div>
					</div>
					<hr />

					<div class="ticket-line">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.transaction-id')</div>
					   <div class="generalcolor-txt">{{ $dispute->transaction->uuid }}</div>
					</div>
					<hr />

					<div class="ticket-line">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.status')</div>
					   <div class="generalcolor-txt">
					   		@if($dispute->status =='Open')
								<span class="badge badge-primary">@lang('message.dashboard.dispute.status-type.open')</span>
							@elseif($dispute->status =='Solve')
								<span class="badge badge-success">@lang('message.dashboard.dispute.status-type.solved')</span>
							@elseif($dispute->status =='Close')
								<span class="badge badge-danger">@lang('message.dashboard.dispute.status-type.closed')</span>
							@endif
                        </div>
					</div>
					<hr />

					<div class="ticket-line mb20">
					   <div class="titlecolor-txt">@lang('message.dashboard.dispute.discussion.sidebar.reason')</div>
					   <div class="generalcolor-txt">{{ $dispute->reason->title }}</div>
					</div>
					<hr>

                    <div class="ticket-btn ticket-line mb20">
						@if($dispute->claimant_id == Auth::user()->id)
							@if ($dispute->status == 'Open')
								<label> @lang('message.dashboard.dispute.discussion.sidebar.change-status')</label>
								<select class="form-control" name="status" id="status">
									<option value="Open" <?= ($dispute->status == 'Open') ? 'selected' : '' ?>>@lang('message.dashboard.dispute.status-type.open')</option>
									<option value="Close" <?= ($dispute->status == 'Close') ? 'selected' : '' ?>>@lang('message.dashboard.dispute.status-type.close')</option>
								</select>
							@endif
						@endif
							<input type="hidden" name="id" value="{{$dispute->id}}" id="id">
						</div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-xs-12 col-sm-12 mb20 marginTopPlus">
				@include('user_dashboard.layouts.common.alert')
				<span id="alertDiv"></span>

                <div class="flash-container">
				 <h2 class="ash-font">@lang('message.dashboard.dispute.discussion.form.title')</h2>
                </div>
				<hr>

				@if($dispute->status == 'Open')
					<form action="{{url('dispute/reply')}}" id="reply" method="post" enctype="multipart/form-data">
						<input type="hidden" name="dispute_id" value="{{ $dispute->id }}">
						{{ csrf_field() }}
						<div class="mt20 mb20">
						<div class="h6">@lang('message.dashboard.dispute.discussion.form.message')</div>

						<textarea name="description" id="description" class="form-control"></textarea>
							@if($errors->has('description'))
							<span class="error">
								{{ $errors->first('description') }}
							</span>
							@endif
						</div>

						<div class="file-box">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label" for="exampleInputFile">@lang('message.dashboard.dispute.discussion.form.file')</label>
										<input class="form-control" type="file" name="file" id="file">
									</div>
								</div>
							</div>
						</div>

						<div class="file-footer mb20">
							<div class="text-right">
							    <button class="btn btn-cust">@lang('message.dashboard.button.submit')</button>
							</div>
						</div>
					</form>
				@endif

                <div class="">
					<div class="reply-views mt20">
						<div class="reply-box">
							<div class="left">
								<div class="profile-id-pic left">
									@if(!empty($dispute->claimant->picture))
										<?php
											$claimantAvatar = $dispute->claimant->picture;
										?>
										<img src='{{url("public/user_dashboard/profile/$claimantAvatar")}}' class="img-responsive" style="width:60px;">
									@else
										<img src="{{url('public/user_dashboard/images/avatar.jpg')}}" alt="" class="img-responsive" style="width:60px;">
									@endif
								</div>
								<div class="left">
								   <h5 class="">{{$dispute->claimant->first_name .' '.$dispute->claimant->last_name}}</h5>
								</div>
							</div>
							<div class="right">
							 <div class="update-time">{{ dateFormat($dispute->created_at) }}</div>
							</div>
							<div class="clearfix"></div>
						</div>

						<div class="reply-details">
							{!! $dispute->description !!}
						</div>
					</div>
            	</div>
            	<br>

				@if( $dispute->disputeDiscussions->count() > 0 )
					@foreach($dispute->disputeDiscussions as $result)
						@if($result->type == 'User' )
		                    <div class="">
		                       <div class="reply-views">
		                         <div class="reply-box">
								  <div class="left">
								   <div class="profile-id-pic left">

									@if(!empty($result->user->picture))
										<?php
										$userAvatar = $result->user->picture;
										?>
										<img src='{{url("public/user_dashboard/profile/$userAvatar")}}' class="rounded-circle" style="width:60px;">
									@else
										<img src="{{url('public/user_dashboard/images/avatar.jpg')}}" alt="" class="rounded-circle" style="width:60px;">
									@endif

								   </div>
								   <div class="left">
								       <h5 class="">{{ $result->user->first_name.' '.$result->user->last_name}}</h5>
								   </div>
								   </div>
								   <div class="right">
								     <div class="update-time">{{ dateFormat($result->created_at) }}</div>
								   </div>
								   <div class="clearfix"></div>
								 </div>
								<div class="reply-details">

								<p>{!! $result->message !!}</p>
								@if($result->file)
									<?php
										$str_arr = explode('_', $result->file);
										$str_position = strlen($str_arr[0])+1;
										$file_name = substr($result->file,$str_position);
									?>
									----------------<br>
									<h5>
									<a class="text-info" href="{{url('public/uploads/files').'/'.$result->file}}"><i class="fa fa-download"></i> {{$file_name}}</a>
									</h5>
								@endif

								</div>
							  </div>
		                	</div>
		                	<br>
	                	@else
		                    <div class="">
		                       <div class="reply-views">
		                         	<div class="reply-box">
									    <div class="left">
										   <div class="profile-id-pic left">
												@if(!empty($result->admin->picture))
													<?php
													$adminAvatar = $result->admin->picture;
													?>
													<img src='{{url("public/uploads/userPic/$adminAvatar")}}' class="rounded-circle" style="width:60px;">
												@else
													<img src="{{url('public/user_dashboard/images/avatar.jpg')}}" alt="" class="rounded-circle" style="width:60px;">
												@endif
										   </div>
										   <div class="left">
										       <h5 class=""><?php echo $result->admin->first_name.' '.$result->admin->last_name ?></h5>
										   </div>
									    </div>
									    <div class="right">
									      <div class="update-time">{{ dateFormat($result->created_at) }}</div>
									    </div>
									    <div class="clearfix"></div>
								 	</div>

									<div class="reply-details">
										<p>{!! $result->message !!}</p>
										@if($result->file)

										<?php
											$str_arr = explode('_', $result->file);
											$str_position = strlen($str_arr[0])+1;
											$file_name = substr($result->file,$str_position);
										?>

										----------------<br>
										<h5>
										<a class="text-info" href="{{url('public/uploads/files').'/'.$result->file}}"><i class="fa fa-download"></i> {{$file_name}}</a>
										</h5>
										@endif
									</div>
							    </div>
		                	</div>
		                	<br>
	                	@endif
					@endforeach
				@endif
            </div>
    	</div>
	</div>
</section>
@endsection

@section('js')
<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script>

jQuery.extend(jQuery.validator.messages, {
    required: "{{__('This field is required.')}}",
})

$('#reply').validate({
	rules: {
			description: {
				required: true,
			},
			file: {
	            extension: "docx|rtf|doc|pdf|png|jpg|jpeg|gif|bmp",
	        },
		},
		messages: {
          file: {
            extension: "{{__("Please select (docx, rtf, doc, pdf, png, jpg, jpeg, gif or bmp) file!")}}"
          }
        },
	});

$("#status").on('change', function()
{
	var status = $(this).val();
	var id = $("#id").val();
	$.ajax({
		method: "POST",
		url: SITE_URL+"/dispute/change_reply_status",
		data: { status: status, id:id}
	})
	.done(function( data )
	{
		if (status == 'Open') { status = 'Open'}
		else if (status == 'Solve') { status = 'Solved'}
		else if (status == 'Close') { status = 'Closed'}

		message = 'Dispute Discussion '+ status +' Successfully!';
		var messageBox = '<div class="alert alert-success" role="alert">'+ message +'</div><br>';
		$("#alertDiv").html(messageBox);

		setTimeout(function()
		{
		  location.reload()
		}, 2000);
	});
});
</script>
@endsection