@extends('admin.layouts.master')
@section('title', 'General Settings')

@section('head_style')
  <!-- sweetalert -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/sweetalert/sweetalert.css')}}">
@endsection

@section('page_content')

<!-- Main content -->
<div class="row">
    <div class="col-md-3 settings_bar_gap">
        @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
        <div class="box box-info">
            <div class="box-header with-border text-center">
              <h3 class="box-title">General Settings Form</h3>
            </div>

            <form action="{{ url('admin/settings') }}" method="post" class="form-horizontal" enctype="multipart/form-data" id="general_settings_form">
                {!! csrf_field() !!}

                <!-- box-body -->
        				<div class="box-body">
        					{{-- Name --}}
        					<div class="form-group">
        					  <label class="col-sm-3 control-label" for="inputEmail3">Name</label>
        					  <div class="col-sm-6">
        					    <input type="text" name="name" class="form-control" value="{{ @$result['name'] }}" placeholder="Name">
        					  	<span class="text-danger">{{ $errors->first('name') }}</span>
        					  </div>
        					</div>

                  <!-- Logo -->
        					<div class="form-group">
        					  <label class="col-sm-3 control-label" for="Logo">Logo</label>
        					  <div class="col-sm-6">
        					    <input type="file" name="photos[logo]" id="logo" class="form-control input-file-field" data-rel="{{ isset($result['logo']) ? $result['logo'] : '' }}" value="{{ old('photos[logo]') }}" placeholder="photos[logo]">

        					  	<span class="text-danger">{{ $errors->first('photos[logo]') }}</span>

                   {{--  <div class="col-sm-3">
                      <p><small><b>Recommended size: 282 * 63</b></small></p>
                    </div> --}}

                    @if (isset($result['logo']))
                        <div class="setting-img">
                            <div class="img-wrap-general-logo">
                                <img src='{{ url('public/images/logos/'. $result['logo']) }}'  class="img-responsive">
                            </div>
                            <span class="remove_img_preview_site_logo" id="logo_preview"></span>
                        </div>
                    @else
                      <img src='{{ url('public/uploads/userPic/default-image.png') }}' width="120" height="80" class="img-responsive">
                    @endif
        					  </div>
        					</div>


        					<!-- Favicon -->
        					<div class="form-group">
        					  <label class="col-sm-3 control-label" for="Favicon">Favicon</label>
        					  <div class="col-sm-6">
        					    <input type="file" name="photos[favicon]" id="favicon" class="form-control input-file-field" data-favicon="{{ isset($result['favicon']) ? $result['favicon'] : '' }}" value="{{ old('photos[favicon]') }}" placeholder="photos[favicon]">
        					  	<span class="text-danger">{{ $errors->first('photos[favicon]') }}</span>

                      @if (isset($result['favicon']))
                        <div class="setting-img">
                          <div class="img-wrap-favicon">
                              <img src='{{ url('public/images/logos/'. $result['favicon']) }}' class="img-responsive">
                          </div>
                        	<span class="remove_fav_preview" id="favicon_preview"></span>
                        </div>
                      @else
                      <img src='{{ url('public/uploads/userPic/default-image.png') }}' width="120" height="80" class="img-responsive">
                      @endif
        					  </div>
        					</div>

                  <!-- Head Code -->
                  <div class="form-group">
                    <label for="inputEmail3" class="col-sm-3 control-label">Google Analytics Tracking Code</label>
                    <div class="col-sm-6">
                      <textarea name="head_code" placeholder="Google Analytics Tracking Code" rows="3" class="form-control">{{ @$result['head_code'] }}</textarea>
                      <span class="text-danger">{{ $errors->first('head_code') }}</span>
                    </div>
                  </div>

                  <!-- Google reCAPTCHA -->
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="exampleFormControlInput1">Google reCAPTCHA</label>
                    <div class="col-sm-6">
                      <select class="select2" name="has_captcha" id="has_captcha">
                          <option value='Enabled' {{ $result['has_captcha'] == 'Enabled' ? 'selected':""}}>Enabled</option>
                          <option value='Disabled' {{ $result['has_captcha'] == 'Disabled' ? 'selected':""}}>Disabled</option>
                      </select>
                    </div>
                  </div>

                  <!-- Login Via -->
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="exampleFormControlInput1">Login Via</label>
                    <div class="col-sm-6">
                      <select class="select2" name="login_via" id="login_via">
                          <option value='email_only' {{ $result['login_via'] == 'email_only' ? 'selected':""}}>email only</option>
                          <option value='phone_only' {{ $result['login_via'] == 'phone_only' ? 'selected':""}}>phone only</option>
                          <option value='email_or_phone' {{ $result['login_via'] == 'email_or_phone' ? 'selected':""}}>email or phone</option>
                      </select>
                      <span id="sms-error"></span>
                    </div>
                  </div>

                  <!-- Default Currency -->
                  <div class="form-group">
                    <label for="inputEmail3" class="col-sm-3 control-label">Default Currency</label>
                    <div class="col-sm-6">
                      <select class="select2" name="default_currency">
                          @foreach ($currency as $key => $value)
                            <option value='{{ $key }}' {{ $result['default_currency'] == $key ? 'selected':""}}> {{ $value }}</option>
                          @endforeach
                      </select>
                    </div>
                  </div>


                  <!-- Default Language -->
        					<div class="form-group">
        					  <label for="inputEmail3" class="col-sm-3 control-label">Default Language</label>
        					  <div class="col-sm-6">
        					    <select class="select2" name="default_language">
        					        @foreach ($language as $key => $value)
        					          <option value='{{ $key }}' {{ $result['default_language'] == $key ? 'selected':""}}> {{ $value }}</option>
        					        @endforeach
        					    </select>
        					  </div>
        					</div>
        				</div>
        				<!-- /.box-body -->


        				<!-- box-footer -->
          				@if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_general_setting'))
          					<div class="box-footer">
          		              <button class="btn btn-primary btn-flat pull-right" type="submit">Submit</button>
          		            </div>
          				@endif
  	            <!-- /.box-footer -->


            </form>
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

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    $('#general_settings_form').validate({
        rules: {
            name: {
                required: true,
            },
            "photos[logo]": {
                extension: "png|jpg|jpeg|gif|bmp|ico",
            },
            "photos[favicon]": {
                extension: "png|jpg|jpeg|gif|bmp|ico",
            },
            // head_code: {
            //     required: true,
            // },
        },
        messages: {
          "photos[logo]": {
            extension: "Please select (png, jpg, jpeg, gif, bmp or ico) file!"
          },
          "photos[favicon]": {
            extension: "Please select (png, jpg, jpeg, gif, bmp or ico) file!"
          }
        },
    });


    //Delete logo preview
    $(document).ready(function()
    {
      $('#logo_preview').click(function(){
            var logo = $('#logo').attr('data-rel');

            if(logo)
            {
              $.ajax(
              {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type : "POST",
                url : SITE_URL+"/admin/settings/delete-logo",
                // async : false,
                data: {
                  'logo' : logo,
                },
                dataType : 'json',
                success: function(reply)
                {
                  if (reply.success == 1)
                  {
                    swal({title: "", text: reply.message, type: "success"},
                      function(){
                        location.reload();
                      }
                    );
                  }
                  else{
                      alert(reply.message);
                      location.reload();
                  }
                }
              });
            }
        });
    });


    //Delete favicon preview
    $(document).ready(function()
    {
      $('#favicon_preview').click(function(){
            var favicon = $('#favicon').attr('data-favicon');

            if(favicon)
            {
              $.ajax(
              {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type : "POST",
                url : SITE_URL+"/admin/settings/delete-favicon",
                // async : false,
                data: {
                  'favicon' : favicon,
                },
                dataType : 'json',
                success: function(reply)
                {
                  if (reply.success == 1){
                    // location.reload();
                    swal({title: "", text: reply.message, type: "success"},
                      function(){
                        location.reload();
                      }
                    );
                  }else{
                      alert(reply.message);
                      location.reload();
                  }
                }
              });
            }
        });
    });

    $(document).ready(function()
    {
        $("#login_via").change(function()
        {
            if ($(this).val() == 'email_or_phone' || $(this).val() == 'phone_only')
            {
              $.ajax({
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: SITE_URL+"/admin/settings/check-sms-settings",
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                // Synchronous ( async: false ) – Script stops and waits for the server to send back a reply before continuing.
                // Asynchronous ( async: true ) – Script allows the page to continue to be processed and will handle the reply if and when it arrives.
                //Script stops and waits for the server to send back a reply before continuing
                // async : false,
              })
              .done(function(response)
              {
                  // console.log(response);
                  if (response.status == false)
                  {
                      $('#sms-error').addClass('error').html(response.message).css("font-weight", "bold");
                      $('form').find("button[type='submit']").prop('disabled',true);
                  }
                  else if (response.status == true)
                  {
                      $('#sms-error').html('');

                      $('form').find("button[type='submit']").prop('disabled',false);
                  }
                  // else if (response.status == 404)
                  // {
                  //     $('#sms-error').addClass('error').html(response.message).css("font-weight", "bold");
                  //     $('form').find("button[type='submit']").prop('disabled',true);
                  // }
              });
            }
            else
            {
              $('#sms-error').html('');
              $('form').find("button[type='submit']").prop('disabled',false);
            }
        });
    });
</script>

@endpush


