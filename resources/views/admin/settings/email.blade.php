@extends('admin.layouts.master')
@section('title', 'Email Settings')

@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">Email Settings @if(@$result['status']==1)(<span style="color: green"><i class="fa fa-check" aria-hidden="true"></i>
               Verified</span>) @endif </h3>
                </div>

                <form action="{{ url('admin/settings/email') }}" method="post" class="form-horizontal"
                      id="emai_settings">
                {!! csrf_field() !!}

                <!-- box-body -->
                    <div class="box-body">
                        <!-- driver -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Driver</label>
                            <div class="col-sm-6">
                                {{--<input type="text" name="driver" class="form-control" value="{{ @$result['driver'] }}" placeholder="Driver" id="driver">--}}
                                <select id="driver" name="driver" class="form-control">
                                    <option value="smtp" <?= isset($result['email_protocol']) && $result['email_protocol'] == "smtp" ? "selected" : "" ?> >
                                        SMTP
                                    </option>
                                    <option value="sendmail" <?= isset($result['email_protocol']) && $result['email_protocol'] == "sendmail" ? "selected" : "" ?> >
                                        Send Mail
                                    </option>
                                </select>
                                @if($errors->has('email_protocol'))
                                    <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('email_protocol') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div id="smtpFields" @if($result['email_protocol']=="smtp") style="display: block;" @else style="display: none;" @endif>
                            <!-- host -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Host</label>
                                <div class="col-sm-6">
                                    <input type="text" name="host" class="form-control" value="{{ @$result['smtp_host'] }}"
                                           placeholder="Host" id="host">

                                    @if($errors->has('smtp_host'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_host') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- port -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Port</label>
                                <div class="col-sm-6">
                                    <input type="text" name="port" class="form-control" value="{{ @$result['smtp_port'] }}"
                                           placeholder="Port" id="port">

                                    @if($errors->has('smtp_port'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_port') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- from_address -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">From Address</label>
                                <div class="col-sm-6">
                                    <input type="text" name="from_address" class="form-control"
                                           value="{{ @$result['from_address'] }}" placeholder="From Address"
                                           id="from_address">

                                    @if($errors->has('from_address'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('from_address') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- from_name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">From Name</label>
                                <div class="col-sm-6">
                                    <input type="text" name="from_name" class="form-control"
                                           value="{{ @$result['from_name'] }}" placeholder="From Name" id="from_name">

                                    @if($errors->has('from_name'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('from_name') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- encryption -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Encryption</label>
                                <div class="col-sm-6">
                                    <input type="text" name="encryption" class="form-control"
                                           value="{{ @$result['email_encryption'] }}" placeholder="Encryption"
                                           id="encryption">

                                    @if($errors->has('email_encryption'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('email_encryption') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- username -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Username</label>
                                <div class="col-sm-6">
                                    <input type="text" name="username" class="form-control"
                                           value="{{ @$result['smtp_username'] }}" placeholder="Username" id="username">

                                    @if($errors->has('smtp_username'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_username') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <!-- password -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Password</label>
                                <div class="col-sm-6">
                                    <input type="password" name="password" class="form-control"
                                           value="{{ @$result['smtp_password'] }}" placeholder="Password" id="password">

                                    @if($errors->has('smtp_password'))
                                        <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('smtp_password') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- box-footer -->
                    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_email_setting'))
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

    $('#emai_settings').validate({
        rules: {
            driver: {
                required: true,
            },
            host: {
                required: true,
            },
            port: {
                required: true,
                number: true,
            },
            from_address: {
                required: true,
                email: true,
            },
            from_name: {
                required: true,
            },
            encryption: {
                required: true,
            },
            username: {
                required: true,
            },
            password: {
                required: true,
            },
        },
    });

    $("#driver").on('change',function(e){
        e.preventDefault();
        smtpfield=$("#smtpFields");
        if($(this).val()=="smtp"){
            smtpfield.show();
        }else{
            smtpfield.hide();
        }
    });
</script>

@endpush
