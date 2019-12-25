@extends('user_dashboard.layouts.app')

@section('content')
    <!-- personal_address -->
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

                                    <li><a href="{{url('/profile/personal-id')}}">@lang('message.personal-id.title')
                                        @if( !empty(getAuthUserIdentity()) && getAuthUserIdentity()->status == 'approved' )(<span style="color: green"><i class="fa fa-check" aria-hidden="true"></i>Verified</span>) @endif
                                        </a>
                                    </li>
                                    <li class="active"><a href="{{url('/profile/personal-address')}}">@lang('message.personal-address.title')
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
                                    <form action="{{ url('profile/personal-address-update') }}" method="POST" class="form-horizontal" id="personal_address" enctype="multipart/form-data">
                                        {{ csrf_field() }}

                                        <input type="hidden" value="{{$user->id}}" name="user_id" id="user_id" />

                                        <input type="hidden" value="{{ isset($documentVerification->file_id) ? $documentVerification->file_id : '' }}" name="existingAddressFileID" id="existingAddressFileID" />

                                        <div class="row">
                                            <div class="form-group col-md-5">
                                                <label for="address_file">@lang('message.personal-address.upload-address-proof')</label>
                                                <input type="file" name="address_file" class="form-control input-file-field">
                                            </div>
                                        </div>

                                        @if (!empty($documentVerification->file))
                                            <h5>
                                                <a class="text-info" href="{{ url('public/uploads/user-documents/address-proof-files').'/'.$documentVerification->file->filename }}"><i class="fa fa-download"></i>
                                                    {{ $documentVerification->file->filename }}
                                                </a>
                                            </h5>
                                            <br>
                                        @endif
                                        <div class="clearfix"></div>

                                        <div class="row">
                                            <div class="form-group col-md-5">
                                                <button type="submit" class="btn btn-cust col-12" id="personal_address_submit">
                                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="personal_address_submit_text">@lang('message.dashboard.button.submit')</span>
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
})

$('#personal_address').validate({
    rules: {
        address_file: {
            required: true,
            extension: "docx|rtf|doc|pdf|png|jpg|jpeg|csv|txt|gif|bmp",
        },
    },
    messages: {
      address_file: {
        extension: "{{__("Please select (docx, rtf, doc, pdf, png, jpg, jpeg, csv, txt, gif or bmp) file!")}}"
      }
    },
    submitHandler: function(form)
    {
        $("#personal_address_submit").attr("disabled", true);
        $(".spinner").show();
        $("#personal_address_submit_text").text('Submitting...');
        form.submit();
    }
});

</script>
@endsection
