@extends('admin.layouts.master')

@section('title', 'Merchant Payments')

@section('head_style')
    <!-- Bootstrap daterangepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/bootstrap/dist/css/daterangepicker.css')}}">
    <!-- dataTables -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')
    <div class="box">
        <div class="box-body">
            <form class="form-horizontal" action="{{ url('admin/merchant_payments') }}" method="GET">

                <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
                <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
                <input id="user_id" type="hidden" name="user_id" value="{{ isset($user) ? $user : '' }}">

                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <!-- Date and time range -->
                            <div class="col-md-3">
                                <label>Date Range</label>
                                <button type="button" class="btn btn-default" id="daterange-btn" >
                                    <span id="drp">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>

                            <!-- Currency -->
                            <div class="col-md-2">
                                <label for="currency">Currency</label>
                                <select class="form-control select2" name="currency" id="currency">
                                    <option value="all" {{ ($currency =='all') ? 'selected' : '' }} >All</option>
                                    @foreach($merchant_payments_currencies as $merchant_payment)
                                        <option value="{{ $merchant_payment->currency_id }}" {{ ($merchant_payment->currency_id == $currency) ? 'selected' : '' }}>
                                            {{ $merchant_payment->currency->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-2">
                                <label for="status">Status</label>
                                <select class="form-control select2" name="status" id="status">
                                    <option value="all" {{ ($status =='all') ? 'selected' : '' }} >All</option>
                                    @foreach($merchant_payments_status as $merchant_payment)
                                        <option value="{{ $merchant_payment->status }}" {{ ($merchant_payment->status == $status) ? 'selected' : '' }}>
                                            {{ ($merchant_payment->status == 'Refund') ? 'Refunded' : $merchant_payment->status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-md-2">
                                <label for="status">Payment Method</label>
                                <select class="form-control select2" name="payment_methods" id="payment_methods">
                                    <option value="all" {{ ($pm =='all') ? 'selected' : '' }} >All</option>
                                    @foreach($merchant_payments_pm as $merchant_payment)
                                        <option value="{{ $merchant_payment->payment_method_id }}" {{ ($merchant_payment->payment_method_id == $pm) ? 'selected' : '' }}>
                                            {{ ($merchant_payment->payment_method->name == "Mts") ? getCompanyName() : $merchant_payment->payment_method->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-1">
                                <div class="input-group" style="margin-top: 25px;">
                                   <button type="submit" name="btn" class="btn btn-primary btn-flat" id="btn">Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="panel-title">All Merchant Payments</h3>
                                </div>
                                <div class="col-md-4">
                                    <div class="btn-group pull-right">
                                        <a href="" class="btn btn-sm btn-default btn-flat" id="csv">CSV</a>&nbsp;&nbsp;
                                        <a href="" class="btn btn-sm btn-default btn-flat" id="pdf">PDF</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

<!-- Bootstrap daterangepicker -->
<script src="{{ asset('public/backend/bootstrap-daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>

<!-- jquery.dataTables js -->
<script src="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

{!! $dataTable->scripts() !!}

<script type="text/javascript">
    $(".select2").select2({
    });

    var sDate;
    var eDate;

    //Date range as a button
    $('#daterange-btn').daterangepicker(
    {
        ranges   : {
        'Today'       : [moment(), moment()],
        'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month'  : [moment().startOf('month'), moment().endOf('month')],
        'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate  : moment()
        },
        function (start, end)
        {
            var sessionDate      = '{{Session::get('date_format_type')}}';
            var sessionDateFinal = sessionDate.toUpperCase();

            sDate = moment(start, 'MMMM D, YYYY').format(sessionDateFinal);
            $('#startfrom').val(sDate);

            eDate = moment(end, 'MMMM D, YYYY').format(sessionDateFinal);
            $('#endto').val(eDate);

            $('#daterange-btn span').html('&nbsp;' + sDate + ' - ' + eDate + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        }
    )

    $(document).ready(function()
    {
        $("#daterange-btn").mouseover(function() {
            $(this).css('background-color', 'white');
            $(this).css('border-color', 'grey !important');
        });

        var startDate = "{!! $from !!}";
        var endDate   = "{!! $to !!}";
        // alert(startDate);
        if (startDate == '') {
            $('#daterange-btn span').html('<i class="fa fa-calendar"></i> &nbsp;&nbsp; Pick a date range &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        } else {
            $('#daterange-btn span').html(startDate + ' - ' +endDate + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        }
    });

    // csv
    $(document).ready(function()
    {
        $('#csv').on('click', function(event)
        {
          event.preventDefault();

          var startfrom = $('#startfrom').val();
          var endto = $('#endto').val();

          var status = $('#status').val();
          var currency = $('#currency').val();
          var payment_methods = $('#payment_methods').val();

          window.location = SITE_URL+"/admin/merchant_payments/csv?startfrom="+startfrom
          +"&endto="+endto
          +"&status="+status
          +"&currency="+currency
          +"&payment_methods="+payment_methods
        });
    });

    // pdf
    $(document).ready(function()
    {
        $('#pdf').on('click', function(event)
        {
          event.preventDefault();

          var startfrom = $('#startfrom').val();

          var endto = $('#endto').val();

          var status = $('#status').val();
          var currency = $('#currency').val();
          var payment_methods = $('#payment_methods').val();

          window.location = SITE_URL+"/admin/merchant_payments/pdf?startfrom="+startfrom
          +"&endto="+endto
          +"&status="+status
          +"&currency="+currency
          +"&payment_methods="+payment_methods
        });
    });

</script>
@endpush
