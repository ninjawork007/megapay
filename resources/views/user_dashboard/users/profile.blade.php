@extends('user_dashboard.layouts.app')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/user_dashboard/css/intl-tel-input-13.0.0/build/css/intlTelInput.css')}}">
@endsection

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
                            <div class="chart-list float-left">
                                <ul>
                                    <li class="active"><a href="{{url('/profile')}}">@lang('message.dashboard.setting.title')</a></li>
                                    @if ($two_step_verification != 'disabled')
                                        <li><a href="{{url('/profile/2fa')}}">@lang('message.2sa.title-short-text')</a></li>
                                    @endif
                                    <li><a href="{{url('/profile/personal-id')}}">@lang('message.personal-id.title')
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
                                                <span id="file-error" style="display: none;"></span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        {{-- <div class="col-md-2">
                                            <img src="{{url('public/user_dashboard/images/password-icon.png')}}" class="rounded-circle rounded-circle-custom-trans">
                                        </div> --}}
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
                                                <h4 class="addPhoneTitle">@lang('message.dashboard.setting.add-phone')</h4>
                                                <p class="addPhoneBody">@lang('message.dashboard.setting.add-phone-subhead1') <b>+</b> @lang('message.dashboard.setting.add-phone-subhead2')</p>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="uploadAvatar">
                                                    <button type="button" class="btn btn-secondary btn-border btn-sm add" data-toggle="modal" data-target="#add" style="margin-top: 10px;">
                                                        <i class="fa fa-plus" id="modalTextSymbol"></i>
                                                        <span class="modalText">&nbsp; @lang('message.dashboard.setting.add-phone')</span>
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

                                                            <input type="hidden" name="defaultCountry" id="defaultCountry" class="form-control">
                                                            <input type="hidden" name="carrierCode" id="carrierCode" class="form-control">
                                                            <input type="hidden" name="countryName" id="countryName" class="form-control">


                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">@lang('message.dashboard.setting.add-phone')</h4>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>

                                                                <div class="modal-body">

                                                                    <div class="alert text-center" id="message" style="display: none"></div>

                                                                    <div class="form-group">
                                                                        <label id="subheader_text">@lang('message.dashboard.setting.add-phone-subheadertext')</label>
                                                                        <br>
                                                                        <div class="phone_group">
                                                                            <input type="tel" class="form-control" id="phone" name="phone">
                                                                        </div>
                                                                        <span id="phone-number-error"></span>
                                                                        <span id="tel-number-error"></span>

                                                                    </div>
                                                                    <div class="clearfix"></div>

                                                                    <div class="form-group">
                                                                        <label></label>
                                                                        <input id="phone_verification_code" type="text" maxlength="6" class="form-control" name="phone_verification_code"
                                                                        style="display: none;width: 46%;">
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
                                                                            <button type="button" class="btn btn-cust" data-dismiss="modal" id="close">@lang('message.form.cancel')</button>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <button type="button" class="btn btn-cust next" id="common_button">@lang('message.dashboard.button.next')</button>
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
                                            <div class="col-md-6">
                                                <h4 class="editPhoneTitle">@lang('message.dashboard.setting.phone-number')</h4>
                                                <p class="editPhoneBody">{{ auth()->user()->phone }}</p>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="uploadAvatar">
                                                    <button type="button" class="btn btn-secondary btn-border btn-sm editModal" data-toggle="modal" data-target="#editModal" style="margin-top: 10px;">
                                                        <i class="fa fa-edit"></i>
                                                        <span>&nbsp; @lang('message.dashboard.setting.edit-phone')</span>
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

                                                            <input type="hidden" name="edit_defaultCountry" id="edit_defaultCountry" value="{{ $user->defaultCountry }}">
                                                            <input type="hidden" name="edit_carrierCode" id="edit_carrierCode" value="{{ $user->carrierCode }}">

                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">@lang('message.dashboard.setting.edit-phone')</h4>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>

                                                                <div class="modal-body editModalBody">
                                                                    <div class="alert text-center" id="message" style="display: none"></div>

                                                                    <div class="form-group">
                                                                        <label id="subheader_edit_text">@lang('message.dashboard.setting.add-phone-subheadertext')</label>
                                                                        <br>
                                                                        <div class="phone_group">
                                                                            <input type="tel" class="form-control" id="edit_phone" name="edit_phone" value="{{ '+'.$user->carrierCode.$user->phone }}">
                                                                        </div>
                                                                        <span id="edit-phone-number-error"></span>
                                                                        <span id="edit-tel-number-error"></span>
                                                                    </div>
                                                                    <div class="clearfix"></div>

                                                                    <div class="form-group">
                                                                        <label></label>
                                                                        <input id="edit_phone_verification_code" type="text" maxlength="6" class="form-control" name="edit_phone_verification_code"
                                                                        style="display: none;width: 46%;">
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
                                                                            <button type="button" class="btn btn-cust" data-dismiss="modal" id="close">@lang('message.form.cancel')</button>
                                                                        </div>

                                                                        <div class="col-md-6">

                                                                            @php
                                                                                $bothDisabled = ($is_sms_env_enabled == false && $checkPhoneVerification == "Disabled");
                                                                            @endphp

                                                                            @if ($bothDisabled || $checkPhoneVerification == "Disabled")
                                                                                <button type="button" class="btn btn-cust edit_form_submit" id="common_button_update">@lang('message.form.update')</button>
                                                                            @else
                                                                                <button type="button" class="btn btn-cust update" id="common_button_update">@lang('message.dashboard.button.next')</button>
                                                                            @endif
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
                            @endif

                            <div class="clearfix"></div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h4>@lang('message.dashboard.setting.profile-information')</h4>
                                    <hr>
                                        <form method="post" action="{{url('prifile/update')}}" id="profile_update_form">
                                            {{csrf_field()}}

                                            <input type="hidden" value="{{$user->id}}" name="id" id="id" />

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
                                                {{-- default cauurency --}}
                                                <div class="form-group col-md-6">
                                                    <label for="email">@lang('message.dashboard.setting.default-wallet')
                                                    </label>
                                                    {{-- <select class="form-control" name="default_wallet" id="default_wallet" style="height: 45px;"> --}}
                                                    <select class="form-control" name="default_wallet" id="default_wallet">
                                                        @foreach($wallets as $wallet)
                                                            <option value="{{$wallet->id}}" {{$wallet->is_default == 'Yes' ? 'Selected' : ''}}>{{$wallet->currency->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>

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


                                            {{-- <div class="row">
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
                                            <div class="clearfix"></div> --}}

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
                                                    <input type="text" class="form-control" name="state" id="state" value="{{ $user->user_detail->state }}">
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

                                            {{-- <div class="row">
                                                <div class="form-group col-md-6">
                                                    <button type="submit" class="btn btn-cust col-12" id="users_profile">
                                                        <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="users_profile_text">@lang('message.dashboard.button.submit')</span>
                                                    </button>
                                                </div>
                                            </div> --}}

                                            <div class="row">
                                                <div class="form-group col-md-12">
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
<script src="{{ asset('public/user_dashboard/js/intl-tel-input-13.0.0/build/js/intlTelInput.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/isValidPhoneNumber.js')}}" type="text/javascript"></script>

<script>
////////////////////////////////////////////////////////////////
        //Add
            //reload on close of phone add modal
            $('#add').on('hidden.bs.modal', function ()
            {
                if ($("#phone").val() != '')
                {
                    $(this).find("input").val('').end(); //reset input
                    $('#complete-phone-verification').validate().resetForm(); //reset validation messages
                    window.location.reload();
                }
            });

            /*
            intlTelInput - add
            */
            $(document).ready(function()
            {
                $("#phone").intlTelInput({
                    separateDialCode: true,
                    nationalMode: true,
                    preferredCountries: ["us"],
                    autoPlaceholder: "polite",
                    placeholderNumberType: "MOBILE",
                    utilsScript: "public/user_dashboard/js/intl-tel-input-13.0.0/build/js/utils.js"
                });

                var countryData = $("#phone").intlTelInput("getSelectedCountryData");
                $('#defaultCountry').val(countryData.iso2);
                $('#carrierCode').val(countryData.dialCode);

                $("#phone").on("countrychange", function(e, countryData)
                {
                    // log(countryData);
                    $('#defaultCountry').val(countryData.iso2);
                    $('#carrierCode').val(countryData.dialCode);

                    if ($.trim($(this).val()))
                    {
                        if (!$(this).intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($(this).val())))
                        {
                            // alert('invalid');
                            $('#tel-number-error').addClass('error').html("{{__("Please enter a valid International Phone Number.")}}").css({
                               'color' : 'red !important',
                               'font-size' : '14px',
                               'font-weight' : '800',
                               'padding-top' : '5px',
                            });
                            $('#common_button').prop('disabled',true);
                            $('#phone-number-error').hide();
                        }
                        else
                        {
                            $('#tel-number-error').html('');

                            var id = $('#id').val();
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
                                    'phone': $.trim($(this).val()),
                                    'carrierCode': $.trim(countryData.dialCode),
                                    'id': id,
                                }
                            })
                            .done(function(response)
                            {
                                if (response.status == true)
                                {
                                    $('#tel-number-error').html('');
                                    $('#phone-number-error').show();

                                    $('#phone-number-error').addClass('error').html(response.fail).css({
                                       'color' : 'red !important',
                                       'font-size' : '14px',
                                       'font-weight' : '800',
                                       'padding-top' : '5px',
                                    });
                                    $('#common_button').prop('disabled',true);
                                }
                                else if (response.status == false)
                                {
                                    $('#tel-number-error').show();
                                    $('#phone-number-error').html('');

                                    $('#common_button').prop('disabled',false);
                                }
                            });
                        }
                    }
                    else
                    {
                        $('#tel-number-error').html('');
                        $('#phone-number-error').html('');
                        $('#common_button').prop('disabled',false);
                    }
                });
            });
            /*
            intlTelInput - add
            */

            //Invalid Number Validation - add
            $(document).ready(function()
            {
                $("#phone").on('blur', function(e)
                {
                    if ($.trim($(this).val()))
                    {
                        if (!$(this).intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($(this).val())))
                        {
                            // alert('invalid');
                            $('#tel-number-error').addClass('error').html("{{__("Please enter a valid International Phone Number.")}}").css({
                               'color' : 'red !important',
                               'font-size' : '14px',
                               'font-weight' : '800',
                               'padding-top' : '5px',
                            });
                            $('#common_button').prop('disabled',true);
                            $('#phone-number-error').hide();
                        }
                        else
                        {
                            var id = $('#id').val();
                            var phone = $(this).val().replace(/-|\s/g,""); //replaces 'whitespaces', 'hyphens'
                            var phone = $(this).val().replace(/^0+/, ""); //replaces (leading zero - for BD phone number)
                            // log(phone);

                            var pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;

                            if(phone.length == 0)
                            {
                                $('#phone-number-error').addClass('error').html("{{__("This field is required.")}}").css({
                                   'color' : 'red !important',
                                   'font-size' : '14px',
                                   'font-weight' : '800',
                                   'padding-top' : '5px',
                                });
                                $('#common_button').prop('disabled',true);
                            }
                            else
                            {
                                $('#phone-number-error').hide();
                                $('#common_button').prop('disabled',false);
                            }

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
                                    'carrierCode': pluginCarrierCode,
                                }
                            })
                            .done(function(response)
                            {
                                $('#phone-number-error').show();
                                if (response.status == true)
                                {
                                    if(phone.length == 0)
                                    {
                                        $('#phone-number-error').html('');
                                    }
                                    else
                                    {
                                        $('#phone-number-error').addClass('error').html(response.fail).css({
                                           'color' : 'red !important',
                                           'font-size' : '14px',
                                           'font-weight' : '800',
                                           'padding-top' : '5px',
                                        });
                                        $('#common_button').prop('disabled',true);
                                    }
                                }
                                else if (response.status == false)
                                {
                                    $('#common_button').prop('disabled',false);
                                    $('#phone-number-error').html('');
                                }
                            });
                            $('#tel-number-error').html('');
                            $('#phone-number-error').show();
                            $('#common_button').prop('disabled',false);
                        }
                    }
                    else
                    {
                        $('#tel-number-error').html('');
                        $('#phone-number-error').html('');
                        $('#common_button').prop('disabled',false);
                    }
                });
            });


            //is_sms_env_enabled and phone verification check
            $(document).ready(function()
            {
                var is_sms_env_enabled = $('#is_sms_env_enabled').val();
                var checkPhoneVerification = $('#checkPhoneVerification').val();

                if ((is_sms_env_enabled != true && checkPhoneVerification != "Enabled") || checkPhoneVerification != "Enabled")
                {
                    $('.next').removeClass("next").addClass('form_submit').html("{{__("Submit")}}");
                }
                else
                {
                    $('.next').removeClass("form_submit").addClass('next').html("{{__("Next")}}");
                }
            });

            // next
            $(document).on('click', '.next', function()
            {
                var phone = $("input[name=phone]");
                if (phone.val() == '')
                {
                    $('#phone-number-error').addClass('error').html("{{__("This field is required.")}}").css({
                       'color' : 'red !important',
                       'font-size' : '14px',
                       'font-weight' : '800',
                       'padding-top' : '5px',
                    });
                    return false;
                }
                else if(phone.hasClass('error'))
                {
                    return false;
                }
                else
                {
                    $('.modal-title').html("{{__("Get Code")}}");
                    $('#subheader_text').html('{{ __('To make sure this number is yours, we will send you a verification code.') }}');
                    $('.phone_group').hide();
                    $('#static_phone_show').show();
                    $('.edit').show();

                    $(this).removeClass("next").addClass("get_code").html("{{__("Get Code")}}");
                    var fullPhone = $("#phone").intlTelInput("getNumber");
                    $('#static_phone_show').html(fullPhone + '&nbsp;&nbsp;');
                    return true;
                }
            });

            //edit - add_phone
            $(document).on('click', '.edit', function()
            {
                $('.get_code').removeClass("get_code").addClass("next").html("{{__("Next")}}");
                $('.static_phone_show').html('');
                $(this).hide();
                $('#subheader_text').html('{{ __('Enter the number you’d like to use') }}');
                $('.phone_group').show();
            });


            //get_code
            $(document).on('click', '.get_code', function()
            {
                $('.modal-title').html("{{__("Verify Phone")}}");
                $('.phone_group').hide();
                $('.static_phone_show').html('');

                $('#subheader_text').html('We just sent you a SMS with a code.'+ '<br><br>' + 'Enter it to verify your phone.');

                $('#subheader_text').html('{{__("We just sent you a SMS with a code.")}}'+ '<br><br>' + '{{__("Enter it to verify your phone.")}}');

                $('.edit').hide();
                $('#phone_verification_code').show().val('');
                $(this).removeClass("get_code").addClass("verify").html("{{__("Verify")}}");

                var pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;
                var pluginPhone = $("#phone").intlTelInput("getNumber");

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
                        'phone': pluginPhone,
                        'carrierCode': pluginCarrierCode,
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

            //verify
            $(document).on('click', '.verify', function()
            {
                var classOfSubmit = $('#common_button');
                var phone_verification_code = $("#phone_verification_code").val();

                var pluginPhone = $("#phone").intlTelInput("getNumber");
                var pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;
                var pluginDefaultCountry = $('#phone').intlTelInput('getSelectedCountryData').iso2;

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
                            'phone': pluginPhone,
                            'defaultCountry': pluginDefaultCountry,
                            'carrierCode': pluginCarrierCode,
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

            //form_submit
            $(document).on('click', '.form_submit', function()
            {
                var classOfSubmit = $('#common_button');
                if (classOfSubmit.hasClass('form_submit'))
                {
                    var pluginPhone = $("#phone").intlTelInput("getNumber");
                    var pluginDefaultCountry = $('#phone').intlTelInput('getSelectedCountryData').iso2;
                    var pluginCarrierCode = $('#phone').intlTelInput('getSelectedCountryData').dialCode;

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
                            'phone': pluginPhone,
                            'defaultCountry': pluginDefaultCountry,
                            'carrierCode': pluginCarrierCode,
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
                            $('.phone_group').hide();
                        }
                    });
                }
            });
////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
    //Update

        //clear inputs on close - edit modal
        $('#editModal').on('hidden.bs.modal', function () {
            if ($("#edit_phone").val() != '')
            {
                var OrginalUsercarrierCode = '{{ $user->carrierCode }}';
                var OrginalUserphone = '{{ $user->phone }}';
                $("#edit_phone").val(`+${OrginalUsercarrierCode}${OrginalUserphone}`)
                window.location.reload(); //need to reload - or validation message still exists.
            }
        });

         /*
        intlTelInput - edit
        */
        $(document).ready(function()
        {
            $("#edit_phone").intlTelInput({
                separateDialCode: true,
                nationalMode: true,
                preferredCountries: ["us"],
                autoPlaceholder: "polite",
                placeholderNumberType: "MOBILE",
                formatOnDisplay: false,
                utilsScript: "public/user_dashboard/js/intl-tel-input-13.0.0/build/js/utils.js"
            })
            .done(function()
            {
                let carrierCode = '{{ !empty($user->carrierCode) ? $user->carrierCode : NULL }}';
                let defaultCountry = '{{ !empty($user->defaultCountry) ? $user->defaultCountry : NULL }}';
                let formattedPhone = '{{ !empty($user->formattedPhone) ? $user->formattedPhone : NULL }}';
                if (formattedPhone !== null && carrierCode !== null && defaultCountry !== null) {
                    $("#edit_phone").intlTelInput("setNumber", formattedPhone);
                    $('#edit_defaultCountry').val(defaultCountry);
                    $('#edit_carrierCode').val(carrierCode);
                }
            });
        });

        var editCountryData = $("#edit_phone").intlTelInput("getSelectedCountryData");
        $("#edit_phone").on("countrychange", function(e, editCountryData)
        {
            // log(editCountryData);
            $('#edit_defaultCountry').val(editCountryData.iso2);
            $('#edit_carrierCode').val(editCountryData.dialCode);

            if ($.trim($(this).val()))
            {
                if (!$(this).intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($(this).val())))
                {
                    // alert('invalid');
                    $('#edit-tel-number-error').addClass('error').html("{{__("Please enter a valid International Phone Number.")}}").css({
                       'color' : 'red !important',
                       'font-size' : '14px',
                       'font-weight' : '800',
                       'padding-top' : '5px',
                    });
                    $('#common_button_update').prop('disabled',true);
                    $('#edit-phone-number-error').hide();
                }
                else
                {
                    $('#edit-tel-number-error').html('');

                    var id = $('#id').val();
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
                            'phone': $.trim($(this).val()),
                            'carrierCode': $.trim(countryData.dialCode),
                            'id': id,
                        }
                    })
                    .done(function(response)
                    {
                        if (response.status == true)
                        {
                            $('#edit-tel-number-error').html('');
                            $('#edit-phone-number-error').show();

                            $('#edit-phone-number-error').addClass('error').html(response.fail).css("font-weight", "bold");
                            $('#common_button_update').prop('disabled',true);
                        }
                        else if (response.status == false)
                        {
                            $('#edit-tel-number-error').show();
                            $('#edit-phone-number-error').html('');

                            $('#common_button_update').prop('disabled',false);
                        }
                    });
                }
            }
            else
            {
                $('#edit-tel-number-error').html('');
                $('#edit-phone-number-error').html('');
                $('#common_button_update').prop('disabled',false);
            }
        });

        //Invalid Number Validation - user edit
        $(document).ready(function()
        {
            $("#edit_phone").on('blur', function(e)
            {
                if ($.trim($(this).val()))
                {
                    if (!$(this).intlTelInput("isValidNumber") || !isValidPhoneNumber($.trim($(this).val())))
                    {
                        // alert('invalid');
                        $('#edit-tel-number-error').addClass('error').html("{{__("Please enter a valid International Phone Number.")}}").css({
                           'color' : 'red !important',
                           'font-size' : '14px',
                           'font-weight' : '800',
                           'padding-top' : '5px',
                        });
                        $('#common_button_update').prop('disabled',true);
                        $('#edit-phone-number-error').hide();
                    }
                    else
                    {
                        var id = $('#user_id').val();

                        var phone = $(this).val().replace(/-|\s/g,""); //replaces 'whitespaces', 'hyphens'
                        var phone = $(this).val().replace(/^0+/,"");  //replaces (leading zero - for BD phone number)

                        var pluginCarrierCode = $(this).intlTelInput('getSelectedCountryData').dialCode;

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
                                'id': id,
                                // 'phone': $.trim(phone),
                                'phone': phone,
                                'carrierCode': $.trim(pluginCarrierCode),
                            }
                        })
                        .done(function(response)
                        {
                            if (response.status == true)
                            {
                                if(phone.length == 0)
                                {
                                    $('#edit-phone-number-error').html('');
                                }
                                else
                                {
                                    $('#edit-phone-number-error').addClass('error').html(response.fail).css({
                                       'color' : 'red !important',
                                       'font-size' : '14px',
                                       'font-weight' : '800',
                                       'padding-top' : '5px',
                                    });
                                    $('#common_button_update').prop('disabled',true);
                                }
                            }
                            else if (response.status == false)
                            {
                                $('#common_button_update').prop('disabled',false);
                                $('#edit-phone-number-error').html('');
                            }
                        });
                        $('#edit-tel-number-error').html('');
                        $('#edit-phone-number-error').show();
                        $('#common_button_update').prop('disabled',false);
                    }
                }
                else
                {
                    $('#edit-tel-number-error').html('');
                    $('#edit-phone-number-error').html('');
                    $('#common_button_update').prop('disabled',false);
                }
            });
        });

        // Duplicate Validate phone via Ajax - update

         /*
        intlTelInput - edit
        */

        //when phone verificaiton is enabled
        $(document).on('click', '.update', function()
        {
            var phone = $("input[name=edit_phone]");
            if (phone.val() == '')
            {
                $('#edit-phone-number-error').addClass('error').html("{{__("This field is required.")}}").css({
                   'color' : 'red !important',
                   'font-size' : '14px',
                   'font-weight' : '800',
                   'padding-top' : '5px',
                });
                return false;
            }
            else if(phone.hasClass('error'))
            {
                return false;
            }
            else
            {
                $('.modal-title').html("{{__("Get Code")}}");

                $('#subheader_edit_text').html("{{__("To make sure this number is yours, we will send you a verification code.")}}");

                $('.phone_group').hide();

                $('#edit_static_phone_show').show();

                $('.edit_button_edit').show();

                $(this).removeClass("update").addClass("edit_get_code").html("{{__("Get Code")}}");

                var edit_phone = $("#edit_phone").intlTelInput("getNumber");
                $('#edit_static_phone_show').html(edit_phone + '&nbsp;&nbsp;');
                return true;
            }
        });

        // //edit_button_edit
        // $(document).on('click', '.edit_button_edit', function()
        // {
        //     $('.edit_get_code').removeClass("edit_get_code").addClass("update").html("{{__("Next")}}");
        //     $('.edit_static_phone_show').html('');
        //     $(this).hide();
        //     $('#subheader_edit_text').html("{{__("Enter the number you’d like to use")}}");
        //     $('.phone_group').show();
        // });

        //edit_get_code
        $(document).on('click', '.edit_get_code', function()
        {
            $('.modal-title').html("{{__("Verify Phone")}}");
            $(this).removeClass("edit_get_code").addClass("edit_verify").html("{{__("Verify")}}");
            $('.phone_group').hide();
            $('.edit_button_edit').hide();
            $('.edit_static_phone_show').html('');
            $('#subheader_edit_text').html('{{__("We just sent you a SMS with a code.")}}'+ '<br><br>' + '{{__("Enter it to verify your phone.")}}.');
            $('#edit_phone_verification_code').show().val('');

            var pluginPhone = $("#edit_phone").intlTelInput("getNumber");
            var pluginCarrierCode = $('#edit_phone').intlTelInput('getSelectedCountryData').dialCode;

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
                    'phone': pluginPhone,
                    'code': pluginCarrierCode,
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

        //edit_verify
        $(document).on('click', '.edit_verify', function()
        {
            var classOfSubmit = $('#common_button_update');

            var edit_phone_verification_code = $("#edit_phone_verification_code").val();

            var pluginPhone = $("#edit_phone").intlTelInput("getNumber");
            var pluginDefaultCountry = $('#edit_phone').intlTelInput('getSelectedCountryData').iso2;
            var pluginCarrierCode = $('#edit_phone').intlTelInput('getSelectedCountryData').dialCode;


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
                        'phone': pluginPhone,
                        'flag': pluginDefaultCountry,
                        'code': pluginCarrierCode,
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

        //when phone verificaiton is disabled
        $(document).on('click', '.edit_form_submit', function()
        {
            var classOfSubmit = $('#common_button_update');
            if (classOfSubmit.hasClass('edit_form_submit'))
            {
                var pluginPhone = $("#edit_phone").intlTelInput("getNumber");
                var pluginDefaultCountry = $('#edit_phone').intlTelInput('getSelectedCountryData').iso2;
                var pluginCarrierCode = $('#edit_phone').intlTelInput('getSelectedCountryData').dialCode;

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
                        'phone': pluginPhone,
                        'flag': pluginDefaultCountry,
                        'code': pluginCarrierCode,
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
                        $('.phone_group').hide();
                        $('.modal-title').hide();
                    }
                });
            }
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
                            // alert(data.errors);
                            // log(data.errors.file);
                            $('#file-error').show().addClass('error').html(data.errors.file).css({
                               'color' : 'red !important',
                               'font-size' : '14px',
                               'font-weight' : '800',
                               'padding-top' : '5px',
                            });
                        }
                        else {
                            $('#file-error').hide();
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

           jQuery.extend(jQuery.validator.messages, {
                required: "{{__('This field is required.')}}",
                minlength: $.validator.format( "{{__("Please enter at least")}}"+" {0} "+"{{__("characters.")}}" ),
            })
        //validation -rest
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
                        required: "{{__('This field is required.')}}",
                    },
                    confirm_password: {
                        equalTo: "{{__('Please enter the same value again.')}}",
                    },
                }
            });

            // jQuery.validator.addMethod("letters_with_spaces_and_dot", function(value, element)
            // {
            //     return this.optional(element) || /^[A-Za-z. ]+$/i.test(value); //letters + dot(.) symbol
            // }, "{{__("Please enter letters & only dot(.) symbol is allowed!")}}");

            // jQuery.validator.addMethod("letters_with_spaces", function(value, element)
            // {
            //     return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
            // }, "{{__("Please enter letters only!")}}");

            $('#profile_update_form').validate({
                rules: {
                    first_name: {
                        required: true,
                        // letters_with_spaces_and_dot: true,
                    },
                    last_name: {
                        required: true,
                        // letters_with_spaces: true,
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

        ///////////////////////////////////////////////////////////////
        //Delete

            //onclick - delete
            // $(document).on('click', '.delete', function()
            // {
            //     // alert('clicked');
            //     var defaultCountry = $('#defaultCountry').val();
            //     var carrierCode = $('#carrierCode').val();
            //     var phone = $("input[name=edit_phone]").val();

            //     swal({
            //       title: "Are you sure you want to delete?",
            //       text: "You won't be able to revert this!",
            //       type: "warning",
            //       showCancelButton: true,
            //       // confirmButtonColor: 'rgb(221, 51, 51)',
            //       confirmButtonText: "Yes, delete it!",
            //       // cancelButtonColor: '#d33',
            //       cancelButtonText: "Cancel",
            //       closeOnConfirm: false,
            //       showLoaderOnConfirm: true,
            //       closeOnCancel: true
            //     },
            //     function(isConfirm)
            //     {
            //         if (!isConfirm) return;

            //         if (isConfirm)
            //         {
            //             $.ajax({
            //                 headers:
            //                 {
            //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //                 },
            //                 method: "POST",
            //                 url: SITE_URL+"/profile/delete-phone-number",
            //                 dataType: "json",
            //                 cache: false,
            //                 data: {
            //                     'defaultCountry': defaultCountry,
            //                     'phone': phone,
            //                     'carrierCode': carrierCode,
            //                 }
            //             })
            //             .done(function(response)
            //             {
            //                 swal({title: "Deleted!", text: response.message, type:response.status},
            //                     function(){
            //                        window.location.reload();
            //                     }
            //                 );
            //             })
            //             .fail(function(){
            //                 swal('Oops...', 'Something went wrong with ajax !', 'error');
            //             });
            //         }
            //         else
            //         {
            //             swal("Cancelled", "You Cancelled", "error");
            //         }
            //     });
            // });
///////////////////////////////////////////////////////////////
</script>
@endsection

{{--
<div class="col-md-1 extra_col"></div>
<div class="col-md-2 delete_col2">
    <div class="uploadAvatar">
        <button type="button" class="btn btn-secondary btn-border btn-sm delete" style="margin-top: 10px;">
            <i class="fa fa-trash"></i>
            <span>&nbsp; Delete phone</span>
        </button>
    </div>
</div> --}}