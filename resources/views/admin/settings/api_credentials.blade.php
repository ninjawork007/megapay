@extends('admin.layouts.master')
@section('title', 'API Credentials')

@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                  <h3 class="box-title">API Credentials</h3>
                </div>

                <form action="{{ url('admin/settings/api_informations') }}" method="post" class="form-horizontal" id="api-credentials" >
                    {!! csrf_field() !!}

                    <!-- box-body -->
                    <div class="box-body">

                        <!-- Google Captcha Secret key -->
                        <div class="form-group">
                          <label class="col-sm-4 control-label" for="inputEmail3">Google ReCaptcha Secret key</label>
                          <div class="col-sm-6">
                            <input type="text" name="captcha_secret_key" class="form-control" value="{{ $recaptcha['secret_key'] or '' }}" placeholder="captcha secret key">

                            @if($errors->has('captcha_secret_key'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('captcha_secret_key') }}</strong>
                                </span>
                            @endif
                          </div>
                        </div>

                        <!-- Google Captcha Site key -->
                        <div class="form-group">
                          <label class="col-sm-4 control-label" for="inputEmail3">Google ReCaptcha Site key</label>
                          <div class="col-sm-6">
                            <input type="text" name="captcha_site_key" class="form-control" value="{{ $recaptcha['site_key'] or '' }}" placeholder="captcha site key">

                            @if($errors->has('captcha_site_key'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('captcha_site_key') }}</strong>
                                </span>
                            @endif
                          </div>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <!-- box-footer -->
                    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_api_credentials'))
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

$('#api-credentials').validate({
    rules: {
        captcha_secret_key: {
            required: true,
        },
        captcha_site_key: {
            required: true,
        },
    },
});

</script>

@endpush
