@extends('admin.layouts.master')
@section('title', 'Revenues')

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
          <form class="form-horizontal" action="{{ url('admin/revenues') }}" method="GET">

              <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
              <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
              <input id="user_id" type="hidden" name="user_id" value="{{ isset($user) ? $user : '' }}">

              <div class="row">
                  <div class="col-md-12">
                      <div class="row">
                          <!-- Date and time range -->
                          <div class="col-md-3">
                              <label>Date Range</label>
                              <button type="button" class="btn btn-default" id="daterange-btn">
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
                                  @foreach($revenues_currency as $revenue)
                                      <option value="{{ $revenue->currency_id }}" {{ ($revenue->currency_id == $currency) ? 'selected' : '' }}>
                                          {{ $revenue->currency->code }}
                                      </option>
                                  @endforeach
                              </select>
                          </div>

                          {{-- <div class="col-md-2">
                              <label for="status">Status</label>
                              <select class="form-control select2" name="status" id="status">
                                  <option value="all" {{ ($status =='all') ? 'selected' : '' }} >All</option>
                                  @foreach($revenues_status as $revenue)
                                    <option value="{{ $revenue->status }}" {{ ($revenue->status == $status) ? 'selected' : '' }}>
                                      {{
                                          (
                                              ($revenue->status == 'Blocked') ? "Cancelled" :
                                              (
                                                  ($revenue->status == 'Refund') ? "Refunded" : $revenue->status
                                              )
                                          )
                                      }}
                                    </option>
                                  @endforeach
                              </select>
                          </div> --}}

                          <div class="col-md-2">
                              <label for="status">Transaction Type</label>
                              <select class="form-control select2" name="type" id="type">
                                  <option value="all" {{ ($type =='all') ? 'selected' : '' }} >All</option>
                                  @foreach($revenues_type as $revenue)
                                  <option value="{{ $revenue->transaction_type_id }}" {{ ($revenue->transaction_type_id == $type) ? 'selected' : '' }}>
                                    {{ ($revenue->transaction_type->name == "Withdrawal") ? "Payout" : str_replace('_', ' ', $revenue->transaction_type->name) }}
                                  </option>
                                  @endforeach
                              </select>
                          </div>

                          <div class="col-md-2">
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

  <!-- Total Charge Boxes -->
  @if($currency_info)
    @php
      // dd($currency_info);
    @endphp
    <div class="box">
      <div class="box-body">
          <div class="row">
            @forelse ($currency_info as $index=>$revenue)

                @if ($revenue > 0)
                  <div class="col-md-3">
                     <div class="panel panel-primary">
                          <div class="panel-body text-center" style="padding:5px; margin-bottom: 0;">
                            <span class="text-info" style="font-size: 15px">Total {{ $index }} Revenue</span>
                            <strong><h4>{{ moneyFormat($index , formatNumber($revenue)) }}</h4></strong>
                          </div>
                     </div>
                  </div>
                @endif

            @empty
              <h3 class="panel-title text-center">No Revenue Found!</h3>
            @endforelse
          </div>
      </div>
    </div>
  @endif

  <div class="box">
    <div class="box-body">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-info">
            <div class="panel-heading">
              <div class="row">
                  <div class="col-md-8">
                      <h3 class="panel-title">All Revenues</h3>
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
    $(".select2").select2({});

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
          var currency = $('#currency').val();
          var type = $('#type').val();
          var user_id = $('#user_id').val();
          window.location = SITE_URL+"/admin/revenues/csv?startfrom="+startfrom
          +"&endto="+endto
          +"&currency="+currency
          +"&type="+type
          +"&user_id="+user_id;
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
          var currency = $('#currency').val();
          var type = $('#type').val();
          var user_id = $('#user_id').val();
          window.location = SITE_URL+"/admin/revenues/pdf?startfrom="+startfrom
          +"&endto="+endto
          +"&currency="+currency
          +"&type="+type
          +"&user_id="+user_id;
        });
    });
</script>

@endpush