@extends('admin.layouts.master')
@section('title', 'SMS Settings')

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
                  <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="false">Nexmo</a></li>
                  {{-- <li><a href="#tab_2" data-toggle="tab" aria-expanded="false">Twilio</a></li> --}}
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade in active" id="tab_1">
                        <form action="{{ url('admin/settings/sms') }}" method="POST" class="form-horizontal" id="nexmo_form" enctype="multipart/form-data" >
                            {!! csrf_field() !!}

                            <div class="box-body">

                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="exampleFormControlInput1">Default</label>
                                    <div class="col-sm-5">
                                        <select class="select2" name="is_nexmo_default" id="is_nexmo_default">
                                          <option value='Yes' {{ $result['is_nexmo_default'] == 'Yes' ? 'selected':""}}>Yes</option>
                                          <option value='No' {{ $result['is_nexmo_default'] == 'No' ? 'selected':""}}>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="clearfix"></div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="nexmo_status">Status</label>
                                    <div class="col-sm-5">
                                        <select class="select2" name="nexmo_status" id="nexmo_status">
                                          <option value='Active' {{ $result['nexmo_status'] == 'Active' ? 'selected':""}}>Active</option>
                                          <option value='Inactive' {{ $result['nexmo_status'] == 'Inactive' ? 'selected':""}}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="clearfix"></div>

                                <!-- Nexmo Key -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="default_nexmo_phone_number">Default Number</label>
                                    <div class="col-sm-5">
                                        <input class="form-control playstore-link" name="default_nexmo_phone_number" type="text"
                                        value="{{ $result['default_nexmo_phone_number'] }}"
                                        id="default_nexmo_phone_number">
                                        @if ($errors->has('default_nexmo_phone_number'))
                                              <span class="error">
                                                  <strong>{{ $errors->first('default_nexmo_phone_number') }}</strong>
                                              </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="clearfix"></div>

                                <!-- Nexmo Key -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="nexmo_key">Key</label>
                                    <div class="col-sm-5">
                                        <input class="form-control playstore-link" name="nexmo_key" type="text"
                                        value="{{ $result['Key'] }}"
                                        id="nexmo_key">
                                        @if ($errors->has('nexmo_key'))
                                              <span class="error">
                                                  <strong>{{ $errors->first('nexmo_key') }}</strong>
                                              </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="clearfix"></div>


                                <!-- Nexmo Secret -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="nexmo_secret">Secret</label>
                                    <div class="col-sm-5">
                                        <input class="form-control playstore-link" name="nexmo_secret" type="text"
                                        value="{{ $result['Secret'] }}"
                                        id="nexmo_secret">
                                        @if ($errors->has('nexmo_secret'))
                                              <span class="error">
                                                  <strong>{{ $errors->first('nexmo_secret') }}</strong>
                                              </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="row">
                              <div class="col-md-12">
                                  <div style="margin-top:10px">
                                    <a href="{{ url('admin/settings/sms') }}" class="btn btn-danger btn-flat">Cancel</a>
                                    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_sms_setting'))
                                        <button class="btn btn-primary pull-right btn-flat" type="submit">Sumbit</button>
                                    @endif
                                  </div>
                              </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane" id="tab_2">
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

    $('#nexmo_form').validate({
        rules: {
            default_nexmo_phone_number: {
                required: true,
                number: true,
            },
            nexmo_secret: {
                required: true,
            },
            nexmo_key: {
                required: true,
            },
        },
    });
</script>

@endpush
