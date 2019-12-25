@extends('user_dashboard.layouts.app')

@section('css')
    <!--daterangepicker-->
    <link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/daterangepicker.css')}}">
@endsection

@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-xs-12 col-sm-12 mb20 marginTopPlus">
                    <div class="flash-container">
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="float-left trans-inline">@lang('message.dashboard.nav-menu.transactions')</h4>
                        </div>

                        <div style="margin: 15px 15px 15px 10px;">

                            <form action="" method="get">
                                <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
                                <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
                                <div class="">
                                    <div class="filter_panel">
                                        <div class="daterange_btn" id="daterange-btn" style="width: 100%;">
                                            <span id="drp" style="text-align: left; "><i class="fa fa-calendar"></i> @lang('message.dashboard.transaction.date-range')</span>
                                        </div>
                                    </div>
                                    <div class="filter_panel">
                                        <select class="form-control" id="type" name="type">

                                            <option value="all" <?= ($type == 'all') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.all-trans-type')
                                            </option>

                                            <option value="{{Deposit}}" <?= ($type == Deposit) ? 'selected' : '' ?>>
                                                @lang('message.dashboard.button.deposit')
                                            </option>

                                            <option value="{{Withdrawal}}" <?= ($type == Withdrawal) ? 'selected' : '' ?>>
                                                @lang('message.dashboard.button.withdraw')
                                            </option>

                                            <option value="sent" <?= ($type == 'sent') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.payment-sent')
                                            </option>

                                            <option value="request" <?= ($type == 'request') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.payment-req')
                                            </option>

                                            <option value="received" <?= ($type == 'received') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.payment-receive')
                                            </option>

                                            <option value="exchange" <?= ($type == 'exchange') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.exchanges')
                                            </option>

                                            <!--<option value="voucher" <?= ($type == 'voucher') ? 'selected' : '' ?>
                                                @lang('message.dashboard.nav-menu.vouchers')
                                            </option> -->

                                        </select>
                                    </div>
                                    <div class="filter_panel">
                                        <select class="form-control" id="status" name="status">
                                            <option value="all" <?= ($status == 'all') ? 'selected' : '' ?>>@lang('message.dashboard.transaction.all-status')
                                            </option>
                                            <option value="Success" <?= ($status == 'Success') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.success')
                                            </option>
                                            <option value="Pending" <?= ($status == 'Pending') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.pending')
                                            </option>
                                            <option value="Refund" <?= ($status == 'Refund') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.refund')
                                            </option>
                                            <option value="Blocked" <?= ($status == 'Blocked') ? 'selected' : '' ?>>
                                                @lang('message.dashboard.transaction.blocked')
                                            </option>
                                        </select>
                                    </div>
                                    <div class="filter_panel">
                                        <select class="form-control" id="wallet" name="wallet">
                                            <option value="all" <?= ($wallet == 'all') ? 'selected' : '' ?>>@lang('message.dashboard.transaction.all-currency')
                                            </option>
                                            @foreach($wallets as $res)
                                                <option value="{{$res->currency->id}}" <?= ($res->currency_id == $wallet) ? 'selected' : '' ?>>{{$res->currency->code}} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="">
                                        <button type="submit" class="btn btn-cust">@lang('message.dashboard.button.filter')</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                        <div>
                            <div class="table-responsive">
                                <table class="table recent_activity" align="left">
                                    <thead>
                                    <tr>
                                        <td></td>
                                        <td class="text-left" width="10%">
                                            <strong>@lang('message.dashboard.left-table.date')</strong></td>
                                        <td class="text-left"><strong>&nbsp;</strong></td>
                                        <td class="text-left">
                                            <strong>@lang('message.dashboard.left-table.description')</strong></td>
                                        <td class="text-left">
                                            <strong>@lang('message.dashboard.left-table.status')</strong></td>
                                        <td class="text-left">
                                            <strong>@lang('message.dashboard.left-table.currency')</strong></td>
                                        <td class="text-left"><strong>@lang('message.dashboard.left-table.fee')</strong>
                                        </td>
                                        <td class="text-left">
                                            <strong>@lang('message.dashboard.left-table.amount')</strong></td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if($transactions->count()>0)
                                        @foreach($transactions as $key=>$transaction)

                                            <tr click="0" data-toggle="collapse" data-target="#collapseRow{{$key}}"
                                                aria-expanded="false" aria-controls="collapseRow{{$key}}"
                                                class="show_area" trans-id="{{$transaction->id}}" id="{{$key}}">
                                                <td class="text-center arrow-size">
                                                    <strong>
                                                        <i class="fa fa-arrow-circle-right text-blue"
                                                           id="icon_{{$key}}"></i>
                                                    </strong>

                                                </td>
                                                <td class="text-left date_td" width="10%">{{ dateFormat($transaction->created_at) }}</td>

                                                    @if(empty($transaction->merchant_id))

                                                        @if($transaction->end_user_id)
                                                            @if(!empty($transaction->end_user->picture))
                                                                <td class="text-left">
                                                                    <img src="{{url('public/user_dashboard/profile/thumb/'.$transaction->end_user->picture)}}"
                                                                         class="rounded-circle rounded-circle-custom-trans">
                                                                </td>
                                                            @else
                                                                <td class="text-left">
                                                                    <img src="{{url('public/user_dashboard/images/avatar.jpg')}}" class="rounded-circle rounded-circle-custom-trans">
                                                                </td>
                                                            @endif
                                                            <td class="text-left">
                                                                @if($transaction->transaction_type_id)
                                                                    @if($transaction->transaction_type_id==Request_From)

                                                                        <p>{{ $transaction->end_user->first_name.' '.$transaction->end_user->last_name }}</p>
                                                                        <p>@lang('Request Sent')</p>

                                                                    @elseif($transaction->transaction_type_id==Request_To)

                                                                        <p>{{ $transaction->end_user->first_name.' '.$transaction->end_user->last_name }}</p>
                                                                        <p>@lang('Request Received')</p>

                                                                    @elseif($transaction->transaction_type_id == Transferred)
                                                                        <p>
                                                                            {{ $transaction->end_user->first_name.' '.$transaction->end_user->last_name }}
                                                                        </p>
                                                                        <p>@lang('Transferred')</p>
                                                                    @elseif($transaction->transaction_type_id == Received)
                                                                        <p>
                                                                            {{ $transaction->end_user->first_name.' '.$transaction->end_user->last_name }}
                                                                        </p>
                                                                        <p>@lang('Received')</p>
                                                                    @else
                                                                        <p>{{ __(str_replace('_',' ',$transaction->transaction_type->name))}}</p>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                        @else

                                                            <?php
                                                                //$payment_method = isset($transaction->payment_method->name) ? $transaction->payment_method->name : '';
                                                                //$img = strtolower($payment_method) . '.jpg';
                                                            ?>

                                                            <?php
                                                                if (isset($transaction->payment_method->name))
                                                                {
                                                                    if ($transaction->payment_method->name == 'Mts')
                                                                    {
                                                                        //$payment_method = 'Pay Money';
                                                                        $payment_method = getCompanyName();
                                                                        $img            = strtolower($transaction->payment_method->name) . '.jpg';
                                                                    }
                                                                    else
                                                                    {
                                                                        $payment_method = $transaction->payment_method->name;
                                                                        $img            = strtolower($payment_method) . '.jpg';
                                                                    }
                                                                }
                                                            ?>

                                                            <td class="text-left">
                                                                @if($transaction->transaction_type->name == 'Deposit')
                                                                    @if ($transaction->payment_method->name == 'Bank')
                                                                        @if (!empty($transaction->bank->file_id))
                                                                            @php $bank_logo = $transaction->bank->file->filename; @endphp
                                                                            <img src='{{url("public/uploads/files/bank_logos/$bank_logo")}}' class="rounded-circle rounded-circle-custom-trans">
                                                                        @else
                                                                            <img src='{{url("public/images/payment_gateway/bank.jpg")}}' class="rounded-circle rounded-circle-custom-trans">
                                                                        @endif
                                                                    @else
                                                                        @if(!empty($payment_method))
                                                                            <img src='{{url("public/images/payment_gateway/thumb/$img")}}' class="rounded-circle rounded-circle-custom-trans">
                                                                        @endif
                                                                    @endif

                                                                @elseif($transaction->transaction_type->name == 'Withdrawal')
                                                                    @if(!empty($payment_method))
                                                                        <img src='{{url("public/images/payment_gateway/thumb/$img")}}' class="rounded-circle rounded-circle-custom-trans">
                                                                    @endif

                                                                @elseif($transaction->transaction_type_id==Exchange_To || $transaction->transaction_type_id==Exchange_From)
                                                                    <img src='{{url("public/frontend/images/exchange.png")}}' class="rounded-circle rounded-circle-custom-trans">

                                                                @elseif($transaction->transaction_type_id == Request_From || $transaction->transaction_type_id==Transferred)
                                                                    <img src="{{url('public/user_dashboard/images/avatar.jpg')}}" class="rounded-circle rounded-circle-custom-trans">

                                                                @elseif($transaction->transaction_type_id == Bank_Transfer)
                                                                     @if ($transaction->bank)
                                                                        <img src="{{url('public/images/payment_gateway/thumb/bank.jpg')}}" class="rounded-circle rounded-circle-custom-trans">
                                                                    @endif

                                                                @elseif($transaction->transaction_type_id==Voucher_Created || $transaction->transaction_type_id==Voucher_Activated)
                                                                    <img src='{{url("public/frontend/images/voucher.png")}}' class=" rounded-circle-custom-trans">
                                                                @endif
                                                            </td>
                                                            <td class="text-left">
                                                                <p>

                                                                    @if($transaction->transaction_type->name == 'Deposit')
                                                                        @if ($transaction->payment_method->name == 'Bank')
                                                                            {{ $payment_method }} ({{ $transaction->bank->bank_name }})
                                                                        @else
                                                                            @if(!empty($payment_method))
                                                                                {{ $payment_method }}
                                                                            @endif
                                                                        @endif
                                                                    @endif

                                                                    @if($transaction->transaction_type->name == 'Withdrawal')
                                                                        @if(!empty($payment_method))
                                                                            {{ $payment_method }}
                                                                        @endif
                                                                    @endif

                                                                    @if( $transaction->transaction_type->name == 'Transferred' || $transaction->transaction_type->name == 'Request_From' && $transaction->user_type = 'unregistered')
                                                                        {{ ($transaction->email) ? $transaction->email : $transaction->phone }} <!--for send money by phone - mobile app-->
                                                                    @endif
                                                                </p>
                                                                @if($transaction->transaction_type_id)
                                                                    @if($transaction->transaction_type_id==Request_From)
                                                                        <p>@lang('Request Sent')</p>
                                                                    @elseif($transaction->transaction_type_id==Request_To)
                                                                        <p>@lang('Request Received')</p>

                                                                    @elseif($transaction->transaction_type_id == Bank_Transfer)
                                                                        <p>{{ $transaction->bank->account_name }}</p>
                                                                        <p>@lang('Bank Transfer')</p>

                                                                    @elseif($transaction->transaction_type_id == Withdrawal)
                                                                        <p>@lang('Payout')</p>
                                                                    @else
                                                                        <p>{{ __(str_replace('_',' ',$transaction->transaction_type->name)) }}</p>
                                                                    @endif
                                                                @endif
                                                            </td>

                                                        @endif
                                                    @else
                                                        <td class="text-left">
                                                            @if($transaction->merchant->logo)
                                                                <img src="{{url('public/user_dashboard/merchant/thumb').'/'.strtolower($transaction->merchant->logo)}}" class="rounded-circle rounded-circle-custom-trans">
                                                            @else
                                                                <img src="{{url('public/uploads/merchant/merchant.jpg')}}" class="rounded-circle rounded-circle-custom-trans">
                                                            @endif
                                                        </td>
                                                        <td class="text-left">
                                                            <p>{{ $transaction->merchant->business_name }}</p>
                                                            @if($transaction->transaction_type_id)
                                                                <p>{{ __(str_replace('_',' ',$transaction->transaction_type->name)) }}</p>
                                                            @endif
                                                        </td>
                                                    @endif

                                                <td class="text-left"><p>
                                                    <p id="status_{{$transaction->id}}">
                                                        {{
                                                            (
                                                                ($transaction->status == 'Blocked') ? __("Cancelled") :
                                                                (
                                                                    ($transaction->status == 'Refund') ? __("Refunded") : __($transaction->status)
                                                                )
                                                            )
                                                        }}
                                                    </p>
                                                </td>
                                                <td class="text-left"><p>{{ $transaction->currency->code }} </p></td>
                                                <td class="text-left">
                                                    <p>{{ decimalFormat(abs($transaction->total)-abs($transaction->subtotal)) }}</p>
                                                </td>

                                                @if($transaction->transaction_type_id == Deposit)
                                                    @if($transaction->subtotal > 0)
                                                        <td class="text-left text-success"><p>+{{ formatNumber($transaction->subtotal) }}</p></td>
                                                    @endif
                                                @elseif($transaction->transaction_type_id == Payment_Received)
                                                    @if($transaction->subtotal > 0)
                                                        @if($transaction->status == 'Refund') <!-- fixed - pm_v2.3 -->
                                                            <td class="text-left text-danger"><p>-{{ formatNumber($transaction->subtotal) }}</p></td>
                                                        @else
                                                            <td class="text-left text-success"><p>+{{ formatNumber($transaction->subtotal) }}</p></td>
                                                        @endif
                                                    @elseif($transaction->subtotal == 0)
                                                        <td class="text-left">
                                                            <p>{{ formatNumber($transaction->subtotal) }}</p>
                                                        </td>
                                                    @elseif($transaction->subtotal < 0)
                                                        <td class="text-left text-danger">
                                                            <p>{{ formatNumber($transaction->subtotal) }}</p>
                                                        </td>
                                                    @endif
                                                @else
                                                    @if($transaction->total > 0)
                                                        <td class="text-left text-success"><p> +{{ formatNumber($transaction->total) }}</p></td>
                                                    @elseif($transaction->total == 0)
                                                        <td class="text-left"><p>{{ formatNumber($transaction->total) }}</p></td>
                                                    @elseif($transaction->total < 0)
                                                        <td class="text-left text-danger">
                                                            <p>{{ formatNumber($transaction->total) }}</p></td>
                                                    @endif
                                                @endif
                                            </tr>
                                            <tr id="collapseRow{{$key}}" class="collapse">
                                                <td colspan="8" class="">
                                                    <div class="row activity-details" id="loader_{{$transaction->id}}"
                                                         style="min-height: 200px">

                                                        <div class="col-md-4 text-left" id="html_{{$key}}">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="right">
                                                                @if( $transaction->transaction_type_id == Payment_Sent && $transaction->status == 'Success' && !isset($transaction->dispute->id))
                                                                    <a id="dispute_{{$transaction->id}}" href="{{url('/dispute/add/').'/'.$transaction->id}}" class="btn btn-secondary btn-sm">@lang('message.dashboard.transaction.open-dispute')</a>
                                                                @endif

                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                        </div>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8"> @lang('message.dashboard.left-table.no-transaction')</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ $transactions->appends($_GET)->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('js')
<!--daterangepicker-->
<script src="{{asset('public/user_dashboard/js/daterangepicker.js')}}" type="text/javascript"></script>

<script>
    $(document).ready(function ()
    {
        var sDate;
        var eDate;
        //Date range as a button
        $('#daterange-btn').daterangepicker(
            {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment()
            },
            function (start, end) {
                sDate = moment(start, 'MMMM D, YYYY').format('DD-MM-YYYY');
                $('#startfrom').val(sDate);
                eDate = moment(end, 'MMMM D, YYYY').format('DD-MM-YYYY');
                $('#endto').val(eDate);
                $('#daterange-btn span').html(sDate + ' - ' + eDate);
            }
        )
        $(document).ready(function () {
            var startDate = "{!! $from !!}";
            var endDate = "{!! $to !!}";
            if (startDate == '') {
                $('#daterange-btn span').html('<i class="fa fa-calendar"></i> {{ __('message.dashboard.transaction.date-range') }}');
            } else {
                $('#daterange-btn span').html(startDate + ' - ' + endDate);
            }
        });
    });

    $(".show_area").on('click', function ()
    {
        var trans_id = $(this).attr('trans-id');

        var row_id = $(this).attr('id');

        var preRowId = (parseInt(row_id) - 1);

        $(this).addClass('leftBorderRow');

        $("#collapseRow" + row_id).css('border-left', '5px solid #0b2854');

        var result = $("#" + preRowId).hasClass('leftBorderRow');
        if (result == false) {
            $("#" + preRowId).css('border-left', '5px solid #FFFFFF');
        }
        $.ajax({
            method: "POST",
            url: SITE_URL + "/get_transaction",
            dataType: "json",
            data: {
                id: trans_id}
            ,
            beforeSend: function () {

                $('#loader_' + trans_id).css({
                    'margin': '0px',
                    'background': "url('public/user_dashboard/images/loading.gif') center",
                    'background-repeat': 'no-repeat'
                });
            },
            complete: function () {
                $('#loader_' + trans_id).css({'margin': '0px', 'background': "", 'background-repeat': ''});
            },
            success: function (response) {
                // console.log(response.html);
                $("#html_" + row_id).html(response.html);
            }
        });

        var totalClick = parseInt($(this).attr('click')) + 1;
        $(this).attr('click', totalClick);
        var nowClick = parseInt($(this).attr('click')) % 2;

        if (nowClick == 0) {
            $(this).removeClass('leftBorderRow');
            $("#collapseRow" + row_id).css('border-left', '5px solid #FFFFFF');
            $("#icon_" + row_id).removeClass("fa-arrow-circle-down").addClass("fa-arrow-circle-right");
        } else {
            $(this).addClass('leftBorderRow');
            $("#icon_" + row_id).removeClass('fa-arrow-circle-right').addClass("fa-arrow-circle-down");
        }

    });

    // $(document).on('click', '.trxn', function ()
    // {
    //     var trans_id = $(this).attr('data');
    //     //alert(trans_id);
    //     $.ajax({
    //         method: "POST",
    //         url: SITE_URL + "/request_payment/cancel",
    //         dataType: "json",
    //         data: {id: trans_id},
    //         success: function (response) {
    //             $("#status_" + trans_id).text("{{__("Cancelled")}}");
    //             $("#btn_" + trans_id).text("{{__("Cancelled")}}");
    //         }
    //     });
    // });

    //Request To - Cancel
    $(document).on('click', '.trxn', function (e)
    {
        e.preventDefault();

        var trans_id = $(this).attr('data');
        var type = $(this).attr('data-type');
        var notificationType = $(this).attr('data-notificationType');
        $.ajax({
            method: "POST",
            url: SITE_URL + "/request_payment/cancel",
            dataType: "json",
            data: {
                id: trans_id,
                type: type,
                notificationType: notificationType,
            },
            beforeSend: function() {
                $("#status_" + trans_id).text("Cancelling..");
                $("#btn_" + trans_id).attr("disabled", true).text("Cancelling..");
                $('.trxn_accept').hide();
            },
            success: function (response)
            {
                // console.log(response);
                $("#status_" + trans_id).text("{{__("Cancelled")}}");
                $("#btn_" + trans_id).text("{{__("Cancelled")}}");

                setTimeout(function() {
                    $("#btn_" + trans_id).fadeOut('fast');
                }, 1000); // <-- time in milliseconds
            }
        });
    });

    //Request To - Accept
    $(document).on('click', '.trxnreqfrom', function (e)
    {
        e.preventDefault();
        var trans_id = $(this).attr('data');
        var type = $(this).attr('data-type');
        var notificationType = $(this).attr('data-notificationType');
        $.ajax({
            method: "POST",
            url: SITE_URL + "/request_payment/cancelfrom",
            dataType: "json",
            data: {
                id: trans_id,
                type: type,
                notificationType: notificationType,
            },
            beforeSend: function() {
                $("#status_" + trans_id).text("Cancelling..");
                $("#btn_" + trans_id).attr("disabled", true).text("Cancelling..");
                $('.trxn_accept').hide();
            },
            success: function (response)
            {
                // console.log(response);
                $("#status_" + trans_id).text("{{__("Cancelled")}}");
                $("#btn_" + trans_id).text("{{__("Cancelled")}}");
                setTimeout(function() {
                    $("#btn_" + trans_id).fadeOut('fast');
                }, 1000); // <-- time in milliseconds
            }
        });
    });

    $(document).on('click', '.trxn_accept', function ()
    {
        window.location.replace(SITE_URL + "/request_payment/accept/" + ($(this).attr('data-rel')));
    });
</script>

@endsection