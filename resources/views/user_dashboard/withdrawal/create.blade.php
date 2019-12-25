@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    <form action="{{ url('payout') }}" style="display: block;" method="POST"
                          accept-charset='UTF-8' id="payout_form">
                        <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="payment_method_id" id="payment_method_id">

                        <div class="card">
                            <div class="card-header">
                                <div class="chart-list float-left">
                                    <ul>
                                        <h3>@lang('message.dashboard.payout.new-payout.title')</h3>
                                    </ul>
                                </div>
                            </div>
                            <div class="wap-wed mt20 mb20">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label>@lang('message.dashboard.payout.new-payout.payment-method')</label>
                                            <select class="form-control" name="payout_setting_id" id="method">
                                                @foreach ($payment_methods as $method)
                                                    @if($method->type =='3')
                                                        <option data-obj="{{json_encode($method->getAttributes())}}" value="{{ $method->id }}" data-type="{{ $method->type }}">
                                                            {{$method->paymentMethod->name}} ({{ $method->email }})
                                                        </option>
                                                    @elseif($method->type == '6')
                                                        <option data-obj="{{json_encode($method->getAttributes())}}" value="{{ $method->id }}" data-type="{{ $method->type }}">
                                                            {{$method->paymentMethod->name}} ({{ $method->account_name }})
                                                        </option>
                                                    @else
                                                        <option data-obj="{{json_encode($method->getAttributes())}}" value="{{ $method->id }}" data-type="{{ $method->type }}">
                                                            {{$method->paymentMethod->name}} ({{ $method->account_number }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>@lang('message.dashboard.payout.new-payout.currency')</label>

                                            <select class="form-control" name="currency_id" id="currency_id">
                                                {{-- @foreach ($wallets as $row)
                                                    <option value="{{ $row->currency->id }}">{{ $row->currency->code }}</option>
                                                @endforeach --}}
                                            </select>

                                            <small id="walletHelp" class="form-text text-muted">
                                                @lang('message.dashboard.deposit.fee') (<span class="pFees">0</span>%+<span class="fFees">0</span>)
                                                @lang('message.dashboard.deposit.total-fee') <span class="total_fees">0.00</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.new-payout.amount')</label>
                                    <input class="form-control" name="amount" id="amount" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" placeholder="0.00" type="text" value="">
                                    <span class="amountLimit" id="amountLimit" style="color: red;font-weight: bold"></span>
                                </div>

                                <div class="form-group" id="bank" style="display: none;">
                                    <label>@lang('message.dashboard.payout.new-payout.bank-info')</label>
                                    <span id="bank_info_input"></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-cust col-12" id="send_money">
                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="send_text" style="font-weight: bolder;">@lang('message.dashboard.button.submit')</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>
    </section>

@endsection

@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/sweetalert/sweetalert-unpkg.min.js')}}" type="text/javascript"></script>

<script>

    // $(function()
    $(window).on('load',function()
    {
        var previousUrl = localStorage.getItem("payoutConfirmPreviousUrl");
        var confirmationUrl = SITE_URL + '/payout';
        if (confirmationUrl == previousUrl)
        {
            var payoutPaymentMethodId = localStorage.getItem('payoutPaymentMethodId');
            var currency_id = localStorage.getItem('currency_id');
            var pFees = localStorage.getItem('pFees');
            var fFees = localStorage.getItem('fFees');
            var total_fees_html = localStorage.getItem('total_fees_html');

            if (payoutPaymentMethodId && currency_id && total_fees_html && pFees && fFees)
            {
                swal('Please Wait', 'Loading...', {
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    buttons: false,
                });
                setTimeout(function(payoutPaymentMethodId, currency_id, total_fees_html, pFees, fFees)
                {
                    $('#payment_method').val(payoutPaymentMethodId);
                    $('#currency_id').val(currency_id);
                    $(".total_fees").html(total_fees_html);
                    $(".total_fees").html(total_fees_html);
                    $('.pFees').html(pFees);
                    $('.fFees').html(fFees);
                    swal.close();
                }, 1300, payoutPaymentMethodId, currency_id, total_fees_html, pFees, fFees);
                removePayoutLocalStorageValues();
            }
        }
        else
        {
            setTimeout(function()
            {
                removePayoutLocalStorageValues();
            }, 1300);
        }

        var paymentMethodId = JSON.parse($('option:selected','#method').attr('data-type'));
        getFeesLimitsPaymentMethodsCurrencies(paymentMethodId);
        withdrawalAmountLimitCheck(paymentMethodId);

        //bug fixed - after giving paymoney 1.9 on march,2019
        var paymentMethodObject = JSON.parse($('option:selected','#method').attr('data-obj'));
        if(paymentMethodObject.email!=null)
        {
            var p = '<input value="' + paymentMethodObject.email + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        }
        else if(paymentMethodObject.account_name!=null)
        {
            var p = '<input value="' + paymentMethodObject.account_name + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        }
         else if(paymentMethodObject.account_number!=null)
        {
            var p = '<input value="' + paymentMethodObject.account_number + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
        }
        $('#bank_info_input').html(p);
        //bug fix finished
    });

    $(document).ready(function()
    {
        $("#method").on('change', function ()
        {
            $("#bank").css("display", "none");

            var paymentMethodObject = JSON.parse($('option:selected','#method').attr('data-obj'));
            if(paymentMethodObject.email!=null)
            {
                var p = '<input value="' + paymentMethodObject.email + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
            else if(paymentMethodObject.account_name!=null)
            {
                var p = '<input value="' + paymentMethodObject.account_name + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
             else if(paymentMethodObject.account_number!=null)
            {
                var p = '<input value="' + paymentMethodObject.account_number + '" type="text" name="payment_method_info" class="form-control" id="payment_method_info">';
            }
            $('#bank_info_input').html(p);

            var paymentMethodId = JSON.parse($('option:selected','#method').attr('data-type'));
            getFeesLimitsPaymentMethodsCurrencies(paymentMethodId);
            withdrawalAmountLimitCheck(paymentMethodId);
        });

        $('#currency_id, #amount').on('change keyup', function (e)
        {
            var paymentMethodId = JSON.parse($('option:selected','#method').attr('data-type'));
            withdrawalAmountLimitCheck(paymentMethodId);
        });
    });

    function getFeesLimitsPaymentMethodsCurrencies(paymentMethodId)
    {
        $('#payment_method_id').val(paymentMethodId);
        var token = $('#_token').val();
        $.ajax({
            method: 'post',
            url: SITE_URL + "/withdrawal/fees-limit-payment-method-isActive-currencies",
            data: {
                "_token": token,
                'transaction_type_id': '{{Withdrawal}}',
                'payment_method_id': paymentMethodId,
            },
            dataType: "json",
            success: function (response)
            {
                // log(response.success.currencies);
                let options = '';
                $.map(response.success.currencies, function(value, index)
                {
                    // console.log(value);
                    options += `<option value="${value.id}" ${value.default_wallet == 'Yes' ? 'selected="selected"': ''}>${value.code}</option>`; //pm_v2.3
                });
                $('#currency_id').html(options);
            }
        });
    }

    function withdrawalAmountLimitCheck(paymentMethodId)
    {
        $('#payment_method_id').val(paymentMethodId);
        var amount = $('#amount').val();

        var currency_id = $('#currency_id').val();
        if (currency_id == '')
        {
            $('#walletHelp').hide();
        }
        else
        {
            $('#walletHelp').show();
        }

        if (currency_id && amount)
        {
            var token = $('#_token').val();

            $.ajax({
                method: 'post',
                url: SITE_URL + "/withdrawal/amount-limit",
                data: {
                    "_token": token,
                    'payment_method_id': paymentMethodId,
                    'currency_id': currency_id,
                    'transaction_type_id': '{{Withdrawal}}',
                    'amount': amount,
                },
                dataType: "json",
                success: function (res)
                {
                    if (res.success.status == 200)
                    {
                        $('.total_fees').html(res.success.totalHtml);
                        $('.pFees').html(res.success.pFeesHtml);
                        $('.fFees').html(res.success.fFeesHtml);

                        //checking balance
                        if(res.success.totalAmount > res.success.balance){
                            $('#amountLimit').html("{{__("Not have enough balance !")}}");
                            $('#send_money').attr('disabled', true);
                        }else {
                            $('#amountLimit').html('');
                            $('#send_money').removeAttr('disabled');
                        }
                    }
                    else
                    {
                        if (amount == '')
                        {
                            $('#amountLimit').text('');
                        }
                        else
                        {
                            $('#amountLimit').text(res.success.message);
                        }

                        $('#send_money').attr('disabled', true);
                        return false;
                    }
                     // $('#amount').focus();
                }
            });
        }
    }

    function removePayoutLocalStorageValues()
    {
        localStorage.removeItem('payoutPaymentMethodId');
        localStorage.removeItem('payoutPaymentMethodId');
        localStorage.removeItem('pFees');
        localStorage.removeItem('fFees');
        localStorage.removeItem('total_fees_html');
    }

    jQuery.extend(jQuery.validator.messages, {
      required: "{{__('This field is required.')}}",
    })


    $('#payout_form').validate({
        rules: {
            amount: {
                required: true
            },
            currency_id: {
                required: true
            },
            payout_setting_id:{
                required:true
            }
        },
        submitHandler: function (form)
        {

            //set values to localStorage
            var payoutPaymentMethodId = JSON.parse($('option:selected','#method').attr('data-type'));
            localStorage.setItem("payoutPaymentMethodId", payoutPaymentMethodId);

            var currency_id = $('#currency_id').val();
            localStorage.setItem("currency_id", currency_id);

            var pFees = $('.pFees').html();
            localStorage.setItem("pFees", pFees);

            var fFees = $('.fFees').html();
            localStorage.setItem("fFees", fFees);

            var total_fees_html = $(".total_fees").html();
            localStorage.setItem("total_fees_html", total_fees_html);
            //

            $("#send_money").attr("disabled", true);
            $(".spinner").show();
            var pretext=$("#send_text").text();
            $("#send_text").text('Submitting...');
            form.submit();
            setTimeout(function(){
                $("#send_money").removeAttr("disabled");
                $(".spinner").hide();
                $("#send_text").text(pretext);
            },1000);
        }
    });

</script>

@endsection