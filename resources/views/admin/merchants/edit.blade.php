@extends('admin.layouts.master')
@section('title', 'Edit Merchant')

@section('head_style')
	<!-- sweetalert -->
	<link rel="stylesheet" type="text/css" href="{{ asset('public/backend/sweetalert/sweetalert.css')}}">
@endsection

@section('page_content')
	<div class="box">
	   <div class="panel-body">
	        <ul class="nav nav-tabs cus" role="tablist">
	            <li class="active">
	              <a href='{{url("admin/merchant/edit/$merchant->id")}}'>Profile</a>
	            </li>

	            <li>
	              <a href="{{url("admin/merchant/payments/$merchant->id")}}">Payments</a>
	            </li>
	       </ul>
	      <div class="clearfix"></div>
	   </div>
	</div>

	<div class="row">
		<div class="col-md-10">
			<h4 class="pull-left">{{ $merchant->business_name }}</h4>
		</div>
		<div class="col-md-2">
			@if ($merchant->status)
				<h4 class="pull-right">@if ($merchant->status == 'Approved')<span class="text-green">Approved</span>@endif
				@if ($merchant->status == 'Moderation')<span class="text-blue">Moderation</span>@endif
				@if ($merchant->status == 'Disapproved')<span class="text-red">Disapproved</span>@endif</h4>
			@endif
		</div>
	</div>

	<div class="box">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<form action="{{ url('admin/merchant/update') }}" class="form-horizontal" id="merchant_edit_form" method="POST" enctype="multipart/form-data">
							{{ csrf_field() }}

					        <input type="hidden" value="{{ $merchant->id }}" name="id" id="id">

							<div class="col-md-7">
			                    @if ($merchant->user)
									<div class="form-group">
										<label class="control-label col-sm-3" for="user">User</label>
										<div class="col-sm-9">
											<p class="form-control-static">{{ isset($merchant->user) ? $merchant->user->first_name.' '.$merchant->user->last_name :"-" }}</p>
										</div>
									</div>
								@endif

								@if ($merchant->merchant_uuid)
									<div class="form-group">
										<label class="control-label col-sm-3" for="merchant_uuid">Merchant ID</label>
										<div class="col-sm-9">
											<p class="form-control-static">{{ $merchant->merchant_uuid }}</p>
										</div>
									</div>
								@endif

								@if ($merchant->type)
				                    <div class="form-group">
										<label class="control-label col-sm-3" for="type">Type</label>
										<div class="col-sm-9">
											<select class="select2" name="type" id="type">
												<option value="standard" {{ $merchant->type ==  'standard'? 'selected':"" }}>Standard</option>
												<option value="express"  {{ $merchant->type == 'express' ? 'selected':"" }}>Express</option>
											</select>
										</div>
									</div>
								@endif

								@if ($merchant->business_name)
				                    <div class="form-group">
										<label class="control-label col-sm-3" for="business_name">Business Name</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="business_name" value="{{ $merchant->business_name }}">

											@if($errors->has('business_name'))
												<span class="error">
													<strong class="text-danger">{{ $errors->first('business_name') }}</strong>
												</span>
											@endif
										</div>
									</div>
								@endif

								@if ($merchant->site_url)
				                    <div class="form-group">
										<label class="control-label col-sm-3" for="site_url">Site Url</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="site_url" value="{{ $merchant->site_url }}">

											@if($errors->has('site_url'))
												<span class="error">
													<strong class="text-danger">{{ $errors->first('site_url') }}</strong>
												</span>
											@endif
										</div>
									</div>
								@endif

								@if ($merchant->currency_id)
				                    <div class="form-group">
										<label class="control-label col-sm-3" for="site_url">Currency</label>
										<div class="col-sm-9">
											<select class="form-control select2" name="currency_id">
												<!--pm_v2.3-->
												@foreach($activeCurrencies as $result)
														<option value="{{ $result->id }}" {{ $merchant->currency_id == $result->id ? 'selected="selected"' : '' }}>{{ $result->code }}</option>
												@endforeach
											</select>

											@if($errors->has('currency_id'))
												<span class="error">
													<strong class="text-danger">{{ $errors->first('currency_id') }}</strong>
												</span>
											@endif
										</div>
									</div>
								@endif

								<div class="form-group">
	                                <label class="col-sm-3 control-label" for="merchantGroup">Group</label>
	                                <div class="col-sm-6">
	                                    <select class="select2" name="merchantGroup" id="merchantGroup">
	                                        @foreach ($merchantGroup as $group)
	                                          <option value='{{ $group->id }}' {{ isset($group) && $group->id == $merchant->merchant_group_id ? 'selected':""}}> {{ $group->name }}</option>
	                                        @endforeach
	                                    </select>
	                                </div>
	                            </div>

			                    <div class="form-group">
									<label class="control-label col-sm-3" for="site_url">Fee (%)</label>
									<div class="col-sm-9">
										<input type="text" class="form-control" name="fee" value="{{ $merchant->fee }}" id="fee">
										@if($errors->has('fee'))
											<span class="error">
												<strong class="text-danger">{{ $errors->first('fee') }}</strong>
											</span>
										@endif
									</div>
								</div>


			                    <div class="form-group">
									<label class="control-label col-sm-3" for="logo">Logo</label>
									<div class="col-sm-9">
									  <input type="file" name="logo" class="form-control input-file-field" data-rel="{{ !empty($merchant->logo) ? $merchant->logo : '' }}" id="logo"
									  	value="{{ !empty($merchant->logo) ? $merchant->logo : '' }}">
									  	@if($errors->has('logo'))
										<span class="error">
											<strong class="text-danger">{{ $errors->first('logo') }}</strong>
										</span>
										@endif
										@if (!empty($merchant->logo))
						                  <div class="setting-img">
						                    <div class="img-wrap">
						                        <img src='{{ url('public/user_dashboard/merchant/'.$merchant->logo) }}'  class="img-responsive">
						                    </div>
						                    <span class="remove_img_preview" id="flag_preview"></span>
						                  </div>
						                @else
						                  <img src='{{ url('public/uploads/userPic/default-image.png') }}' width="120" height="80" class="img-responsive">
						                @endif
									</div>
								</div>

		                   		@if ($merchant->status)
			                   		<div class="form-group">
										<label class="control-label col-sm-3" for="status">Change Status</label>
										<div class="col-sm-9">
											<select class="select2" name="status" id="status">
												<option value="Approved" {{ isset($merchant->status) && $merchant->status ==  'Approved'? 'selected':"" }}>Approved</option>
												<option value="Moderation"  {{ isset($merchant->status) && $merchant->status == 'Moderation' ? 'selected':"" }}>Moderation</option>
												<option value="Disapproved"  {{ isset($merchant->status) && $merchant->status == 'Disapproved' ? 'selected':"" }}>Disapproved</option>
											</select>
										</div>
									</div>
								@endif
							</div>

							<div class="row">
								<div class="col-md-11">
									<div class="col-md-2"></div>
									<div class="col-md-2"><a id="cancel_anchor" class="btn btn-danger pull-left" href="{{ url('admin/merchants') }}">Cancel</a></div>
									<div class="col-md-1">
										<button type="submit" class="btn button-secondary pull-right" id="merchant_edit">
			                                <i class="spinner fa fa-spinner fa-spin"></i> <span id="merchant_edit_text">Update</span>
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
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/js/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>

<!-- sweetalert -->
<script src="{{ asset('public/backend/sweetalert/sweetalert.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">

	$(function () {
	    $(".select2").select2({
	    });
	});

	$(document).ready(function()
    {
	    $("#merchantGroup").change(function()
	    {
	        var merchant_group_id = $("#merchantGroup").val();
	        $.ajax({
	            headers:
	            {
	                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	            },
	            method: "POST",
	            url: SITE_URL+"/admin/merchants/change-fee-with-group-change",
	            dataType: "json",
	            data: {
	                'merchant_group_id':merchant_group_id,
	            }
	        })
	        .done(function(response)
	        {
	            // console.log(response);
				if(response.status == true)
				{
				 	$('#fee').val(response.fee);
				}
	        });
	    });
	});

	$(document).ready(function()
    {
      $('.remove_img_preview').click(function()
      {
        var logo = $('#logo').attr('data-rel');
        var merchant_id = $('#id').val();
        if(logo)
        {
          $.ajax(
          {
            headers:
            {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type : "POST",
            url : SITE_URL+"/admin/merchant/delete-merchant-logo",
            async : false,
            data: {
              'logo' : logo,
              'merchant_id' : merchant_id,
            },
            dataType : 'json',
            success: function(reply)
            {
              if (reply.success == 1)
              {
					swal({title: "Deleted!", text: reply.message, type: "success"},
	                   function(){
	                       location.reload();
	                   }
	                );
              }
              else
              {
                  alert(reply.message);
                  location.reload();
              }
            }
          });
        }
      });
    });

    jQuery.validator.addMethod("letters_with_spaces", function(value, element)
    {
        return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
    }, "Please enter letters only!");

    $.validator.setDefaults({
        highlight: function(element) {
           $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
    });

    $('#merchant_edit_form').validate({
        rules: {
            user: {
                required: true,
                letters_with_spaces: true,
            },
            business_name: {
                required: true,
            },
            site_url: {
                required: true,
                url: true,
            },
            type: {
                required: true,
                lettersonly: true,
            },
            logo: {
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          logo: {
            extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
          },
          type: {
            lettersonly: "Please enter letters only!"
          }
        },
    });
</script>

@endpush
