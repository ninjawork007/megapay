@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')
                    <div class="card">
                        <div class="card-header">
                            <h4 class="float-left trans-inline">@lang('message.dashboard.exchange.left-top.title')</h4>
                        </div>
                        <div class="wap-wed mt20 mb20">

                            <div>
                                <form action="{{ url('/exchange_of_base_currency') }}" method="post"
                                      accept-charset="utf-8" id="exchange1_form">
                                    <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                                    <input type="hidden" name="percentage_fee" class="form-control percentage_fee"
                                           value="">
                                    <input type="hidden" name="fixed_fee" class="form-control fixed_fee" value="">
                                    <input type="hidden" name="fee" class="total_fees" value="0.00">


                                    <div class="form-group">
                                        <div class="col-md-8">
                                            <label>@lang('message.dashboard.exchange.left-top.type') <span>(@lang('message.dashboard.exchange.left-top.type-text') {{ $defaultCurrency->code }}) </span></label>
                                            <select name="type" id="exchange-type" class="form-control"
                                                    data-trans-currency-id="{{$transInfo['currency_id']}}">
                                                <option value="to-other">@lang('message.dashboard.exchange.left-top.to-other')</option>
                                                <option value="to-base">@lang('message.dashboard.exchange.left-top.to-base')</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <div class="col-md-8">
                                            <label id="wallet-label">@lang('message.dashboard.exchange.left-top.other-wallet')</label>
                                            <select class="form-control wallet" name="currency_id" id="wallets">
                                                @foreach($currencyList as $result)
                                                    @if($result['id'] != $defaultCurrency->id)
                                                        <option value="{{$result['id']}}"{{ $transInfo['currency_id'] == $result['id']? 'selected="selected"' : '' }}>{{$result['code']}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <small id="walletlHelp" class="form-text text-muted">
                                                <!--Fee (%) : <span class="percentage_fees">0.00</span>
                                               Fee ($) : <span class="fixed_fees">0.00</span>-->
                                                @lang('message.dashboard.deposit.fee')(<span
                                                        class="pFees">0</span>%+<span
                                                        class="fFees">0</span>)
                                                @lang('message.dashboard.deposit.total-fee') <span
                                                        class="total_fees">0.00</span>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-8">
                                            <label>@lang('message.dashboard.exchange.left-top.amount-exchange')
                                            </label>
                                            <input class="form-control amount1" name="amount" id="amounts"
                                                   onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                                   placeholder="0.00"
                                                   value="{{isset($transInfo['amount'])?$transInfo['amount']:''}}"
                                                   type="text">
                                            <span class="amountLimit1" style="color: red;font-weight: bold"></span>

                                            @if($errors->has('amount'))
                                                <span class="help-block">
					                      <strong class="text-danger">{{ $errors->first('amount') }}</strong>
					                    </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                    <div class="col-md-8">
                                        <div class="buttonPadding pull-right" style="margin-bottom: 10px;margin-top:-25px">

                                            <button type="submit" class="btn btn-cust" id="exchange1_money">
                                                <i class="spinner1 fa fa-spinner fa-spin" style="display: none;"></i> <span id="exchange1_text">@lang('message.dashboard.button.exchange')</span>

                                                {{-- <i class="spinner1 fa fa-spinner" id="lock-refresh" style="display: none;"></i>
                                                    <span id="exchange1_text">@lang('message.dashboard.button.exchange')</span> --}}
                                            </button>
                                        </div>
                                    </div>
                                    </div>
                                </form>
                            </div>
                            <div>
                            </div>

                        </div>
                    </div>

                    <!--
                    <div class="card">
                        <div class="card-header">
                            <h4 class="float-left trans-inline">@lang('message.dashboard.exchange.left-bottom.title')</h4>
                        </div>
                        <div class="wap-wed mt20 mb20">

                            <div>

                            </div>
                            <div>
                                <form action="{{ url('/exchange_to_base_currency') }}" method="post"
                                      accept-charset="utf-8" id="exchange2_form">
                                    <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                                    <input type="hidden" name="percentage_fee" id="percentage_fee" class="form-control"
                                           value="">
                                    <input type="hidden" name="fixed_fee" id="fixed_fee" class="form-control" value="">
                                    <input type="hidden" name="fee" id="total_fees" value="0.00">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>@lang('message.dashboard.exchange.left-bottom.exchange-to-base')
                        , <?php echo e($defaultCurrency->code); ?></label>
                                                <input class="form-control amount2" name="amount" id="amount"
                                                       onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                                       placeholder="0.00"
                                                       value="{{isset($transInfo['amount'])?$transInfo['amount']:''}}"
                                                       type="text">
                                                <span class="amountLimit2" style="color: red;font-weight: bold"></span>
                                                @if($errors->has('amount'))
                    <span class="help-block">
          <strong class="text-danger">{{ $errors->first('amount') }}</strong>
                                        </span>
                                                @endif

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>@lang('message.dashboard.exchange.left-bottom.wallet')</label>
                                                <select class="form-control wallet2" name="currency_id">
                                                    @foreach(@$userCurrencyList as $result)
                    @if($result['id'] != $defaultCurrency->id)
                        <option value="{{$result['id']}}"{{ $transInfo['currency_id'] == $result['id']? 'selected="selected"' : '' }}>{{$result['code']}}</option>
                                                        @endif
                @endforeach
                        </select>
                        <small id="walletlHelp" class="form-text text-muted">

                            <!--Fee (%) : <span class="percentage_fees">0.00</span>
                            Fee ($) : <span class="fixed_fees">0.00</span>-->
                <!--
                                                    @lang('message.dashboard.deposit.fee') (<span
                                                            class="pFees2">0</span>%+<span class="fFees2">0</span>)
                                                    @lang('message.dashboard.deposit.total-fee') <span
                                                            class="total_fees2">0.00</span>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="pull-right buttonPadding">
                                                <button type="submit" class="btn btn-cust" id="exchange2_money">
                                                    <i class="spinner2 fa fa-spinner fa-spin"
                                                       style="display: none;"></i> <span
                                                            id="exchange2_text">@lang('message.dashboard.button.exchange')</span>
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                        <div class="card-footer">
                        </div>
                    </div>
        -->
                </div>
            <!--
                {{--
                <div class="col-md-5 col-xs-12 mb20 marginTopPlus">

                    <div class="card">
                        <div class="card-header">
                            <h4 class="float-left trans-inline">@lang('message.dashboard.exchange.right.title')</h4>
                        </div>
                        <div class="card-body" style="overflow: auto;">

                    <div class="row">
                        @foreach($currencies as $result)
                            <div class="col-md-6">
                                <strong>{{ $defaultCurrency->rate.' ' .$defaultCurrency->code}} </strong>= {{ $result['rate'].' '.$result['code'] }}
                            </div>
                        @endforeach
                    </div>

                        </div>
                    </div>

                </div>
                --}}
                    -->

            </div>
        </div>
    </section>
@endsection


@section('js')
<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">

    $( '#lock-refresh' ).click(function() {
            $(this).addClass("fa-spin").delay(1000).queue('fx', function() { $(this).removeClass('fa-spin'); });
            startup();
            // Whatever here.
     });

    jQuery.extend(jQuery.validator.messages, {
        required: "{{__('This field is required.')}}",
    })

    $('#exchange1_form').validate({
        rules: {
            amounts: {
                required: true,
            },
        },
        submitHandler: function (form) {
            $("#exchange1_money").attr("disabled", true);
            $(".spinner").show();
            var pretext=$("#exchange1_text").text();
            $("#exchange1_text").text('Exchange..');
            form.submit();
            setTimeout(function(){
                $("#exchange1_text").text(pretext);
                $("#exchange1_money").removeAttr("disabled");
                $(".spinner").hide();
            },1000);
        }
    });
    $('#exchange2_form').validate({
        rules: {
            amount: {
                required: true,
            },
        },
        submitHandler: function (form) {
            $("#exchange2_money").attr("disabled", true);
            $(".spinner2").show();
            $("#exchange2_text").text('Exchange...');
            form.submit();
        }
    });
    //Code for Exchange of Base Currency Amount And Fees Limit Check ends here

    // Code for Amount Limit  check when window load
    $(window).on('load', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    // Code for Amount Limit  check
    $(document).on('input', '.amount1', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    // Code for Fees Limit  check
    $(document).on('change', '.wallet', function (e) {
        checkAmountLimitAndFeesLimit();
    });
    function checkAmountLimitAndFeesLimit() {
        var token = $("#token").val();
        var amount = $('.amount1').val();
        var currency_id = $('.wallet').val();
        var ex_type=$('#exchange-type option:selected').val();
        console.log(ex_type);
        if(ex_type=='to-other'){
            currency_id='{{session('default_currency')}}';
        }
console.log(currency_id);
        $.ajax({
            method: "POST",
            url: SITE_URL + "/exchange/amount-limit-check",
            dataType: "json",
            data: {
                "_token": token,
                'amount': amount,
                'currency_id': currency_id,
                'transaction_type_id':{{Exchange_From}} }
            })
            .done(function (response) {
                //console.log(response.success.status);
                if (response.success.status == 200)
                {
                    $(".percentage_fee").val(response.success.feesPercentage);
                    $(".fixed_fee").val(response.success.feesFixed);
                    $(".percentage_fees").html(response.success.feesPercentage);
                    $(".fixed_fees").html(response.success.feesFixed);

                    $(".total_fees").val(response.success.totalFees);
                    $('.total_fees').html(response.success.totalFeesHtml);

                    $('.pFees').html(response.success.pFees);
                    $('.fFees').html(response.success.fFees);

                    //checking wallet balance - added by parvez
                    if(response.success.totalAmount > response.success.balance)
                    {
                            $('.amountLimit1').html("{{__("Not have enough balance !")}}");
                            $('#exchange1_money').attr('disabled', true);
                    }else {
                        $('.amountLimit1').html('');
                        $('#exchange1_money').removeAttr('disabled');
                    }
                }
                else
                {
                    if (amount == '')
                    {
                        $('.amountLimit1').text('');
                    }
                    else
                    {
                        $('.amountLimit1').text(response.success.message);
                        $('.text-danger').text('');
                    }
                    $('#exchange1_money').attr('disabled', true);
                    return false;
                }
            });
    }


    //Extra portion below -----------------------------------------------------------------

    //Code for Exchange of Base Currency Amount And Fees Limit Check ends here
    // Code for Amount Limit  check when window load
    $(window).on('load', function (e) {
        checkAmountLimitAndFeesLimitBaseCurrency();
    });

    // Code for Amount Limit  check
    $(document).on('input', '.amount2', function (e) {
        checkAmountLimitAndFeesLimitBaseCurrency();
    });

    // Code for Fees Limit  check
    $(document).on('change', '.wallet2', function (e) {
        checkAmountLimitAndFeesLimitBaseCurrency();
    });

    function checkAmountLimitAndFeesLimitBaseCurrency() {
        var token = $("#token").val();
        var amount = $('.amount2').val();
        var currency_id = $('.wallet2').val();

        //alert(amount);
        //alert(wallet_id);
        $.ajax({
            method: "POST",
            url: SITE_URL + "/exchange/amount-limit-check",
            dataType: "json",
            data: {
                "_token": token,
                'amount': amount,
                'currency_id': currency_id,
                'transaction_type_id':{{Exchange_From}} }
        })
        .done(function (response) {
            //console.log(response.success.status);
            if (response.success.status == 200) {
                $("#percentage_fee").val(response.success.feesPercentage);
                $("#fixed_fee").val(response.success.feesFixed);

                $('#total_fees').val(response.success.totalFees);
                $('.total_fees2').html(response.success.totalFeesHtml);

                $('.pFees2').html(response.success.pFees);
                $('.fFees2').html(response.success.fFees);
                $('.amountLimit2').text('');
                return true;
            } else {
                if (amount == '') {
                    $('.amountLimit2').text('');
                } else {
                    $('.amountLimit2').text(response.success.message);
                    $('.text-danger').text('');
                    return false;
                }
            }
        });
    }

    $('#exchange-type').on('change', function ()
    {
        var type = $(this).val();
        var form = $('#exchange1_form');
        var wallet_label = $('#wallet-label');
        if (type == 'to-base')
        {
            wallet_label.text('{{trans('message.dashboard.exchange.left-top.base-wallet')}}');
            form.attr('action', '{{url('exchange_to_base_currency')}}');
        }
        else {
            wallet_label.text('{{trans('message.dashboard.exchange.left-top.other-wallet')}}');
            form.attr('action', '{{url('/exchange_of_base_currency')}}');
        }
        var currency_id = $(this).attr('data-trans-currency-id');
        $.ajax({
            url: '{{url('get-wallets')}}',
            method: 'get',
            data: {type: type},
            success: function (res) {
                var options = '';
                Object.keys(res).forEach(function (index) {
                    options += `<option ${res[index].id == currency_id ? 'selected' : ''} value="${res[index].id}">${res[index].code}</option>`;
                });
                $('#wallets').html(options);
            }
        });
    });
</script>

@endsection
