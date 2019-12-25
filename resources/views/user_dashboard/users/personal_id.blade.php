@extends('user_dashboard.layouts.app')

@section('content')
    <!-- personal_id -->
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    <div class="card">
                        <div class="card-header">
                            <div class="chart-list float-left">
                                <ul>
                                    <li><a href="{{url('/profile')}}">@lang('message.dashboard.setting.title')</a></li>
                                    @if ($two_step_verification != 'disabled')
                                        <li><a href="{{url('/profile/2fa')}}">@lang('message.2sa.title-short-text')</a></li>
                                    @endif
                                    <li class="active"><a href="{{url('/profile/personal-id')}}">@lang('message.personal-id.title')
                                        @if( !empty(getAuthUserIdentity()) && getAuthUserIdentity()->status == 'approved' )(<span style="color: green"><i class="fa fa-check" aria-hidden="true"></i>Verified</span>) @endif
                                        </a>
                                    </li>
                                    <li><a href="{{url('/profile/personal-address')}}">@lang('message.personal-address.title')
                                        @if( !empty(getAuthUserAddress()) && getAuthUserAddress()->status == 'approved' )(<span style="color: green"><i class="fa fa-check" aria-hidden="true"></i>Verified</span>) @endif
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- form -->
                                    <form action="{{ url('profile/personal-id-update') }}" method="POST" class="form-horizontal" id="personal_id" enctype="multipart/form-data">
                                        {{ csrf_field() }}

                                        <input type="hidden" value="{{$user->id}}" name="user_id" id="user_id" />

                                        <input type="hidden" value="{{ isset($documentVerification->file_id) ? $documentVerification->file_id : '' }}" name="existingIdentityFileID" id="existingIdentityFileID" />

                                        <div class="row">
                                            <div class="form-group col-md-5">
                                                <label for="identity_type">@lang('message.personal-id.identity-type')</label>
                                                <select name="identity_type" id="identity_type" class="form-control">
                                                    <option value="">@lang('message.personal-id.select-type')</option>
                                                    <option value="driving_license"
                                                    {{ !empty($documentVerification->identity_type) && $documentVerification->identity_type == 'driving_license' ? 'selected' : '' }}>
                                                        @lang('message.personal-id.driving-license')
                                                    </option>
                                                    <option value="passport" {{ !empty($documentVerification->identity_type) && $documentVerification->identity_type == 'passport' ? 'selected' : '' }}>@lang('message.personal-id.passport')</option>
                                                    <option value="national_id" {{ !empty($documentVerification->identity_type) && $documentVerification->identity_type == 'national_id' ? 'selected' : '' }}>@lang('message.personal-id.national-id')</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>

                                        <div class="row">
                                            <div class="form-group col-md-5">
                                                <label for="identity_number">@lang('message.personal-id.identity-number')</label>
                                                <input type="number" name="identity_number" class="form-control" value="{{ !empty($documentVerification->identity_number) ? $documentVerification->identity_number : '' }}">
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>

                                        <div class="row">
                                            <div class="form-group col-md-5">
                                                <label for="identity_file">@lang('message.personal-id.upload-identity-proof')</label>
                                                <input type="file" name="identity_file" class="form-control input-file-field">
                                            </div>
                                        </div>

                                        @if (!empty($documentVerification->file))
                                            <h5>
                                                <a class="text-info" href="{{ url('public/uploads/user-documents/identity-proof-files').'/'.$documentVerification->file->filename }}"><i class="fa fa-download"></i>
                                                    {{ $documentVerification->file->filename }}
                                                </a>
                                            </h5>
                                            <br>
                                        @endif

                                        <div class="row">
                                            <div class="form-group col-md-5">
                                                <button type="submit" class="btn btn-cust col-12" id="personal_id_submit">
                                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="personal_id_submit_text">@lang('message.dashboard.button.submit')</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /form -->
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">

    jQuery.extend(jQuery.validator.messages, {
        required: "{{__('This field is required.')}}",
        number: "{{__("Please enter a valid number.")}}",
    })

    $('#personal_id').validate({
        rules: {
            identity_type: {
                required: true,
            },
            identity_number: {
                required: true,
                number: true,
            },
            identity_file: {
                required: true,
                extension: "docx|rtf|doc|pdf|png|jpg|jpeg|csv|txt|gif|bmp",
            },
        },
        messages: {
          identity_file: {
            extension: "{{__("Please select (docx, rtf, doc, pdf, png, jpg, jpeg, csv, txt, gif or bmp) file!")}}"
          }
        },
        submitHandler: function(form)
        {
            $("#personal_id_submit").attr("disabled", true);
            $(".spinner").show();
            $("#personal_id_submit_text").text('Submitting...');
            form.submit();
        }
    });
</script>
@endsection
