@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">

                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')
                    <form method="POST" action="{{url('bank-transfer/confirm')}}" id="bank_transfer_form" accept-charset='UTF-8'>
                        <div class="card">
                            <div class="card-header">
                                <div class="chart-list float-left">
                                    <ul>
                                        @if(Common::has_permission(auth()->id(),'manage_transfer'))
                                            <li><a href="{{url('/moneytransfer')}}">@lang('message.dashboard.send-request.menu.send')</a>
                                            </li>
                                        @endif

                                        @if(Common::has_permission(auth()->id(),'manage_request_payment'))
                                            <li>
                                                <a href="{{url('/request_payment/add')}}">@lang('message.dashboard.send-request.menu.request')</a>
                                            </li>
                                        @endif

                                        @if(Common::has_permission(auth()->id(),'manage_bank_transfer'))
                                            <li class="active">
                                                <a href="{{url('/bank-transfer')}}">@lang('message.dashboard.send-request.send-to-bank.title')</a>
                                            </li>
                                        @endif

                                    </ul>
                                </div>
                            </div>
                            <div class="wap-wed mt20 mb20">
                                <h3 class="ash-font">@lang('message.dashboard.send-request.send-to-bank.subtitle')</h3>
                                <hr>
                                <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                                <input type="hidden" name="percentage_fee" id="percentage_fee" class="form-control"
                                       value="">
                                <input type="hidden" name="fixed_fee" id="fixed_fee" class="form-control" value="">
                                <input type="hidden" name="fee" class="total_fees" value="0.00">

                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">@lang('message.dashboard.send-request.common.amount')</label>
                                            <input type="text" class="form-control amount" name="amount"
                                                   placeholder="0.00" type="text" id="amount"
                                                   onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                                   value="{{isset($transInfo['amount'])?$transInfo['amount']:''}}">
                                            <span class="amountLimit" style="color: red;font-weight: bold"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">@lang('message.dashboard.send-request.common.currency')</label>
                                            <select class="form-control wallet" name="wallet">
                                                @foreach($walletList as $result)
                                                        <option value="{{$result['id']}}"{{ $transInfo['wallet'] == $result['id']? 'selected="selected"' : '' }}>{{ $result['currency_code'] }}</option>
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

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-cust col-12 bank_transfer_form" id="send_money">
                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span
                                            id="send_text">@lang('message.dashboard.button.send-money')</span>
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

    jQuery.extend(jQuery.validator.messages, {
        required: "{{__('This field is required.')}}",
        email: "{{__("Please enter a valid email address.")}}",
    })

    $('#bank_transfer_form').validate({
        rules: {
            amount: {
                required: true,
            },
            receiver: {
                required: true,
                email: true,
            },
        },
        submitHandler: function (form) {
            $("#send_money").attr("disabled", true);
            $(".spinner").show();
            $("#send_text").text('Sending...');
            var pretxt=$("#send_text").text();
            form.submit();
            setTimeout(function(){
                $("#send_money").removeAttr("disabled");
                $(".spinner").hide();
                $("#send_text").text(pretxt);
            },2000);
        }
    });


    // Code for Amount Limit  check when window load
    $(window).on('load', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    // Code for Amount Limit  check
    $(document).on('input', '.amount', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    // Code for Fees Limit  check
    $(document).on('change', '.wallet', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    function checkAmountLimitAndFeesLimit()
    {
        var token = $("#token").val();
        var amount = $('#amount').val();
        var wallet_id = $('.wallet').val();
        $.ajax({
            method: "POST",
            url: SITE_URL + "/bank-transfer/amount-limit",
            dataType: "json",
            data: {
                "_token": token,
                'amount': amount,
                'wallet_id': wallet_id,
                'transaction_type_id':{{Bank_Transfer}} }
        })
        .done(function (response)
        {
            if (response.success.status == 200)
            {
                $("#percentage_fee").val(response.success.feesPercentage);
                $("#fixed_fee").val(response.success.feesFixed);
                $(".percentage_fees").html(response.success.feesPercentage);
                $(".fixed_fees").html(response.success.feesFixed);

                $(".total_fees").val(response.success.totalFees);
                $('.total_fees').html(response.success.totalFeesHtml);

                $('.pFees').html(response.success.pFees);
                $('.fFees').html(response.success.fFees);
                if(response.success.totalAmount > response.success.balance){
                    $('.amountLimit').text("{{__("Not have enough balance !")}}");
                    $('#send_money').attr('disabled','true');
                }else{
                    $('#send_money').removeAttr('disabled');
                    $('.amountLimit').text('');
                }
                return true;
            }
            else
            {
                if (amount == '')
                {
                    $('.amountLimit').text('');
                }
                else
                {
                    $('.amountLimit').text(response.success.message);
                    return false;
                }
            }
        });
    }
</script>
@endsection