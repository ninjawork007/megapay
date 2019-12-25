@extends('admin.layouts.master')
@section('title', 'Disputes')

@section('page_content')

	<div class="box">
		<div class="box-body">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-10">
							Dispute
						</div>
						<div class="col-md-2">
							<select class="form-control" name="status" id="status">
								<option value="Open" <?= ($dispute->status == 'Open') ? 'selected' : '' ?>>Open</option>
								<option value="Solve" <?= ($dispute->status == 'Solve') ? 'selected' : '' ?>>Solve</option>
								<option value="Close" <?= ($dispute->status == 'Close') ? 'selected' : '' ?>>Close</option>
							</select>
							<input type="hidden" name="id" value="{{$dispute->id}}" id="id">
						</div>
					</div>
				</div>

				<div class="panel-body">

					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<div class="form-group">
									<label class="control-label col-sm-3">Title</label>
									<div class="col-sm-9">
									  <p>{{ $dispute->title  }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-7">
						<div class="form-group">
							<label class="control-label col-sm-3">Transaction ID</label>
							<div class="col-sm-9">
							  <p>{{ (isset($dispute->transaction)) ? $dispute->transaction->uuid : ''  }}</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-sm-3">Status</label>
							<div class="col-sm-9">
							  	<p>
							  		@if($dispute->status == 'Open')
										<span class="label label-info">Open</span>
									@elseif($dispute->status =='Solve')
										<span class="label label-success">Solved</span>
									@elseif($dispute->status =='Close')
										<span class="label label-danger">Closed</span>
									@endif
								</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-sm-3">Date</label>
							<div class="col-sm-9">
							  <p>{{ dateFormat($dispute->created_at) }}</p>
							</div>
						</div>
					</div>

					<div class="col-md-5">
	                    <div class="form-group">
							<label class="control-label col-sm-4">Claimant</label>
							<div class="col-sm-7">
							  <p>{{ isset($dispute->claimant) ? $dispute->claimant->first_name .' '.$dispute->claimant->last_name :"-" }}</p>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-sm-4">Defendant</label>
							<div class="col-sm-7">
							  <p>{{ isset($dispute->defendant) ? $dispute->defendant->first_name .' '.$dispute->defendant->last_name :"-" }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="box">
		<div class="box-body">
			<form action="{{url('admin/dispute/reply')}}" id="reply" method="post" enctype="multipart/form-data">
				{{ csrf_field() }}

				<div class="form-group col-sm-12">
					<label for="email">Reply</label>
					<input type="hidden" name="dispute_id" value="{{ $dispute->id }}">
					<textarea name="description" id="description" class="form-control"></textarea>

					@if($errors->has('description'))
						<span class="error">
							{{ $errors->first('description') }}
						</span>
					@endif
				</div>

				<div class="form-group col-md-3">
					<label class="control-label" for="exampleInputFile">File</label>
					<input class="form-controls" type="file" name="file" id="file">
				</div>

				<div class="form-group col-sm-12 text-right">
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
				<div class="clearfix"></div>
			</form>
		</div>
	</div>


	@if( $dispute->disputeDiscussions->count() > 0 )
		<div class="box">
			<div class="box-body">
				@foreach($dispute->disputeDiscussions as $result)
					@if($result->type == 'User' )
						<div class="well well-sm">
							<div class="media">
							  <div class="media-left">

							  	@if(!empty($result->user->picture))
								  	<?php
								  		$userAvatar = $result->user->picture;
								  	?>
							  	 	<img src='{{url("public/user_dashboard/profile/$userAvatar")}}' class="media-object" style="width:60px">
							  	@else
							    	<img src="{{url('public/images/avatar.png')}}" class="media-object" style="width:60px">
							    @endif

							  </div>
							    <div class="media-body">
							       <h4><a href="{{ url('admin/users/edit/'. $result->user->id)}}">{{$result->user->first_name.' '.$result->user->last_name}}</a> <small><i>{{ dateFormat($result->created_at) }}</i></small> &nbsp;
							       	</h4>
							      <p>{!! $result->message !!}</p>

						@if($result->file)
						----------------<br>
						<?php
							$str_arr = explode('_', $result->file);
							$str_position = strlen($str_arr[0])+1;
							$file_name = substr($result->file,$str_position);
						?>
						<h5>
						<a class="text-info" href="{{url('public/uploads/files').'/'.$result->file}}"><i class="fa fa-download"></i> {{$file_name}}
						</a>
					</h5>
						@endif
							    </div>
							</div>
						</div>

					@else
						<div class="well well-sm">
							<div class="media">

								<div class="media-left">
									@if(!empty($result->admin->picture))
										<?php
										$adminAvatar = $result->admin->picture;
										?>
									 <img src='{{url("public/uploads/userPic/$adminAvatar")}}' class="media-object" style="width:60px">
									@else
										<img src="{{url('public/images/avatar.png')}}" class="media-object" style="width:60px">
									@endif
								</div>

							    <div class="media-body">
							      <h4><a href="{{ url('admin/admin-user/edit/'. $result->admin->id)}}">{{$result->admin->first_name.' '.$result->admin->last_name}}</a> <small><i>{{ dateFormat($result->created_at) }}</i></small> &nbsp;
							      </h4>

							      <p>{!! $result->message !!}</p>

									@if($result->file)
									----------------<br>
										<?php
											$str_arr = explode('_', $result->file);
											$str_position = strlen($str_arr[0])+1;
											$file_name = substr($result->file,$str_position);
										?>
										<h5>
											<a class="text-info" href="{{url('public/uploads/files').'/'.$result->file}}">
												<i class="fa fa-download"></i> {{$file_name}}
											</a>
										</h5>
									@endif
							    </div>
							</div>
						</div>
					@endif
				@endforeach
			</div>
		</div>
	@endif

@endsection


@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/js/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

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
            extension: "Please select (docx, rtf, doc, pdf, png, jpg, jpeg, gif or bmp) file!"
          },
        },
	});

	$("#status").on('change', function()
	{
		var status = $(this).val();

		var id = $("#id").val();

		$.ajax({
			method: "POST",
			url: SITE_URL+"/admin/dispute/change_reply_status",
			data: { status: status, id:id}
		})
	    .done(function( data )
	    {
	    	message = 'Dispute discussion '+ status +' successfully done.';
	    	var messageBox = '<div class="alert alert-success" role="alert">'+ message +'</div><br>';
	    	$("#alertDiv").html(messageBox);
			location.reload().delay(10000);
	    });
	});

</script>

@endpush