@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">

                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    <form method="POST" action="{{url('transfer')}}" id="transfer_form" accept-charset='UTF-8'>

                        <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                        <input type="hidden" name="percentage_fee" id="percentage_fee" value="">
                        <input type="hidden" name="fixed_fee" id="fixed_fee" value="">
                        <input type="hidden" name="fee" class="total_fees" value="0.00">
                        <input type="hidden" name="sendMoneyProcessedBy" id="sendMoneyProcessedBy">

                        <div class="card">
                            <div class="card-header">
                                <div class="chart-list float-left">
                                    <ul>
                                        @if(Common::has_permission(auth()->id(),'manage_transfer'))
                                            <li class="active"><a href="{{url('/moneytransfer')}}">@lang('message.dashboard.send-request.menu.send')</a>
                                            </li>
                                        @endif

                                        @if(Common::has_permission(auth()->id(),'manage_request_payment'))
                                            <li>
                                                <a href="{{url('/request_payment/add')}}">@lang('message.dashboard.send-request.menu.request')</a>
                                            </li>
                                        @endif

                                       {{--  @if(Common::has_permission(auth()->id(),'manage_bank_transfer'))
                                            <li>
                                                <a href="{{url('/bank-transfer')}}">@lang('message.dashboard.send-request.send-to-bank.title')</a>
                                            </li>
                                        @endif --}}

                                    </ul>
                                </div>
                            </div>
                            <div class="wap-wed mt20 mb20">
                                <h3 class="ash-font">@lang('message.dashboard.send-request.send.title')</h3>
                                <hr>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.send-request.common.recipient')</label>
                                    <input type="text" class="form-control receiver" value="{{isset($transInfo['receiver'])?$transInfo['receiver']:''}}" name="receiver" id="receiver">
                                    <span class="receiverError"></span>
                                    <small id="emailHelp" class="form-text text-muted"></small>
                                </div>
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">@lang('message.dashboard.send-request.common.amount')</label>
                                            <input type="text" class="form-control amount" name="amount" placeholder="0.00" type="text" id="amount" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                            value="{{isset($transInfo['amount'])?$transInfo['amount']:''}}">
                                            <span class="amountLimit" style="color: red;font-weight: bold"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">@lang('message.dashboard.send-request.common.currency')</label>
                                            <select class="form-control wallet" name="wallet">
                                               <!--pm_v2.3-->
                                                @foreach($walletList as $result)
                                                        <option value="{{ $result->id }}" {{ $result->is_default == 'Yes' ? 'selected="selected"' : '' }}>{{ $result->active_currency->code }}</option>
                                                @endforeach
                                            </select>
                                            <small id="walletlHelp" class="form-text text-muted">
                                                @lang('message.dashboard.deposit.fee') (<span
                                                        class="pFees">0</span>%+<span class="fFees">0</span>)
                                                @lang('message.dashboard.deposit.total-fee') <span class="total_fees">0.00</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>@lang('message.dashboard.send-request.common.note')</label>
                                    <textarea class="form-control" rows="5" placeholder="@lang('message.dashboard.send-request.common.enter-note')" name="note" id="note">{{isset($transInfo['note'])?$transInfo['note']:''}}</textarea>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-cust col-12 transfer_form" id="send_money">
                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="send_text" style="font-weight: bolder;">@lang('message.dashboard.button.send-money')</span>
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
<script>
    var recipientErrorFlag = false;
    var amountErrorFlag = false;

    /**
    * [check submit button should be disabled or not]
    * @return {void}
    */
    function enableDisableButton()
    {
        if (!recipientErrorFlag && !amountErrorFlag)
        {
            $("#send_money").attr("disabled", false);
        }
        else
        {
            $("#send_money").attr("disabled", true);
        }
    }

    /**
     * [validateEmail description]
     * @param  {null} email [regular expression for email pattern]
     * @return {null}
     */
    function validateEmail(receiver) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(receiver);
    }

    function getStringAfterPlusSymbol(str)
    {
        return str.split('+')[1];
    }

    function checkMoneyProcessedBy()
    {
        $.ajax(
        {
            url: SITE_URL + "/check-processed-by",
            type: 'GET',
            data: {},
            dataType: 'json',
        })
        .done(function(response)
        {
            // console.log(response.processedBy);
            if (response.status == true)
            {
                if (response.processedBy == "email")
                {
                    $('#receiver').attr("placeholder", "{{__("Please enter valid email (ex: user@gmail.com)")}}");
                    $('#emailHelp').text("{{__("We will never share your email with anyone else.")}}");
                }
                else if (response.processedBy == "phone")
                {
                    $('#receiver').attr("placeholder", "{{__("Please enter valid phone (ex: +12015550123)")}}");
                    $('#emailHelp').text("{{__("We will never share your phone with anyone else.")}}");
                }
                else if (response.processedBy == "email_or_phone")
                {
                    $('#receiver').attr("placeholder", "{{__("Please enter valid email (ex: user@gmail.com) or phone (ex: +12015550123)")}}");
                    $('#emailHelp').text("{{__("We will never share your email or phone with anyone else.")}}");
                }
                $('#receiver').attr("data-processedBy", response.processedBy);
                $('#sendMoneyProcessedBy').val(response.processedBy);
            }
        })
        .fail(function(error)
        {
            console.log(error);
        });
    }

    function emailPhoneValidationCheck(emailOrPhone, sendOrRequestSubmitButton)
    {
        let processedBy = $('#receiver').attr('data-processedBy');
        // console.log(processedBy);
        if (emailOrPhone && emailOrPhone.length != 0)
        {
            let message = '';
            if (processedBy == "email")
            {
                // console.log('by email only');
                if (validateEmail(emailOrPhone))
                {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                    // sendOrRequestSubmitButton.attr("disabled", false);
                }
                else
                {
                    $('.receiverError').html("{{__("Please enter a valid email address.")}}").css({
                        'color': 'red',
                        'font-size': '14px',
                        'font-weight': '800',
                        'padding-top': '5px',
                    });
                    recipientErrorFlag = true;
                    enableDisableButton();
                    // sendOrRequestSubmitButton.attr("disabled", true);
                }
            }
            else if (processedBy == "phone")
            {
                // console.log('by phone only');
                if (emailOrPhone.charAt(0) != "+" || !$.isNumeric(getStringAfterPlusSymbol(emailOrPhone)))
                {
                    $('.receiverError').html("{{__("Please enter valid phone (ex: +12015550123)")}}").css({
                        'color': 'red',
                        'font-size': '14px',
                        'font-weight': '800',
                        'padding-top': '5px',
                    });
                    recipientErrorFlag = true;
                    enableDisableButton();
                    // sendOrRequestSubmitButton.attr("disabled", true);
                }
                else
                {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                    // sendOrRequestSubmitButton.attr("disabled", false);
                }
            }
            else if (processedBy == "email_or_phone")
            {
                if (emailOrPhone.charAt(0) != "+" || !$.isNumeric(getStringAfterPlusSymbol(emailOrPhone)))
                {
                    // if (emailOrPhone.includes("@"))
                    if (validateEmail(emailOrPhone))
                    {
                        $('.receiverError').html('');
                        recipientErrorFlag = false;
                        enableDisableButton();
                        // sendOrRequestSubmitButton.attr("disabled", false);
                    }
                    else
                    {
                         $('.receiverError').html("{{__("Please enter valid email (ex: user@gmail.com) or phone (ex: +12015550123)")}}")
                         .css({
                            'color': 'red',
                            'font-size': '14px',
                            'font-weight': '800',
                            'padding-top': '5px',
                        });
                        recipientErrorFlag = true;
                        enableDisableButton();
                        // sendOrRequestSubmitButton.attr("disabled", true);
                    }
                }
                else
                {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                    // sendOrRequestSubmitButton.attr("disabled", false);
                }
            }
        }
        else
        {
            $('.receiverError').html('');
            recipientErrorFlag = false;
            enableDisableButton();
            // sendOrRequestSubmitButton.attr("disabled", false);
        }
    }

    function checkReceiverEmailorPhone()
    {
        var token = $('#token').val();
        var receiver = $('.receiver').val();
        if (receiver != null)
        {
            $.ajax({
                method: "POST",
                url: SITE_URL + "/transferEmailOrPhoneValidate",
                dataType: "json",
                data: {
                    '_token': token,
                    'receiver': receiver
                }
            })
            .done(function (response)
            {
                if (response.status == true || response.status == 404)
                {
                    $('.receiverError').html(response.message).css({
                        'color': 'red',
                        'font-size': '14px',
                        'font-weight': '800',
                        'padding-top': '5px',
                    });
                    recipientErrorFlag = true;
                    enableDisableButton();
                }
                else
                {
                    $('.receiverError').html('');
                    recipientErrorFlag = false;
                    enableDisableButton();
                }
            });
        }
        else
        {
            $('.receiverError').html('');
        }
    }

    function checkAmountLimitAndFeesLimit()
    {
        var token = $("#token").val();
        var amount = $('#amount').val();
        var wallet_id = $('.wallet').val();

        if (amount.length === 0)
        {
            $('.amountLimit').hide();
        }
        else
        {
            $('.amountLimit').show();
            if (amount > 0 && wallet_id)
            {
                $.ajax({
                    method: "POST",
                    url: SITE_URL + "/amount-limit",
                    dataType: "json",
                    data: {
                        "_token": token,
                        'amount': amount,
                        'wallet_id': wallet_id,
                        'transaction_type_id':{{Transferred}}
                    }
                })
                .done(function (response)
                {
                    checkReceiverEmailorPhone();

                    //console.log(response.success.status);
                    if (response.success.status == 200)
                    {
                        $("#percentage_fee").val(response.success.feesPercentage);
                        $("#fixed_fee").val(response.success.feesFixed);
                        $(".percentage_fees").html(response.success.feesPercentage);
                        $(".fixed_fees").html(response.success.feesFixed);
                        $(".total_fees").val(response.success.totalFees);
                        $('.total_fees').html(response.success.totalFeesHtml);
                        $('.pFees').html(response.success.pFeesHtml);
                        $('.fFees').html(response.success.fFeesHtml);
                        $('.amountLimit').text('');
                        amountErrorFlag = false;
                        enableDisableButton();

                        //Not have enough balance - starts
                        if(response.success.totalAmount > response.success.balance)
                        {
                            $('.amountLimit').text("{{__("Not have enough balance !")}}");
                            amountErrorFlag = true;
                            enableDisableButton();
                        }
                        //Not have enough balance - ends
                    }
                    else
                    {
                        $('.amountLimit').text(response.success.message);
                        amountErrorFlag = true;
                        enableDisableButton();
                    }
                });
            }
        }
    }

    //Code for email and phone validation and Fees Limit  check
    $(window).on('load', function (e)
    {
        checkMoneyProcessedBy();
        let emailOrPhone    = $('#receiver').val();
        if (emailOrPhone != null)
        {
            emailPhoneValidationCheck(emailOrPhone, $("#send_money"));
        }
        checkAmountLimitAndFeesLimit();
    });

    //Code for email and phone validation
    $(document).on('input', ".receiver", function (e)
    {
        let emailOrPhone    = $('#receiver').val();
        if (emailOrPhone != null)
        {
            emailPhoneValidationCheck(emailOrPhone, $("#send_money"));
            checkReceiverEmailorPhone();
        }
    });

    // Code for Fees Limit  check
    $(document).on('input', '.amount', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    // Code for Fees Limit  check
    $(document).on('change', '.wallet', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    jQuery.extend(jQuery.validator.messages, {
        required: "{{__('This field is required.')}}",
        email: "{{__("Please enter a valid email address.")}}",
        maxlength: $.validator.format( "{{__("Please enter no more than")}}"+" {0} "+"{{__("characters.")}}" ),
    })

    $('#transfer_form').validate({
        rules: {
            amount: {
                required: true,
            },
            receiver: {
                required: true,
                // email: true,
            },
            note: {
                required: true,
                maxlength: 512,
            },
        },
        submitHandler: function (form)
        {
            var pretxt=$("#send_text").html();
            setTimeout(function()
            {
                $("#send_money").removeAttr("disabled");
                $(".spinner").hide();
                $("#send_text").html(pretxt);
            },1000);
            $("#send_money").attr("disabled", true);
            $(".spinner").show();
            $("#send_text").text('Sending...');
            form.submit();
        }
    });

</script>
@endsection