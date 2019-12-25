@extends('admin.layouts.master')

@section('title', 'Payout')

@section('page_content')
    <div class="box">
       <div class="panel-body">
            <ul class="nav nav-tabs cus" role="tablist">
                <li class="active">
                  <a href='{{url("admin/users/edit/$users->id")}}'>Profile</a>
                </li>

                <li>
                  <a href="{{url("admin/users/transactions/$users->id")}}">Transactions</a>
                </li>
                <li>
                  <a href="{{url("admin/users/wallets/$users->id")}}">Wallets</a>
                </li>
                <li>
                  <a href="{{url("admin/users/tickets/$users->id")}}">Tickets</a>
                </li>
                <li>
                  <a href="{{url("admin/users/disputes/$users->id")}}">Disputes</a>
                </li>
           </ul>
          <div class="clearfix"></div>
       </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button style="margin-top: 15px;"  type="button" class="btn button-secondary btn-flat active">Payout</button>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-4">
            <div class="pull-right">
                <h3>{{ $users->first_name.' '.$users->last_name }}</h3>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <form action="{{ url("admin/users/withdraw/create/$users->id") }}" method="post" accept-charset='UTF-8' id="admin-user-withdraw-create">
                        <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">

                        <input type="hidden" name="user_id" id="user_id" value="{{ $users->id }}">

                        <input type="hidden" name="fullname" id="fullname" value="{{ $users->first_name.' '.$users->last_name }}">

                        <input type="hidden" name="payment_method" id="payment_method" value="{{ $payment_met->id }}">

                        <input type="hidden" name="percentage_fee" id="percentage_fee" value="">
                        <input type="hidden" name="fixed_fee" id="fixed_fee" value="">
                        <input type="hidden" name="fee" class="total_fees" value="0.00">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Amount</label>
                                        <input type="text" class="form-control amount" name="amount" placeholder="0.00" type="text" id="amount" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" value="">
                                        <span class="amountLimit" style="color: red;font-weight: bold"></span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Currency</label>
                                        <select class="select2 wallet" name="currency_id" id="currency_id">
                                            @foreach ($wallets as $row)
                                                <option data-wallet="{{$row->id}}" value="{{ $row->active_currency->id }}">{{ $row->active_currency->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small id="walletlHelp" class="form-text text-muted">
                                        Fee(<span class="pFees">0</span>%+<span class="fFees">0</span>),
                                        Total:  <span class="total_fees">0.00</span>
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="col-md-5">
                                    <a href="{{ url('admin/users/edit/'. $users->id) }}" class="btn button-secondary"><span><i class="fa fa-angle-left"></i>&nbsp;Back</span></a>
                                    <button type="submit" class="btn button-secondary" id="send_money">
                                        <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i>
                                        <span id="send_text">Next&nbsp;<i class="fa fa-angle-right"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

    $(".select2").select2({});

    $('#admin-user-withdraw-create').validate({
        rules: {
            amount: {
                required: true,
            },
        },
        submitHandler: function (form)
        {
            $("#send_money").attr("disabled", true);
            $(".spinner").show();
            var pretext=$("#send_text").text();
            $("#send_text").text('Sending...');
            form.submit();
            setTimeout(function(){
                $("#send_text").html(pretext + '<i class="fa fa-angle-right"></i>');
                $("#send_money").removeAttr("disabled");
                $(".spinner").hide();
            },1000);

        }
    });

    $(window).on('load', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    $(document).on('input', '.amount', function (e) {
        checkAmountLimitAndFeesLimit();
    });
    $(document).on('change', '.wallet', function (e) {
        checkAmountLimitAndFeesLimit();
    });

    function checkAmountLimitAndFeesLimit()
    {
        var token = $("#token").val();
        var amount = $('#amount').val();
        log(amount);
        var currency_id = $('#currency_id').val();
        var payment_method_id = $('#payment_method').val();

        $.ajax({
            method: "POST",
            url: SITE_URL + "/admin/users/withdraw/amount-fees-limit-check",
            dataType: "json",
            data: {
                "_token": token,
                'amount': amount,
                'currency_id': currency_id,
                'payment_method_id': payment_method_id,
                'user_id': '{{ $users->id }}',
                'transaction_type_id': '{{ Withdrawal }}'
            }
        })
        .done(function (response)
        {
            // console.log(response);

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

                //Balance Checking
                if(response.success.totalAmount > response.success.balance)
                {
                    $('.amountLimit').text("Insufficient Balance");
                    $("#send_money").attr("disabled", true);
                }
                else
                {
                    $('.amountLimit').text('');
                    $("#send_money").attr("disabled", false);
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
                    $("#send_money").attr("disabled", true);
                    return false;
                }
            }
        });
    }

</script>
@endpush