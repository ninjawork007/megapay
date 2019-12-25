@extends('admin.layouts.master')
@section('title', 'API Credentials Settings')

@section('head_style')
  <!-- custom-checkbox -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/custom-checkbox.css') }}">
@endsection

@section('page_content')
    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>

        <div class="col-md-9">
            <div class="box box-info">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabs">
                      <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="false">Google Play Store</a></li>
                      <li><a href="#tab_2" data-toggle="tab" aria-expanded="false">Apple Store</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab_1">
                            <form action="{{ url('admin/settings/app-store-credentials/update-google-credentials') }}" method="POST" class="form-horizontal" id="app-store-google-credentials" enctype="multipart/form-data" >
                                {!! csrf_field() !!}

                                <input type="hidden" name="playstoreid" id="playstoreid" value="{{ isset($appStoreCredentialsForGoogle) ? $appStoreCredentialsForGoogle->id : '' }}">
                                <input type="hidden" name="playstorecompany" id="playstorecompany" value="{{ isset($appStoreCredentialsForGoogle) ? $appStoreCredentialsForGoogle->company : '' }}">

                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="has_transaction">Available</label>
                                        <div class="col-sm-6">
                                            <label class="checkbox-container">
                                              <input type="checkbox" class="has_app_playstore_credentials" name="has_app_playstore_credentials" value="Yes" id="has_app_playstore_credentials"
                                              {{ isset($appStoreCredentialsForGoogle->has_app_credentials) && $appStoreCredentialsForGoogle->has_app_credentials == 'Yes' ? 'checked' : '' }}
                                              >
                                              <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- PlayStore Link -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="playstore[link]">Play Store Link</label>
                                        <div class="col-sm-5">
                                            <input class="form-control playstore-link" name="playstore[link]" type="text"
                                            value="{{ isset($appStoreCredentialsForGoogle->link) ? $appStoreCredentialsForGoogle->link : '' }}"
                                            id="playstore-link">
                                            @if ($errors->has('playstore[link]'))
                                                  <span class="error">
                                                      <strong>{{ $errors->first('playstore[link]') }}</strong>
                                                  </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- Play Store Logo -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="playstore[logo]">Play Store Logo</label>
                                        <div class="col-sm-5">
                                           <input type="file" name="playstore[logo]" class="form-control input-file-field" id="playstore-logo"
                                            data-rel="{{ isset($appStoreCredentialsForGoogle->logo) ? $appStoreCredentialsForGoogle->logo : '' }}" value="{{ isset($appStoreCredentialsForGoogle->logo) ? $appStoreCredentialsForGoogle->logo : '' }}">

                                            @if ($errors->has('playstore[logo]'))
                                                <span class="error">
                                                    <strong>{{ $errors->first('playstore[logo]') }}</strong>
                                                </span>
                                            @endif

                                            @if (isset($appStoreCredentialsForGoogle->logo))
                                            <div class="setting-img">
                                                <div class="img-wrap">
                                                <img src='{{ url('public/uploads/app-store-logos/'.$appStoreCredentialsForGoogle->logo) }}'  class="img-responsive">
                                                </div>
                                                {{-- <span class="remove_img_preview" id="playstore-logo-preview"></span> --}}
                                            </div>

                                            {{-- <img src='{{ url('public/uploads/app-store-logos/'.$appStoreCredentialsForGoogle->logo) }}'  class="img-responsive"> --}}

                                            @else
                                                <img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' width="120" height="80" class="img-responsive">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="row">
                                  <div class="col-md-12">
                                      <div style="margin-top:10px">
                                        <a href="{{ url('admin/settings/app-store-credentials') }}" class="btn btn-danger btn-flat">Cancel</a>
                                        @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_appstore_credentials'))
                                            <button class="btn btn-primary pull-right btn-flat" type="submit">Sumbit</button>
                                        @endif
                                      </div>
                                  </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="tab_2">
                            <form action="{{ url('admin/settings/app-store-credentials/update-apple-credentials') }}" method="POST" class="form-horizontal" id="app-store-apple-credentials" enctype="multipart/form-data">
                                {!! csrf_field() !!}

                                <input type="hidden" name="appstoreid" id="appstoreid" value="{{ isset($appStoreCredentialsForApple) ? $appStoreCredentialsForApple->id : '' }}">
                                <input type="hidden" name="appstorecompany" id="appstorecompany" value="{{ isset($appStoreCredentialsForApple) ? $appStoreCredentialsForApple->company : '' }}">

                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="has_transaction">Available</label>
                                        <div class="col-sm-6">
                                            <label class="checkbox-container">
                                              <input type="checkbox" class="has_app_appstore_credentials" name="has_app_appstore_credentials" value="Yes" id="has_app_appstore_credentials" {{ isset($appStoreCredentialsForApple->has_app_credentials) && $appStoreCredentialsForApple->has_app_credentials == 'Yes' ? 'checked' : '' }}>
                                              <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- Apple Store Link -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="applestore[link]">Apple Store Link</label>
                                        <div class="col-sm-5">
                                            <input class="form-control applestore-link" name="applestore[link]" type="text"
                                            value="{{ isset($appStoreCredentialsForApple->link) ? $appStoreCredentialsForApple->link : '' }}"
                                            id="applestore-link">
                                            @if ($errors->has('applestore[link]'))
                                                  <span class="error">
                                                      <strong>{{ $errors->first('applestore[link]') }}</strong>
                                                  </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                    <!-- Maximum Limit -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label" for="applestore[logo]">Apple Store Logo</label>
                                        <div class="col-sm-5">
                                          <input type="file" name="applestore[logo]" class="form-control input-file-field" id="applestore-logo"
                                            data-rel="{{ isset($appStoreCredentialsForApple->logo) ? $appStoreCredentialsForApple->logo : '' }}" value="{{ isset($appStoreCredentialsForApple->logo) ? $appStoreCredentialsForApple->logo : '' }}">

                                            @if ($errors->has('applestore[logo]'))
                                                <span class="error">
                                                    <strong>{{ $errors->first('applestore[logo]') }}</strong>
                                                </span>
                                            @endif

                                            @if (isset($appStoreCredentialsForApple->logo))
                                                <div class="setting-img">
                                                    <div class="img-wrap">
                                                    <img src='{{ url('public/uploads/app-store-logos/'.$appStoreCredentialsForApple->logo) }}'  class="img-responsive">
                                                    </div>
                                                    {{-- <span class="remove_img_preview" id="applestore-logo-preview"></span> --}}
                                                </div>
                                            @else
                                                <img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' width="120" height="80" class="img-responsive">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="row">
                                  <div class="col-md-12">
                                      <div style="margin-top:10px">
                                        <a href="{{ url('admin/settings/app-store-credentials') }}" class="btn btn-danger btn-flat">Cancel</a>

                                        @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_appstore_credentials'))
                                            <button class="btn btn-primary pull-right btn-flat" type="submit">Sumbit</button>
                                        @endif
                                      </div>
                                  </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- /.tab-content -->
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

<script type="text/javascript">

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

    $('#app-store-google-credentials').validate({
        rules: {
            "playstore[link]": {
                required: true,
                url: true,
            },
            "playstore[logo]":{
                required: true,
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          "playstore[logo]": {
            extension: "Please select images(png|jpg|jpeg|gif|bmp) only!"
          },
        },
    });

    $('#app-store-apple-credentials').validate({
        rules: {
            "applestore[link]": {
                required: true,
                url: true,
            },
            "applestore[logo]":{
                required: true,
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          "applestore[logo]": {
            extension: "Please select images(png|jpg|jpeg|gif|bmp) only!"
          },
        },
    });

    //Delete playstoreLogo logo preview
    $(document).ready(function()
    {
        $('#playstore-logo-preview').click(function(){
            var playstoreLogo = $('#playstore-logo').attr('data-rel');
            var playstorecompany = $('#playstorecompany').val();

            if(playstoreLogo)
            {
              $.ajax(
              {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type : "POST",
                url : SITE_URL+"/admin/settings/app-store-credentials/delete-playstore-logo",
                async : false,
                data: {
                  'playstoreLogo' : playstoreLogo,
                  'playstorecompany' : playstorecompany,
                },
                dataType : 'json',
                success: function(reply)
                {
                  if (reply.success == 1)
                  {
                    swal({title: "", text: reply.message, type: "success"},function(){
                        location.reload();
                    });
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

    //Delete applestore logo preview
    $(document).ready(function()
    {
        $('#applestore-logo-preview').click(function(){
            var appleStoreLogo = $('#applestore-logo').attr('data-rel');
            var appstorecompany = $('#appstorecompany').val();

            if(appleStoreLogo)
            {
              $.ajax(
              {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type : "POST",
                url : SITE_URL+"/admin/settings/app-store-credentials/delete-appstore-logo",
                async : false,
                data: {
                  'appleStoreLogo' : appleStoreLogo,
                  'appstorecompany' : appstorecompany,
                },
                dataType : 'json',
                success: function(reply)
                {
                  if (reply.success == 1){
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
</script>

@endpush
