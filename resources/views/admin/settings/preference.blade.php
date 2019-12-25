@extends('admin.layouts.master')
@section('title', 'Preferences')

@section('page_content')
  <!-- Main content -->
  <div class="row">
      <div class="col-md-3 settings_bar_gap">
          {{-- settings_bar --}}
          @include('admin.common.settings_bar')
      </div>
      <div class="col-md-9">
          <div class="box box-info">
              <div class="box-header with-border text-center">
                <h3 class="box-title">Manage Preferences</h3>
              </div>
              <form action="{{ url('admin/save-preference') }}" method="post" id="myform1" class="form-horizontal">
              {!! csrf_field() !!}
                <div class="box-body">

                  <div class="form-group">
                      <label class="col-sm-3 control-label" for="inputEmail3">TimeZone</label>
                      <div class="col-sm-6">
                        <select class="select2" name="dflt_timezone" id="dflt_timezone">

                            @foreach($timezones as $timezone)
                              <option value="{{ $timezone['zone'] }}" {{ isset($prefData['preference']['dflt_timezone']) && $prefData['preference']['dflt_timezone'] == $timezone['zone'] ? 'selected' : "" }}>
                                {{ $timezone['diff_from_GMT'] . ' - ' . $timezone['zone'] }}
                              </option>
                            @endforeach

                        </select>
                      </div>
                  </div>

                  <div class="form-group">
                      <label class="col-sm-3 control-label" for="processed_by">Send/Request Via</label>
                      <div class="col-sm-6">
                        <select name="processed_by" class="select2" id="processed_by">
                            <option value="email" {{isset($prefData['preference']['processed_by']) && $prefData['preference']['processed_by'] == 'email' ? 'selected':""}}>Email</option>
                            <option value="phone" {{isset($prefData['preference']['processed_by']) && $prefData['preference']['processed_by'] == 'phone' ? 'selected':""}}>Phone</option>
                            <option value="email_or_phone" {{isset($prefData['preference']['processed_by']) && $prefData['preference']['processed_by'] == 'email_or_phone' ? 'selected':""}}>Email or Phone</option>
                        </select>
                        <span id="processed-by-check-error"></span>
                      </div>
                  </div>

                  <div class="form-group">
                  <label class="col-sm-3 control-label" for="inputEmail3">Email Verification</label>
                      <div class="col-sm-6">
                        <select name="verification_mail" class="select2">
                            <option value="Enabled" {{isset($prefData['preference']['verification_mail']) && $prefData['preference']['verification_mail'] == 'Enabled' ? 'selected':""}}>Enabled</option>
                            <option value="Disabled" {{isset($prefData['preference']['verification_mail']) && $prefData['preference']['verification_mail'] == 'Disabled' ? 'selected':""}}>Disabled</option>
                        </select>
                      </div>
                  </div>

                  <div class="form-group">
                  <label class="col-sm-3 control-label" for="inputEmail3">Phone Verification</label>
                      <div class="col-sm-6">
                        <select name="phone_verification" class="select2" id="phone_verification">
                            <option value="Enabled" {{isset($prefData['preference']['phone_verification']) && $prefData['preference']['phone_verification'] == 'Enabled' ? 'selected':""}}>Enabled</option>
                            <option value="Disabled" {{isset($prefData['preference']['phone_verification']) && $prefData['preference']['phone_verification'] == 'Disabled' ? 'selected':""}}>Disabled</option>
                        </select>
                        <span id="phone_verification-error"></span>
                      </div>
                  </div>

                  <div class="form-group">
                  <label class="col-sm-3 control-label" for="inputEmail3">2-Factor Authentication</label>
                      <div class="col-sm-6">
                        <select name="two_step_verification" id="two_step_verification" class="select2">
                            <option value="disabled" {{isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'disabled' ? 'selected':""}}>Disabled</option>
                            <option value="by_email" {{isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_email' ? 'selected':""}}>By email</option>
                            <option {{isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_google_authenticator' ? 'selected':""}}
                            value="by_google_authenticator">
                              By google authenticator
                            </option>
                            <option value="by_phone" {{isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_phone' ? 'selected':""}}>By phone</option>
                            <option value="by_email_phone" {{isset($prefData['preference']['two_step_verification']) && $prefData['preference']['two_step_verification'] == 'by_email_phone' ? 'selected':""}}>By email & phone</option>
                        </select>
                        <span id="sms-error"></span>
                      </div>
                  </div>

                  {{-- row_per_page --}}
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Row Per Page:</label>

                    <div class="col-sm-6">
                      <select name="row_per_page" class="select2" >
                          <option value="10" {{isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 10 ? 'selected':""}}>10</option>
                          <option value="25" {{isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 25 ? 'selected':""}}>25</option>
                          <option value="50" {{isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 50 ? 'selected':""}}>50</option>
                          <option value="100" {{isset($prefData['preference']['row_per_page']) && $prefData['preference']['row_per_page'] == 100 ? 'selected':""}}>100</option>
                      </select>
                    </div>
                  </div>

                  {{-- date_sepa --}}
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Date Separator:</label>

                    <div class="col-sm-6">
                      <select name="date_sepa" class="select2">
                          <option value="-" {{isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '-' ? 'selected':""}}>-</option>
                          <option value="/" {{isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '/' ? 'selected':""}}>/</option>
                          <option value="." {{isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '.' ? 'selected':""}}>.</option>
                          {{-- <option value="  " {{isset($prefData['preference']['date_sepa']) && $prefData['preference']['date_sepa'] == '  ' ? 'selected':""}}>  </option> --}}
                      </select>
                    </div>
                  </div>

                  {{-- date_format --}}
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Date Format:</label>
                    <div class="col-sm-6">
                      <select name="date_format" class="select2" >
                          <option value="0" {{isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 0 ? 'selected':""}}>yyyymmdd {2019 12 31}</option>
                          <option value="1" {{isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 1 ? 'selected':""}}>ddmmyyyy {31 12 2019}</option>
                          <option value="2" {{isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 2 ? 'selected':""}}>mmddyyyy {12 31 2019}</option>
                          <option value="3" {{isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 3 ? 'selected':""}}>ddMyyyy &nbsp;&nbsp;&nbsp;{31 Dec 2019}</option>
                          <option value="4" {{isset($prefData['preference']['date_format']) && $prefData['preference']['date_format'] == 4 ? 'selected':""}}>yyyyMdd &nbsp;&nbsp;&nbsp;{2019 Dec 31}</option>
                      </select>
                    </div>
                  </div>

                  {{-- decimal places --}}
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Decimal Format (.)</label>
                    <div class="col-sm-6">
                      <select name="decimal_format_amount" class="select2">
                          @for($i=1;$i<=8;$i++)
                              <option value="{{$i}}" {{isset($prefData['preference']['decimal_format_amount']) && $prefData['preference']['decimal_format_amount'] == $i ? 'selected':""}}>{{$i}}</option>
                          @endfor
                      </select>
                    </div>
                  </div>

                  {{-- money_format --}}
                  <div class="form-group">
                  <label class="col-sm-3 control-label" for="inputEmail3">Money Symbol Position:</label>
                      <div class="col-sm-6">
                        <select name="money_format" class="select2">
                            <option value="before" {{isset($prefData['preference']['money_format']) && $prefData['preference']['money_format'] == 'before' ? 'selected':""}}>Before { $500 }</option>
                            <option value="after" {{isset($prefData['preference']['money_format']) && $prefData['preference']['money_format'] == 'after' ? 'selected':""}}>After { 500$ }</option>
                        </select>
                      </div>
                  </div>

                  {{-- Thousand Seperator --}}
                  <div class="form-group">
                  <label class="col-sm-3 control-label" for="inputEmail3">Thousand Separator:</label>
                      <div class="col-sm-6">
                        <select name="thousand_separator" class="select2" id="thousand_separator">
                            <option data-decimal="." value="," {{isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == ',' ? 'selected':""}}>,(comma)</option>
                            <option data-decimal="," value="." {{isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == '.' ? 'selected':""}}>.(dot)</option>
                        </select>
                          <span>Followed By <span>
                            <span id="separator" style="font-size: 20px;font-weight: 800">
                                {{isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == '.' ? ',':""}}
                                {{isset($prefData['preference']['thousand_separator']) && $prefData['preference']['thousand_separator'] == ',' ? '.':""}}
                            </span> (decimal)</span>
                          </span>
                      </div>
                  </div>

                  @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_preference'))
                  <div class="row">
                      <div class="col-md-9">
                          <button class="btn btn-primary btn-flat pull-right" type="submit">Submit</button>
                      </div>
                  </div>
                  @endif

                </div>
              </form>
          </div>
      </div>
  </div>
  <!-- /.box -->
@endsection

@push('extra_body_scripts')

  <script type="text/javascript">

    $(window).on('load',function()
    {
        $(".select2").select2();
    });

    //thousand_separator
    $('#thousand_separator').on('change.select2', function()
    {
        $('#separator').html($('option:selected', this).attr('data-decimal'));
    });

    let condition;
    let errorElement;

    //two_step_verification
    $(document).ready(function()
    {
        $("#two_step_verification").change(function()
        {
            condition = $(this).val() == 'by_phone' || $(this).val() == 'by_email_phone'
            errorElement = $('#sms-error');
            checkSmsGatewaySettingsForPreference(condition, errorElement);
        });
    });

    //check sms setting for phone_verification
    $(document).ready(function()
    {
        $("#phone_verification").change(function()
        {
            condition = $(this).val() == 'Enabled';
            errorElement = $('#phone_verification-error');
            checkSmsGatewaySettingsForPreference(condition, errorElement);
        });
    });

    //check sms setting for processed by - phone and email_or_phone
    $(document).ready(function()
    {
        $("#processed_by").change(function()
        {
            condition = $(this).val() == 'phone'||$(this).val() == 'email_or_phone';
            errorElement = $('#processed-by-check-error');
            checkSmsGatewaySettingsForPreference(condition, errorElement);
        });
    });

    function checkSmsGatewaySettingsForPreference(condition, errorElement)
    {
        if (condition)
        {
            $.ajax(
            {
                headers:
                {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: SITE_URL + "/admin/settings/check-sms-settings",
                dataType: "json",
                cache: false,
            }).done(function(response)
            {
                // console.log(response);
                if (response.status == false)
                {
                    errorElement.addClass('error').html(response.message).css("font-weight", "bold");
                    $('form').find("button[type='submit']").prop('disabled', true);
                }
                else if (response.status == true)
                {
                    errorElement.html('');
                    $('form').find("button[type='submit']").prop('disabled', false);
                }
            });
        }
        else
        {
            errorElement.html('');
            $('form').find("button[type='submit']").prop('disabled', false);
        }
    }

  </script>

@endpush