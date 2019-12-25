@extends('user_dashboard.layouts.app')

@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h4 class="float-left">@lang('message.dashboard.setting.title')</h4>
                        </div>
                        {{-- <div class="card-body" style="overflow:hidden"> --}}
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-2">
                                            @if(!empty(Auth::user()->picture))
                                                <img src="{{url('public/user_dashboard/profile/'.Auth::user()->picture)}}"
                                                     class="rounded-circle rounded-circle-custom-trans"
                                                     id="profileImage">
                                            @else
                                                <img src="{{url('public/user_dashboard/images/avatar.jpg')}}"
                                                     class="rounded-circle rounded-circle-custom-trans"
                                                     id="profileImage">

                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h4>@lang('message.dashboard.setting.change-avatar')</h4>
                                            <p>@lang('message.dashboard.setting.change-avatar-here')</p>

                                            <input type="file" id="file" style="display: none"/>
                                            <input type="hidden" id="file_name"/>

                                        </div>
                                        <div class="col-md-4">

                                            <div class="uploadAvatar">
                                                <a href="javascript:changeProfile()" id="changePicture"
                                                   class="btn btn-secondary btn-border btn-sm"
                                                   style="margin-top: 10px;">
                                                    <i class="fa fa-camera" aria-hidden="true"></i>
                                                    &nbsp; @lang('message.dashboard.button.change-picture')
                                                </a>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4>@lang('message.dashboard.setting.change-password')</h4>
                                            <p>@lang('message.dashboard.setting.change-password-here')</p>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-cust" data-toggle="modal"
                                                    data-target="#myModal">
                                                @lang('message.dashboard.button.change-password')
                                            </button>

                                            <!-- The Modal -->
                                            <div class="modal" id="myModal">
                                                <div class="modal-dialog">
                                                    <form method="post" action="{{url('prifile/update_password')}}" id="reset_password">
                                                        {{ csrf_field() }}

                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">@lang('message.dashboard.setting.change-password')</h4>
                                                                <button type="button" class="close"
                                                                        data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">

                                                                <div class="form-group">
                                                                    <label>@lang('message.dashboard.setting.old-password')</label>
                                                                    <input class="form-control" name="old_password"
                                                                           id="old_password" type="password">
                                                                    @if($errors->has('old_password'))
                                                                        <span class="error">
                                                                         {{ $errors->first('old_password') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <div class="clearfix"></div>

                                                                <div class="form-group">
                                                                    <label>@lang('message.dashboard.setting.new-password')</label>
                                                                    <input class="form-control" name="password"
                                                                           id="password" type="password">
                                                                    @if($errors->has('password'))
                                                                        <span class="error">
                                                                         {{ $errors->first('password') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <div class="clearfix"></div>
                                                                <div class="form-group">
                                                                    <label>@lang('message.dashboard.setting.confirm-password')</label>
                                                                    <input class="form-control" name="confirm_password"
                                                                           id="confirm_password" type="password">
                                                                    @if($errors->has('confirm_password'))
                                                                        <span class="error">
                                                                         {{ $errors->first('confirm_password') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Modal footer -->
                                                            <div class="modal-footer">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <button type="button" class="btn btn-cust" data-dismiss="modal">@lang('message.form.close')</button>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <button type="submit" class="btn btn-cust">@lang('message.dashboard.button.submit')</button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            @if (empty($user->phone))

                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="row">
                                            <div class="col-md-2">
                                                <img src="{{ url('public/user_dashboard/images/phone-icon.png') }}" class="rounded-circle rounded-circle-custom-trans">
                                            </div>
                                            <div class="col-md-6">
                                                <h4 class="addPhoneTitle">Add Phone</h4>
                                                <p class="addPhoneBody">Click on <b>+</b> to add phone</p>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="uploadAvatar">
                                                    <button type="button" class="btn btn-secondary btn-border btn-sm add" data-toggle="modal" data-target="#add" style="margin-top: 10px;">
                                                        <i class="fa fa-plus" id="modalTextSymbol"></i>
                                                        <span class="modalText">&nbsp; Add phone</span>
                                                    </button>
                                                </div>

                                                <!-- Add Phone Modal -->
                                                <div class="modal" id="add">
                                                    <div class="modal-dialog">

                                                        <form method="POST" action="{{ url('profile/complete-phone-verification')}}" id="complete-phone-verification">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" value="{{ $is_sms_env_enabled }}" name="is_sms_env_enabled" id="is_sms_env_enabled" />
                                                            <input type="hidden" value="{{ $checkPhoneVerification }}" name="checkPhoneVerification" id="checkPhoneVerification" />
                                                            <input type="hidden" value="{{ $user->id }}" name="user_id" id="user_id" />
                                                            <input type="hidden" name="hasVerificationCode" id="hasVerificationCode" />


                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">Add Phone</h4>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>

                                                                <div class="modal-body">

                                                                    <div class="alert text-center" id="message" style="display: none"></div>

                                                                    <div class="form-group">
                                                                        <label id="subheader_text">Enter the number you’d like to use</label>
                                                                        <br>
                                                                        <div class="input-phone">
                                                                        </div>
                                                                        <span id="phone-error"></span>
                                                                    </div>
                                                                    <div class="clearfix"></div>

                                                                    <div class="form-group">
                                                                        <label></label>
                                                                        <input id="phone_verification_code" type="text" maxlength="6" class="form-control" name="phone_verification_code" style="display: none;">
                                                                    </div>
                                                                    <div class="clearfix"></div>

                                                                    <div class="row">
                                                                        <div class="col-md-5">
                                                                            <div style="margin-top: 6px;">
                                                                                <span id="static_phone_show" class="static_phone_show" style="display: none;"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <button type="button" class="btn btn-sm btn-cust edit" style="display: none;"><i class="fa fa-edit"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Modal footer -->
                                                                <div class="modal-footer">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <button type="button" class="btn btn-cust" data-dismiss="modal" id="close">Cancel</button>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <button type="button" class="btn btn-cust next" id="common_button">Next</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>

                            @else
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <img src="{{ url('public/user_dashboard/images/phone-icon.png') }}" class="rounded-circle rounded-circle-custom-trans">
                                            </div>
                                            <div class="col-md-4">
                                                <h4 class="editPhoneTitle">Phone Number</h4>
                                                <p class="editPhoneBody">{{ auth()->user()->phone }}</p>
                                            </div>
                                            <div class="col-md-2 delete_col1">
                                                <div class="uploadAvatar">
                                                    <button type="button" class="btn btn-secondary btn-border btn-sm editModal" data-toggle="modal" data-target="#editModal" style="margin-top: 10px;">
                                                        <i class="fa fa-edit"></i>
                                                        <span>&nbsp; Edit phone</span>
                                                    </button>

                                                </div>
                                                <!-- The Modal -->
                                                <div class="modal" id="editModal">
                                                    <div class="modal-dialog">

                                                        <form method="POST" action="{{ url('profile/update-phone-number')}}" id="update-phone-number">
                                                            {{ csrf_field() }}
                                                            <input type="hidden" value="{{ $is_sms_env_enabled }}" name="is_sms_env_enabled" id="is_sms_env_enabled">
                                                            <input type="hidden" value="{{ $user->id }}" name="user_id" id="user_id">

                                                            <input type="hidden" value="{{ $checkPhoneVerification }}" name="editCheckPhoneVerification" id="editCheckPhoneVerification" />
                                                            <input type="hidden" name="editHasVerificationCode" id="editHasVerificationCode" />


                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">Edit Phone</h4>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>

                                                                <div class="modal-body editModalBody">
                                                                    <div class="alert text-center" id="message" style="display: none"></div>

                                                                    <div class="form-group">
                                                                        <label id="subheader_edit_text">Enter the number you’d like to use</label>
                                                                        <br>
                                                                        <div class="input-phone">
                                                                        </div>
                                                                        <span id="edit-phone-error"></span>
                                                                    </div>
                                                                    <div class="clearfix"></div>

                                                                    <div class="form-group">
                                                                        <label></label>
                                                                        <input id="edit_phone_verification_code" type="text" maxlength="6" class="form-control" name="edit_phone_verification_code" style="display: none;">
                                                                    </div>
                                                                    <div class="clearfix"></div>

                                                                    <div class="row">
                                                                        <div class="col-md-5">
                                                                            <div style="margin-top: 6px;">
                                                                                <span id="edit_static_phone_show" class="edit_static_phone_show" style="display: none;"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2">
                                                                            <button type="button" class="btn btn-sm btn-cust edit_button_edit" style="display: none;"><i class="fa fa-edit"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Modal footer -->
                                                                <div class="modal-footer">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <button type="button" class="btn btn-cust" data-dismiss="modal" id="close">Cancel</button>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            @if ($is_sms_env_enabled == false && $checkPhoneVerification == "Disabled")
                                                                                <button type="button" class="btn btn-cust edit_form_submit" id="common_button_update">Update</button>
                                                                            @else
                                                                                <button type="button" class="btn btn-cust update" id="common_button_update">Next</button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-1 extra_col"></div>

                                            <div class="col-md-2 delete_col2">
                                                <div class="uploadAvatar">
                                                    <button type="button" class="btn btn-secondary btn-border btn-sm delete" style="margin-top: 10px;">
                                                        <i class="fa fa-trash"></i>
                                                        <span>&nbsp; Delete phone</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>

                            @endif

                            <div class="clearfix"></div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h4>@lang('message.dashboard.setting.profile-information')</h4>
                                    <hr>
                                        <form method="post" action="{{url('prifile/update')}}" id="profile_update_form">
                                            {{csrf_field()}}

                                            <input type="hidden" data-phone="{{ $user->phone }}" value="{{ $user->phone }}" name="user_original_phone" id="user_original_phone" />

                                            <input type="hidden" value="{{$user->id}}" name="id" id="id" />

                                            <input type="hidden" value="{{ $user->defaultCountry }}" name="user_defaultCountry" id="user_defaultCountry" />

                                            <input type="hidden" value="{{ $user->carrierCode }}" name="user_carrierCode" id="user_carrierCode" />

    										<div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="first_name">@lang('message.dashboard.setting.first-name')
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="first_name" id="first_name"
                                                           value="{{ $user->first_name }}">
                                                    @if($errors->has('first_name'))
                                                        <span class="error">
                                                           {{ $errors->first('first_name') }}
                                                          </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="last_name">@lang('message.dashboard.setting.last-name')
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="last_name" id="last_name"
                                                           value="{{ $user->last_name }}">
                                                    @if($errors->has('last_name'))
                                                        <span class="error">
                                                           {{ $errors->first('last_name') }}
                                                          </span>
                                                    @endif
                                                </div>
    										</div>
                                            <div class="clearfix"></div>

    										<div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="email">@lang('message.dashboard.setting.email')
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" id="email" class="form-control" value="{{ $user->email }}" readonly>
                                                </div>
    										</div>
                                            <div class="clearfix"></div>

                                        {{-- others --}}

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="address_1">@lang('message.dashboard.setting.address1')</label>
                                                    <textarea class="form-control" name="address_1"
                                                              id="address_1">{{ $user->user_detail->address_1 }}</textarea>
                                                    @if($errors->has('address_1'))
                                                        <span class="error">
                                                           {{ $errors->first('address_1') }}
                                                          </span>
                                                    @endif
                                                </div>
    										</div>
                                            <div class="clearfix"></div>


    										<div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="address_2">@lang('message.dashboard.setting.address2')</label>
                                                    <textarea class="form-control" name="address_2"
                                                              id="address_2">{{ $user->user_detail->address_2 }}</textarea>
                                                    @if($errors->has('address_2'))
                                                        <span class="error">
                                                           {{ $errors->first('address_2') }}
                                                          </span>
                                                    @endif
                                                </div>
    										</div>
                                            <div class="clearfix"></div>

    										<div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="city">@lang('message.dashboard.setting.city')</label>

                                                    <input type="text" class="form-control" name="city" id="city"
                                                           value="{{ $user->user_detail->city }}">
                                                    @if($errors->has('city'))
                                                        <span class="error">
                                                           {{ $errors->first('city') }}
                                                          </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="state">@lang('message.dashboard.setting.state')</label>
                                                    <input type="text" class="form-control" name="state" id="state"
                                                           value="{{ $user->user_detail->state }}">
                                                    @if($errors->has('state'))
                                                        <span class="error">
                                                           {{ $errors->first('state') }}
                                                          </span>
                                                    @endif
                                                </div>
    										</div>
                                            <div class="clearfix"></div>

    										<div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="country_id">@lang('message.dashboard.setting.country')</label>
                                                    <select class="form-control" name="country_id" id="country_id">
                                                        @foreach($countries as $country)
                                                            <option value="{{$country->id}}" <?= ($user->user_detail->country_id == $country->id) ? 'selected' : '' ?> >{{$country->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($errors->has('country_id'))
                                                        <span class="error">
                                                           {{ $errors->first('country_id') }}
                                                          </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="timezone">@lang('message.dashboard.setting.timezone')</label>

                                                    <select class="form-control" name="timezone" id="timezone">
                                                        @foreach($timezones as $timezone)
                                                          <option value="{{ $timezone['zone'] }}" {{ ($user->user_detail->timezone == $timezone['zone']) ? 'selected' : '' }}>
                                                            {{ $timezone['diff_from_GMT'] . ' - ' . $timezone['zone'] }}
                                                          </option>
                                                        @endforeach
                                                    </select>

                                                    @if($errors->has('timezone'))
                                                        <span class="error">
                                                           {{ $errors->first('timezone') }}
                                                          </span>
                                                    @endif
                                                </div>
    										</div>
                                            <div class="clearfix"></div>
    										<br />

    										<div class="row">
                                                <div class="form-group col-md-6">
                                                    <button type="submit" class="btn btn-cust col-12" id="users_profile">
                                                        <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="users_profile_text">@lang('message.dashboard.button.submit')</span>
                                                    </button>
                                                </div>
    										</div>
                                        </form>
                                </div>
                            </div>
                            <hr>
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

    <script>

         jQuery.extend(jQuery.validator.messages, {
            required: "{{__('This field is required.')}}",
            minlength: $.validator.format( "{{__("Please enter at least")}}"+" {0} "+"{{__("characters.")}}" ),
        })

        $(document).ready(function() {
            $('.flag').css('margin-top', '3px');
        });

        //Add

            //reload on close of phone add modal
            $('#add').on('hidden.bs.modal', function () {
                window.location.reload();
            });

            //is_sms_env_enabled check
            $(document).ready(function()
            {
                var is_sms_env_enabled = $('#is_sms_env_enabled').val();
                var checkPhoneVerification = $('#checkPhoneVerification').val();

                if (is_sms_env_enabled == false && checkPhoneVerification == "Disabled")
                {
                    $('.next').removeClass("next").addClass('form_submit').html('Submit');
                }
            });

            // get data form users
            $(document).ready(function()
            {
                var country = $('.country'),
                btn_flag = $('.btn-flag'),
                btn_cc = $('.btn-cc'),
                defaultCountry = $('#defaultCountry'),
                carrierCode = $('#carrierCode');

                var user_carrierCode = $('#user_carrierCode').val();
                btn_cc.html('&nbsp;&nbsp;+'+user_carrierCode+'&nbsp;&nbsp;');
                carrierCode.val(user_carrierCode);

                var user_defaultCountry = $('#user_defaultCountry').val();
                btn_flag.attr('class', 'flag '+user_defaultCountry);
                defaultCountry.val(user_defaultCountry);

                var phoneNumber = $('#phoneNumber');
                var user_original_phone = $('#user_original_phone').data('phone');
                phoneNumber.html(user_original_phone);
                phoneNumber.val(user_original_phone);
            });


            $(document).on('click', '.next', function()
            {
                var phoneNumber = $("input[name=phoneNumber]").val();
                // alert(phoneNumber.length);

                if (phoneNumber == '')
                {
                    $('#phone-error').addClass('error').html('This field is required.').css({
                       'color' : 'red !important',
                       'font-size' : '14px',
                       'font-weight' : '800',
                       'padding-top' : '5px',
                    });
                    $('#phoneNumber-error').hide();
                    return false;
                }
                else if (isNaN(phoneNumber))
                {
                    $('#phone-error').addClass('error').html('Please enter numbers only!').css({
                       'color' : 'red !important',
                       'font-size' : '14px',
                       'font-weight' : '800',
                       'padding-top' : '5px',
                    });
                    $('#phoneNumber-error').hide();
                    return false;
                }
                else
                {


                    $('#subheader_text').html('To make sure this number is yours, Google will send you a verification code.');

                    $('.input-phone').hide();

                    $('#static_phone_show').show();

                    $('.edit').show();

                    $(this).removeClass("next").addClass("get_code").html('Get Code');

                    $('#static_phone_show').html('+' + $('#carrierCode').val() + $('#phoneNumber').val() + '&nbsp;&nbsp;');
                    return true;
                }
            });

            //onclick - edit
            $(document).on('click', '.edit', function()
            {
                $('.get_code').removeClass("get_code").addClass("next").html('Next');

                $('.static_phone_show').html('');
                $(this).hide();

                $('#subheader_text').html('Enter the number you’d like to use');
                $('.input-phone').show();

                $('.btn-flag').attr('class', 'flag '+ $('#defaultCountry').val());

                $('.btn-cc').html('&nbsp;&nbsp;+'+ $('#carrierCode').val() +'&nbsp;&nbsp;');
            });


            //Get verification ocde
            $(document).on('click', '.get_code', function()
            {
                $(this).removeClass("get_code").addClass("verify").html('Verfy');
                $('.input-phone').hide();
                $('.edit').hide();
                $('.static_phone_show').html('');
                $('.modal-title').html('Verify Phone');

                $('#subheader_text').html('We just sent you a SMS with a code.'+ '<br><br>' + 'Enter it to verify your phone.');

                $('#phone_verification_code').show().val('');

                var carrierCode = $('#carrierCode').val();
                var phoneNumber = $("input[name=phoneNumber]").val();

                $.ajax({
                    headers:
                    {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: SITE_URL+"/profile/getVerificationCode",
                    dataType: "json",
                    cache: false,
                    data: {
                        'phoneNumber': phoneNumber,
                        'carrierCode': carrierCode,
                    }
                })
                .done(function(response)
                {
                    if (response.status == true)
                    {
                        $('#hasVerificationCode').val(response.message);
                    }
                });
            });


            //with  phone-verification - add-phone-number
            $(document).on('click', '.verify', function()
            {
                var classOfSubmit = $('#common_button');

                var phone_verification_code = $("#phone_verification_code").val();
                var defaultCountry = $('#defaultCountry').val();
                var carrierCode = $('#carrierCode').val();
                var phoneNumber = $("input[name=phoneNumber]").val();


                if (classOfSubmit.hasClass('verify'))
                {
                    $.ajax({
                        headers:
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: SITE_URL+"/profile/complete-phone-verification",
                        dataType: "json",
                        cache: false,
                        data: {
                            'defaultCountry': defaultCountry,
                            'carrierCode': carrierCode,
                            'phoneNumber': phoneNumber,
                            'phone_verification_code': phone_verification_code,
                        }
                    })
                    .done(function(data)
                    {
                        if (data.status == false || data.status == 500)
                        {
                            $('#message').css('display', 'block');
                            $('#message').html(data.message);
                            $('#message').addClass(data.error);
                        }
                        else
                        {

                            $('#message').removeClass('alert-danger');
                            $('#message').css('display', 'block');
                            $('#message').html(data.message);
                            $('#message').addClass(data.success);

                            $('#subheader_text').hide();
                            $('#phone_verification_code').hide();
                            $('#common_button').hide();
                            $('#close').hide();
                            $('.modal-title').hide();
                        }
                    });
                }
            });


            //without phone-verification - add-phone-number
            $(document).on('click', '.form_submit', function()
            {
                var classOfSubmit = $('#common_button');
                if (classOfSubmit.hasClass('form_submit'))
                {
                    var defaultCountry = $('#defaultCountry').val();
                    var carrierCode = $('#carrierCode').val();
                    var phoneNumber = $("input[name=phoneNumber]").val();

                    $.ajax({
                        headers:
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: SITE_URL+"/profile/add-phone-number",
                        dataType: "json",
                        cache: false,
                        data: {
                            'defaultCountry': defaultCountry,
                            'phoneNumber': phoneNumber,
                            'carrierCode': carrierCode,
                        }
                    })
                    .done(function(data)
                    {
                        if (data.status == true)
                        {
                            $('#message').css('display', 'block');
                            $('#message').html(data.message);
                            $('#message').addClass(data.class_name);

                            $('#subheader_text').hide();
                            $('#common_button').hide();
                            $('#close').hide();
                            $('.input-phone').hide();
                        }
                    });
                }
            });


            $(document).ready(function() {
                $('#profile_update_form').on('submit', function(event)
                {
                    var inputGroup = $('.phoneNumber');
                    if (inputGroup.hasClass( "error" ))
                    {
                        $("input[name=phoneNumber]").css('width', '60%');
                    }
                });
            });


            //jquery validation
            $('#complete-phone-verification').validate({
                rules: {
                    phoneNumber: {
                        required: true,
                        number: true,
                        maxlength: 20,
                    },
                    phone_verification_code: {
                        required: true,
                        number: true,
                    },
                },
                messages: {
                    phoneNumber: {
                        number: "Please enter numbers only!",
                    },
                },
                submitHandler: function(form)
                {
                    form.submit();
                }
            });
        //

///////////////////////////////////////////////////////////////////////////////////

        //Update


            $(document).on('click', '.update', function()
            {
                var phoneNumber = $("input[name=phoneNumber]").val();

                if (phoneNumber == '')
                {
                    $('#edit-phone-error').addClass('error').html('This field is required.').css({
                       'color' : 'red !important',
                       'font-size' : '14px',
                       'font-weight' : '800',
                       'padding-top' : '5px',
                    });
                    $('#phoneNumber-error').hide();
                    return false;
                }
                else if (isNaN(phoneNumber))
                {
                    $('#edit-phone-error').addClass('error').html('Please enter numbers only!').css({
                       'color' : 'red !important',
                       'font-size' : '14px',
                       'font-weight' : '800',
                       'padding-top' : '5px',
                    });
                    $('#phoneNumber-error').hide();
                    return false;
                }
                else
                {
                    $('#subheader_edit_text').html('To make sure this number is yours, Google will send you a verification code.');

                    $('.input-phone').hide();

                    $('#edit_static_phone_show').show();

                    $('.edit_button_edit').show();

                    $(this).removeClass("update").addClass("edit_get_code").html('Get Code');

                    $('#edit_static_phone_show').html('+' + $('#carrierCode').val() + $('#phoneNumber').val() + '&nbsp;&nbsp;');
                    return true;
                }
            });

            //onclick - edit_button_edit
            $(document).on('click', '.edit_button_edit', function()
            {
                $('.edit_get_code').removeClass("edit_get_code").addClass("update").html('Next');

                $('.edit_static_phone_show').html('');
                $(this).hide();

                $('#subheader_edit_text').html('Enter the number you’d like to use');
                $('.input-phone').show();
            });


            //Get verification edit_get_code
            $(document).on('click', '.edit_get_code', function()
            {
                $(this).removeClass("edit_get_code").addClass("edit_verify").html('Verfy');
                $('.input-phone').hide();
                $('.edit_button_edit').hide();
                $('.edit_static_phone_show').html('');
                $('.modal-title').html('Verify Phone');

                $('#subheader_edit_text').html('We just sent you a SMS with a code.'+ '<br><br>' + 'Enter it to verify your phone.');

                $('#edit_phone_verification_code').show().val('');

                var carrierCode = $('#carrierCode').val();
                var phoneNumber = $("input[name=phoneNumber]").val();

                $.ajax({
                    headers:
                    {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: SITE_URL+"/profile/editGetVerificationCode",
                    dataType: "json",
                    cache: false,
                    data: {
                        'phoneNumber': phoneNumber,
                        'carrierCode': carrierCode,
                    }
                })
                .done(function(response)
                {
                    if (response.status == true)
                    {
                        $('#editHasVerificationCode').val(response.message);
                    }
                });
            });


            //with  phone-verification - edit-phone-number
            $(document).on('click', '.edit_verify', function()
            {
                // alert('edit_verify')
                var classOfSubmit = $('#common_button_update');

                var edit_phone_verification_code = $("#edit_phone_verification_code").val();
                var defaultCountry = $('#defaultCountry').val();
                var carrierCode = $('#carrierCode').val();
                var phoneNumber = $("input[name=phoneNumber]").val();


                if (classOfSubmit.hasClass('edit_verify'))
                {
                    $.ajax({
                        headers:
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: SITE_URL+"/profile/edit-complete-phone-verification",
                        dataType: "json",
                        cache: false,
                        data: {
                            'defaultCountry': defaultCountry,
                            'carrierCode': carrierCode,
                            'phoneNumber': phoneNumber,
                            'edit_phone_verification_code': edit_phone_verification_code,
                        }
                    })
                    .done(function(data)
                    {
                        if (data.status == false || data.status == 500)
                        {
                            $('#message').css('display', 'block');
                            $('#message').html(data.message);
                            $('#message').addClass(data.error);
                        }
                        else
                        {

                            $('#message').removeClass('alert-danger');
                            $('#message').css('display', 'block');
                            $('#message').html(data.message);
                            $('#message').addClass(data.success);

                            $('#subheader_edit_text').hide();
                            $('#edit_phone_verification_code').hide();
                            $('#common_button_update').hide();
                            $('#close').hide();
                            $('.modal-title').hide();
                        }
                    });
                }
            });


            //without phone-verification - add-phone-number
            $(document).on('click', '.edit_form_submit', function()
            {
                var classOfSubmit = $('#common_button_update');
                if (classOfSubmit.hasClass('edit_form_submit'))
                {
                    var defaultCountry = $('#defaultCountry').val();
                    var carrierCode = $('#carrierCode').val();
                    var phoneNumber = $("input[name=phoneNumber]").val();

                    $.ajax({
                        headers:
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: SITE_URL+"/profile/update-phone-number",
                        dataType: "json",
                        cache: false,
                        data: {
                            'defaultCountry': defaultCountry,
                            'phoneNumber': phoneNumber,
                            'carrierCode': carrierCode,
                        }
                    })
                    .done(function(data)
                    {
                        if (data.status == true)
                        {
                            $('#message').css('display', 'block');
                            $('#message').html(data.message);
                            $('#message').addClass(data.class_name);

                            $('#subheader_edit_text').hide();
                            $('#common_button_update').hide();
                            $('#close').hide();
                            $('.input-phone').hide();
                            $('.modal-title').hide();
                        }
                    });
                }
            });

            //reload on close of phone add modal
                $('#editModal').on('hidden.bs.modal', function () {
                    window.location.reload();
                });

                $(document).on('click', '.editModal', function()
                {
                    $('#message').css('display', 'none');
                    $('#phone_update').prop('disabled',false);
                });

                //disable submit button if phoneNumber error exist
                $(document).ready(function()
                {
                    $("input[name=phoneNumber]").blur(function(event)
                    {
                        var errorPhoneCheckClass = $('#phoneNumber-error');

                        if (errorPhoneCheckClass.is(":visible"))
                        {
                            $('#phone_update').prop('disabled',true);
                        }
                        else
                        {
                            $('#phone_update').prop('disabled',false);
                        }
                    });
                });


///////////////////////////////////////////////////////////////

        //Delete

        //onclick - delete
        $(document).on('click', '.delete', function()
        {
            // alert('clicked');
            var defaultCountry = $('#defaultCountry').val();
            var carrierCode = $('#carrierCode').val();
            var phoneNumber = $("input[name=phoneNumber]").val();

            swal({
              title: "Are you sure you want to delete?",
              text: "You will not be able to recover this number",
              type: "warning",
              showCancelButton: true,
              confirmButtonClass: "btn-danger",
              confirmButtonText: "Confirm",
              cancelButtonText: "Cancel",
              closeOnConfirm: false,
              closeOnCancel: false
            },
            function(isConfirm)
            {
                if (!isConfirm) return;

                if (isConfirm)
                {
                    $.ajax({
                        headers:
                        {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: SITE_URL+"/profile/delete-phone-number",
                        dataType: "json",
                        cache: false,
                        data: {
                            'defaultCountry': defaultCountry,
                            'phoneNumber': phoneNumber,
                            'carrierCode': carrierCode,
                        }
                    })
                    .done(function(response)
                    {
                        swal({title: "Deleted!", text: response.message, type:response.status},
                            function(){
                               window.location.reload();
                            }
                        );
                    })
                    .fail(function(){
                        swal('Oops...', 'Something went wrong with ajax !', 'error');
                    });
                }
                else
                {
                    swal("Cancelled", "You Cancelled", "error");
                }
            });
        });
///////////////////////////////////////////////////////////////

        //start - ajax image upload
            function changeProfile() {
                $('#file').click();
            }
            $('#file').change(function () {
                if ($(this).val() != '') {
                    upload(this);

                }
            });
            function upload(img) {
                var form_data = new FormData();
                form_data.append('file', img.files[0]);
                form_data.append('_token', '{{csrf_token()}}');
                $('#loading').css('display', 'block');
                $.ajax({
                    url: "{{url('profile-image-upload')}}",
                    data: form_data,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function (data) {
                        if (data.fail) {
                            $('#profileImage').attr('src', '{{asset('public/user_dashboard/images/avatar.jpg')}}');
                            // alert(data.errors['file']);
                            alert(data.errors);
                        }
                        else {
                            $('#file_name').val(data);
                            $('#profileImage').attr('src', '{{asset('public/user_dashboard/profile')}}/' + data);
                            $('#profileImageHeader').attr('src', '{{asset('public/user_dashboard/profile')}}/' + data);
                        }
                        $('#loading').css('display', 'none');
                    },
                    error: function (xhr, status, error) {
                        alert(xhr.responseText);
                        $('#profileImage').attr('src', '{{asset('public/user_dashboard/images/avatar.jpg')}}');
                    }
                });
            }
        //end - ajax image upload


        //profile_update_form on submit
        $(document).ready(function() {
            $('#profile_update_form').on('submit', function(event)
            {
                var inputGroup = $('.phoneNumber');
                if (inputGroup.hasClass( "error" ))
                {
                    $("input[name=phoneNumber]").css('width', '60%');
                }
            });
        });

        //change btn-country css
        $(document).ready(function()
        {
            $("input[name=phoneNumber]").on('input', function(e)
            {
                var phoneNumber = $(this).val();

                if($.isNumeric(phoneNumber))
                {
                   $('#btn-country').css('margin-top', '0px');
                }
                else if(phoneNumber == '' || phoneNumber.length === 0)
                {
                   $('#btn-country').css('margin-top', '0px');
                }
                else
                {
                    $("input[name=phoneNumber]").css('width', '60%');
                }
            });
        });


        // Validate phone via Ajax
        $(document).ready(function()
        {
            $("input[name=phoneNumber]").on('input', function(e)
            {

                var id = $('#id').val();
                var phone = $("input[name=phoneNumber]").val();
                $.ajax({
                    headers:
                    {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: SITE_URL+"/profile/duplicate-phone-number-check",
                    dataType: "json",
                    cache: false,
                    data: {
                        'phone': phone,
                        'id': id,
                    }
                })
                .done(function(response)
                {
                    if (response.status == true)
                    {
                        if(phone.length == 0)
                        {
                            $('#phone-error').html('');
                        }
                        else{
                            $('#phone-error').addClass('error').html(response.fail).css("font-weight", "bold");
                            $('form').find("button[type='submit']").prop('disabled',true);
                        }
                    }
                    else if (response.status == false)
                    {
                        $('form').find("button[type='submit']").prop('disabled',false);
                        $('#phone-error').html('');
                    }
                });
            });
        });

        $('.input-phone').intlInputPhone();

        $("#reset_password").validate({
            rules: {
                old_password: {
                    required: true
                },
                password: {
                    required: true,
                    minlength: 6,
                },
                confirm_password: {
                    equalTo: "#password",
                    minlength: 6,
                }
            },
            messages: {
                password: {
                    required: "The password is required",
                }
            }
        });

        $('#profile_update_form').validate({
            rules: {
                first_name: {
                    required: true,
                },
                last_name: {
                    required: true,
                },
                phoneNumber: {
                    required: true,
                    number: true,
                    maxlength: 20,
                },
            },
            messages: {
                phoneNumber: {
                    number: "Please enter numbers only!",
                },
            },
            submitHandler: function(form)
            {
                $("#users_profile").attr("disabled", true);
                $(".spinner").show();
                $("#users_profile_text").text('Submitting...');
                form.submit();
            }
        });

        $('#update-phone-number').validate({
            rules: {
                phoneNumber: {
                    required: true,
                    number: true,
                    maxlength: 20,
                },
            },
            messages: {
                phoneNumber: {
                    number: "Please enter numbers only!",
                },
            },
        });
    </script>
@endsection
