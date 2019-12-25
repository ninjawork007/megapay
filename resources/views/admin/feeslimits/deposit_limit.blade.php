@extends('admin.layouts.master')
@section('title', 'Fees & Limits')

@section('head_style')
  <!-- custom-checkbox -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/custom-checkbox.css') }}">
@endsection

@section('page_content')
  <div class="box box-default">
      <div class="box-body">
          <div class="row">
              <div class="col-md-12">
                  <div class="top-bar-title padding-bottom">Fees &amp; Limits</div>
              </div>
          </div>
      </div>
  </div>

  <div class="box">
    <div class="box-body">

      <div class="row">
          <div class="col-md-2">
            <div class="dropdown pull-left" style="margin-top: 10px;">
              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Currency : <span class="currencyName">{{ $currency->name }}</span>
              <span class="caret"></span></button>
              <ul class="dropdown-menu">
                @foreach($currencyList as $currencyItem)
                  <li class="listItem" data-rel="{{$currencyItem->id}}" data-default="{{ $currencyItem->default }}">
                    <a href="#">{{$currencyItem->name}}</a>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
          <div class="col-md-2 col-md-offset-8 defaultCurrencyDiv" style="display: none;">
              <h4 class="form-control-static pull-right"><span class="label label-success">Default Currency</span></h4>
          </div>
      </div>

    </div>
  </div>

  <div class="row">
    <div class="col-md-3">
       @include('admin.common.currency_menu')
    </div>

    <div class="col-md-9">
      <div class="box box-info">
        <div class="box-header with-border text-center">
          <h3 class="box-title">

            @if($list_menu == 'request_payment')
              {{ ucwords(str_replace('_', ' ', $list_menu)) }} Settings
            @elseif($list_menu == 'withdrawal')
              {{ "Payout Settings" }}
            @else
              {{ ucfirst($list_menu) }} Settings
            @endif
          </h3>
        </div>

        <form action='{{url('admin/settings/feeslimit/update-deposit-limit')}}' class="form-horizontal" method="POST" id="deposit_limit_form">
          {!! csrf_field() !!}

          <input type="hidden" value="{{ $currency->id }}" name="currency_id" id="currency_id">
          <input type="hidden" value="{{ $transaction_type }}" name="transaction_type" id="transaction_type">
          <input type="hidden" value="{{ $list_menu }}" name="tabText" id="tabText">

          <input type="hidden" value="{{ $currency->default }}" name="defaultCurrency" id="defaultCurrency">

          <div class="box-body">

            <div class="col-md-11 col-md-offset-1">
              <div class="panel-group" id="accordion">
                @foreach($payment_methods as $key=>$method)
                    <input type="hidden" name="payment_method_id[]" value="{{ $method->id }}">
                    <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $method->id }}">
                        {{ isset($method->name) && $method->name == 'Mts' ? getCompanyName() : $method->name }}</a>
                      </h4>
                    </div>
                    <div id="collapse{{ $method->id }}" class="panel-collapse collapse">
                      <div class="panel-body">

                          <!-- has_transaction -->
                          <div class="form-group">
                          <label class="col-sm-3 control-label default_currency_label" for="has_transaction">Is Activated</label>
                          <div class="col-sm-5">
                              <label class="checkbox-container">
                                <input type="checkbox" class="has_transaction" data-method_id="{{ $method->id }}" name="has_transaction[{{ $method->id}}]" value="Yes" {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? 'checked' : '' }} {{ $currency->default == 1 ? 'disabled="disabled"' : ' ' }} id="has_transaction_{{ $method->id }}">
                                <span class="checkmark"></span>

                              @if ($errors->has('has_transaction'))
                                  <span class="help-block">
                                      <strong>{{ $errors->first('has_transaction') }}</strong>
                                  </span>
                              @endif
                         </div>
                         <div class="col-sm-4">
                              <p><span class="default_currency_side_text">Default currency is always active</span></p>
                          </div>
                        </div>
                        <div class="clearfix"></div>

                           <!-- Minimum Limit -->
                          <div class="form-group">
                                <label class="col-sm-3 control-label" for="min_limit">Minimum Limit</label>
                                <div class="col-sm-5">
                                  <input class="form-control min_limit" name="min_limit[]" type="text" value="{{ isset($method->fees_limit->min_limit) ? $method->fees_limit->min_limit : 1.00000000 }}"
                                  id="min_limit_{{ $method->id }}" {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }} >
                                  @if ($errors->has('min_limit'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('min_limit') }}</strong>
                                        </span>
                                  @endif
                                </div>
                                <div class="col-sm-4">
                                  <p>If not set, minimum limit is 1</p>
                                </div>
                          </div>
                          <div class="clearfix"></div>

                          <!-- Maximum Limit -->
                          <div class="form-group">
                                <label class="col-sm-3 control-label" for="max_limit">Maximum Limit</label>
                                <div class="col-sm-5">
                                    <input class="form-control max_limit" name="max_limit[]" type="text" value="{{ isset($method->fees_limit->max_limit) ? $method->fees_limit->max_limit : '' }}" id="max_limit_{{ $method->id }}" {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}>
                                    @if ($errors->has('max_limit'))
                                          <span class="help-block">
                                              <strong>{{ $errors->first('max_limit') }}</strong>
                                          </span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                  <p>If not set, maximum limit is infinity</p>
                                </div>
                          </div>
                          <div class="clearfix"></div>

                          <!-- Charge Percentage -->
                          <div class="form-group">
                                <label class="col-sm-3 control-label" for="charge_percentage">Charge Percentage</label>
                                <div class="col-sm-5">
                                    <input class="form-control charge_percentage" name="charge_percentage[]" type="text" value="{{ isset($method->fees_limit->charge_percentage) ? $method->fees_limit->charge_percentage : 0 }}" id="charge_percentage_{{ $method->id }}" {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}>
                                    @if ($errors->has('charge_percentage'))
                                          <span class="help-block">
                                              <strong>{{ $errors->first('charge_percentage') }}</strong>
                                          </span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                  <p>If not set, charge percentage is 0</p>
                                </div>
                          </div>
                          <div class="clearfix"></div>

                          <!-- Charge Fixed -->
                          <div class="form-group">
                                <label class="col-sm-3 control-label" for="charge_fixed">Charge Fixed</label>
                                <div class="col-sm-5">
                                    <input class="form-control charge_fixed" name="charge_fixed[]" type="text" value="{{ isset($method->fees_limit->charge_fixed) ? $method->fees_limit->charge_fixed : 0 }}" id="charge_fixed_{{ $method->id }}" {{ isset($method->fees_limit->has_transaction) && $method->fees_limit->has_transaction == 'Yes' ? '' : 'readonly' }}>
                                    @if ($errors->has('charge_fixed'))
                                          <span class="help-block">
                                              <strong>{{ $errors->first('charge_fixed') }}</strong>
                                          </span>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                  <p>If not set, charge fixed is 0</p>
                                </div>
                          </div>
                          <div class="clearfix"></div>




                      </div>
                    </div>
                    </div>

                @endforeach
              </div>
            </div>
          </div>

          <div class="box-footer">
              <a href="{{ url("admin/settings/currency") }}" class="btn btn-danger btn-flat">Cancel</a>
              <button type="submit" class="btn btn-primary btn-flat pull-right" id="deposit_limit_update">
                  <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="deposit_limit_update_text">Update</span>
              </button>
          </div>
        </form>

      </div>
    </div>
  </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

  if ($('#defaultCurrency').val() == 1)
  {
      $('.defaultCurrencyDiv').show();
  }
  else
  {
      $('.defaultCurrencyDiv').hide();
  }

  $('#deposit_limit_form').validate(
  {
      rules:
      {
          min_limit:
          {
              number: true,
          },
          max_limit:
          {
              number: true,
          },
          charge_percentage:
          {
              number: true,
          },
          charge_fixed:
          {
              number: true,
          },
          processing_time:
          {
              number: true,
          },
      },
      submitHandler: function(form)
      {
          $("#deposit_limit_update").attr("disabled", true);
          $(".spinner").show();
          $("#deposit_limit_update_text").text('Updating...');
          form.submit();
      }
  });

  $(".has_transaction").click(function()
  {
      var payment_method_id = $(this).data('method_id');
      if ($('#has_transaction_' + payment_method_id).prop('checked') == true)
      {
          $('#has_transaction_' + payment_method_id).val('Yes')
          $('#min_limit_' + payment_method_id).prop("readonly", false);
          $('#max_limit_' + payment_method_id).prop("readonly", false);
          $('#charge_percentage_' + payment_method_id).prop("readonly", false);
          $('#charge_fixed_' + payment_method_id).prop("readonly", false);
      }
      else
      {
          $('#has_transaction_' + payment_method_id).val('')
          $('#min_limit_' + payment_method_id).prop("readonly", true);
          $('#max_limit_' + payment_method_id).prop("readonly", true);
          $('#charge_percentage_' + payment_method_id).prop("readonly", true);
          $('#charge_fixed_' + payment_method_id).prop("readonly", true);
      }
  });

  //on load
  $(window).on('load', function()
  {
      var previousUrl = document.referrer;
      var urlByOwn = SITE_URL + '/admin/settings/currency';
      if (previousUrl == urlByOwn)
      {
          localStorage.removeItem('currencyId');
          localStorage.removeItem('currencyName');
          localStorage.removeItem('defaultCurrency');
      }
      else
      {
          if ((localStorage.getItem('currencyName')) && (localStorage.getItem('currencyId')) && (localStorage.getItem('defaultCurrency')))
          {
              $('.currencyName').text(localStorage.getItem('currencyName'));
              $('#currency_id').val(localStorage.getItem('currencyId'));
              $('#defaultCurrency').val(localStorage.getItem('defaultCurrency'));
              getFeesLimitDetails();
          }
          else
          {
              getSpecificCurrencyDetails();
          }
      }
  });

  //currency dropdown
  $('.listItem').on('click', function()
  {
      var currencyId = $(this).attr('data-rel');
      var currencyName = $(this).text();
      var defaultCurrency = $(this).attr('data-default');
      if (defaultCurrency == 1)
      {
          $('.defaultCurrencyDiv').show();
      }
      else
      {
          $('.defaultCurrencyDiv').hide();
      }
      localStorage.setItem('currencyId', currencyId);
      localStorage.setItem('currencyName', currencyName);
      localStorage.setItem('defaultCurrency', defaultCurrency);
      $('.currencyName').text(currencyName);
      $('#currency_id').val(currencyId);
      $('#defaultCurrency').val(defaultCurrency);
      getFeesLimitDetails();
  });

  //Window on load/click on list item get fees limit details
  function getFeesLimitDetails()
  {
      var currencyId = $('#currency_id').val();
      var checkDefaultCurrency = $('#defaultCurrency').val();
      var tabText = $('#tabText').val();
      var transaction_type = $('#transaction_type').val();
      var token = $("input[name=_token]").val();
      var url = SITE_URL + '/admin/settings/get-feeslimit-details';
      $.ajax(
      {
          url: url,
          type: "post",
          data:
          {
              'currency_id': currencyId,
              'transaction_type': transaction_type,
              '_token': token
          },
          dataType: 'json',
          success: function(data)
          {
              if (data.status == 200)
              {
                  var feesLimt = data.feeslimit;
                  if (checkDefaultCurrency == 1)
                  {
                      $('.defaultCurrencyDiv').show();
                      $('.default_currency_label').html('Is Activated');
                      $('.default_currency_side_text').text('Default currency is always active');
                      $(".has_transaction").prop('checked', true);
                      $(".has_transaction").prop('disabled', true);
                      $('.has_transaction').val('Yes');
                  }
                  else
                  {
                      $('.defaultCurrencyDiv').hide();
                      $('.default_currency_label').html('Is Activated');
                      $('.default_currency_side_text').text('');
                      $(".has_transaction").prop('checked', false);
                      $('.has_transaction').removeAttr('disabled');
                      $('.has_transaction').val('');
                  }
                  $.each(feesLimt, function(key, value)
                  {
                      // console.log(feesLimt);
                      if (value.fees_limit != null)
                      {
                          $('#min_limit_' + value.id).val(value.fees_limit.min_limit);
                          $('#max_limit_' + value.id).val(value.fees_limit.max_limit);
                          $('#charge_percentage_' + value.id).val(value.fees_limit.charge_percentage);
                          $('#charge_fixed_' + value.id).val(value.fees_limit.charge_fixed);
                          $('#has_transaction_' + value.id).val(value.fees_limit.has_transaction);
                          if (value.fees_limit.has_transaction == 'Yes')
                          {
                              $('#has_transaction_' + value.id).prop('checked', true);
                              $('#min_limit_' + value.id).prop("readonly", false);
                              $('#max_limit_' + value.id).prop("readonly", false);
                              $('#charge_percentage_' + value.id).prop("readonly", false);
                              $('#charge_fixed_' + value.id).prop("readonly", false);
                          }
                          else
                          {
                              $('#has_transaction_' + value.id).prop('checked', false);
                              $('#min_limit_' + value.id).prop("readonly", true);
                              $('#max_limit_' + value.id).prop("readonly", true);
                              $('#charge_percentage_' + value.id).prop("readonly", true);
                              $('#charge_fixed_' + value.id).prop("readonly", true);
                          }
                      }
                      else
                      {
                          $('#min_limit_' + value.id).val(1.00000000);
                          $('#max_limit_' + value.id).val('');
                          $('#charge_percentage_' + value.id).val(0.00000000);
                          $('#charge_fixed_' + value.id).val(0.00000000);
                          $('#has_transaction_' + value.id).prop('checked', false);
                          $('#min_limit_' + value.id).prop("readonly", true);
                          $('#max_limit_' + value.id).prop("readonly", true);
                          $('#charge_percentage_' + value.id).prop("readonly", true);
                          $('#charge_fixed_' + value.id).prop("readonly", true);
                      }
                  });
              }
          },
          error: function(error)
          {
              console.log(error);
          }
      });
  }

  // Get Specific Currency Details
  function getSpecificCurrencyDetails()
  {
      var currencyId       = $('#currency_id').val();
      var checkDefaultCurrency = $('#defaultCurrency').val();
      var transaction_type = $('#transaction_type').val();
      var token            = $("input[name=_token]").val();
      var tabText = $('#tabText').val();
      var url              = SITE_URL+'/admin/settings/get-specific-currency-details';
      $.ajax({
        url : url,
        type : "post",
        data : {
          'currency_id':currencyId,
          'transaction_type':transaction_type,
          '_token':token
        },
        dataType : 'json',
        success:function(data)
        {
          if(data.status == 200)
          {
            var feesLimt = data.feeslimit;
            if (checkDefaultCurrency == 1)
            {
              $('.defaultCurrencyDiv').show();
              $('.default_currency_label').html('Is Activated');
              $('.default_currency_side_text').text('Default currency is always active');
              $(".has_transaction").prop('checked', true);
              $('#has_transaction').attr('disabled', true);
              $('#has_transaction').val('Yes');
              $("#min_limit").prop("readonly", false);
              $("#max_limit").prop("readonly", false);
              $("#charge_percentage").prop("readonly", false);
              $("#charge_fixed").prop("readonly", false);
              if (tabText == 'bank_transfer')
              {
                $("#processing_time").prop("readonly", false);
              }
            }
            else
            {
              $('.defaultCurrencyDiv').hide();
              $('.default_currency_label').html('Is Activated');
              $('.default_currency_side_text').hide();
              $("#has_transaction").prop('checked', false);
              $('#has_transaction').removeAttr('disabled');
              $('.has_transaction').val('No');
              $("#min_limit").prop("readonly", true);
              $("#max_limit").prop("readonly", true);
              $("#charge_percentage").prop("readonly", true);
              $("#charge_fixed").prop("readonly", true);
              if (tabText == 'bank_transfer')
              {
                $("#processing_time").prop("readonly", true);
              }
            }
            $.each(feesLimt, function(key, value)
            {
                // console.log(feesLimt);
                if (value.fees_limit != null)
                {
                    $('#min_limit_' + value.id).val(value.fees_limit.min_limit);
                    $('#max_limit_' + value.id).val(value.fees_limit.max_limit);
                    $('#charge_percentage_' + value.id).val(value.fees_limit.charge_percentage);
                    $('#charge_fixed_' + value.id).val(value.fees_limit.charge_fixed);
                    $('#has_transaction_' + value.id).val(value.fees_limit.has_transaction);
                    if (value.fees_limit.has_transaction == 'Yes')
                    {
                        $('#has_transaction_' + value.id).prop('checked', true);
                        $('#min_limit_' + value.id).prop("readonly", false);
                        $('#max_limit_' + value.id).prop("readonly", false);
                        $('#charge_percentage_' + value.id).prop("readonly", false);
                        $('#charge_fixed_' + value.id).prop("readonly", false);
                    }
                    else
                    {
                        $('#has_transaction_' + value.id).prop('checked', false);
                        $('#min_limit_' + value.id).prop("readonly", true);
                        $('#max_limit_' + value.id).prop("readonly", true);
                        $('#charge_percentage_' + value.id).prop("readonly", true);
                        $('#charge_fixed_' + value.id).prop("readonly", true);
                    }
                }
                else
                {
                    $('#min_limit_' + value.id).val(1.00000000);
                    $('#max_limit_' + value.id).val('');
                    $('#charge_percentage_' + value.id).val(0.00000000);
                    $('#charge_fixed_' + value.id).val(0.00000000);
                    $('#has_transaction_' + value.id).prop('checked', false);
                    $('#min_limit_' + value.id).prop("readonly", true);
                    $('#max_limit_' + value.id).prop("readonly", true);
                    $('#charge_percentage_' + value.id).prop("readonly", true);
                    $('#charge_fixed_' + value.id).prop("readonly", true);
                }
            });
          }
          else
          {
            if (checkDefaultCurrency == 1)
            {
              $('.defaultCurrencyDiv').show();
              $('.default_currency_label').html('Is Activated');
              $('.default_currency_side_text').text('Default currency is always active');
              $('#has_transaction').removeAttr('disabled');
            }
            else
            {
              $('.defaultCurrencyDiv').hide();
              $('.default_currency_label').html('Is Activated');
              $('.default_currency_side_text').text('');
            }
            $('#id').val('');
            $('.currencyName').text(data.currency.name);
            $('#currency_id').val(data.currency.id);
            $(".has_transaction").prop('checked', false);
            $('.has_transaction').val('No');
            $('.min_limit').val('1.00000000');
            $('.max_limit').val('');
            $('.charge_percentage').val('0');
            $('.charge_fixed').val('0');
            if (tabText == 'bank_transfer')
            {
              $("#processing_time").prop("readonly", true);
            }
          }
        },
        error: function(error){
            console.log(error);
        }
      });
  }

</script>

@endpush

