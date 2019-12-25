@extends('admin.layouts.master')
@section('title', 'Request Payments')

@section('head_style')
  <!-- Bootstrap daterangepicker -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/bootstrap/dist/css/daterangepicker.css')}}">

  <!-- dataTables -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">

  <!-- jquery-ui-1.12.1 -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/jquery-ui-1.12.1/jquery-ui.min.css')}}">
@endsection

@section('page_content')
  <div class="box">
      <div class="box-body">
          <form class="form-horizontal" action="{{ url('admin/request_payments') }}" method="GET">

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
                                  @foreach($requestpayments_currencies as $requestpayment)
                                      <option value="{{ $requestpayment->currency_id }}" {{ ($requestpayment->currency_id == $currency) ? 'selected' : '' }}>
                                          {{ $requestpayment->currency->code }}
                                      </option>
                                  @endforeach
                              </select>
                          </div>

                          <div class="col-md-2">
                              <label for="status">Status</label>
                              <select class="form-control select2" name="status" id="status">
                                  <option value="all" {{ ($status =='all') ? 'selected' : '' }} >All</option>
                                  @foreach($requestpayments_status as $requestpayment)
                                      <option value="{{ $requestpayment->status }}" {{ ($requestpayment->status == $status) ? 'selected' : '' }}>
                                      {{
                                          (
                                              ($requestpayment->status == 'Blocked') ? "Cancelled" :
                                              (
                                                  ($requestpayment->status == 'Refund') ? "Refunded" : $requestpayment->status
                                              )
                                          )
                                      }}
                                      </option>
                                  @endforeach
                              </select>
                          </div>

                          <div class="col-md-2">
                              <label for="creator">User</label>
                              <input id="user_input" type="text" name="user" placeholder="Enter Name" class="form-control"
                                  value="{{ empty($user) ?  $user : $getName->first_name.' '.$getName->last_name }}"
                                  {{  isset($getName) && ($getName->id == $user) ? 'selected' : '' }}>
                              <span id="error-user"></span>
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

  <div class="box">
    <div class="box-body">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-info">
              <div class="panel-heading">
                  <div class="row">
                      <div class="col-md-8">
                          <h3 class="panel-title">All Request Payments</h3>
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
                      {{-- <table class="table text-center" id="request_payments">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th style="display: none;">UUID</th>
                              <th>User</th>
                              <th>Requested Amount</th>
                              <th>Accepted Amount</th>
                              <th>Currency</th>
                              <th>Receiver</th>
                              <th>Status</th>
                              @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_request_payment'))
                              <th>Action</th>
                              @endif
                            </tr>
                          </thead>
                          <tbody>
                          @foreach($requestpayments as $row)
                            <tr>

                              <td>{{ dateFormat($row->created_at) }}</td>

                              <td style="display: none;">{{  $row->uuid }}</td>

                              <td>{{ isset($row->user) ? $row->user->first_name.' '.$row->user->last_name : "-" }}</td>

                              @if ($row->amount > 0)
                                <td><span class="text-green">+ {{ formatNumber($row->amount) }} </span></td>
                              @else
                                <td><span class="text-red"> {{ formatNumber($row->amount) }} </span></td>
                              @endif

                              @if ($row->accept_amount > 0)
                                <td><span class="text-green">+ {{ formatNumber($row->accept_amount) }}</span></td>
                              @else
                                <td><span class="text-red">{{ ($row->accept_amount == 0) ?  "-" : formatNumber($row->accept_amount) }}</span></td>
                              @endif

                              <td>{{ $row->currency->code }}</td>

                              <td>{{ isset($row->receiver) ? $row->receiver->first_name.' '.$row->receiver->last_name : $row->email }}</td>

                              @if ($row->status == 'Success')
                                      <td><span class="label label-success">Success</span></td>
                              @elseif ($row->status == 'Pending')
                                  <td><span class="label label-primary">Pending</span></td>
                              @elseif ($row->status == 'Refund')
                                  <td><span class="label label-warning">Refunded</span></td>
                              @elseif ($row->status == 'Blocked')
                                  <td><span class="label label-danger">Cancelled</span></td>
                              @endif

                              @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_request_payment'))
                              <td>
                                      <a class="btn btn-xs btn-primary" href="{{url('admin/request_payments/edit/'.$row->id)}}"><i class="glyphicon glyphicon-edit"></i></a>
                              </td>
                              @endif
                            </tr>
                           @endforeach
                          </tbody>
                      </table> --}}
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

<!-- jquery-ui-1.12.1 -->
<script src="{{ asset('public/backend/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

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

        $("#user_input").on('keyup keypress', function(e)
        {
            if (e.type=="keyup" || e.type=="keypress")
            {
                var user_input = $('form').find("input[type='text']").val();
                if(user_input.length === 0)
                {
                    $('#user_id').val('');
                    $('#error-user').html('');
                    $('form').find("button[type='submit']").prop('disabled',false);
                }
            }
        });

        $('#user_input').autocomplete(
        {
            source:function(req,res)
            {
                if (req.term.length > 0)
                {
                    $.ajax({
                        url:'{{url('admin/request_payments/user_search')}}',
                        dataType:'json',
                        type:'get',
                        data:{
                            search:req.term
                        },
                        success:function (response)
                        {
                            // console.log(response);
                            // console.log(req.term.length);

                            $('form').find("button[type='submit']").prop('disabled',true);

                            if(response.status == 'success')
                            {
                                res($.map(response.data, function (item)
                                {
                                    return {
                                            user_id : item.user_id, //user_id is defined
                                            first_name : item.first_name, //first_name is defined
                                            last_name : item.last_name, //last_name is defined
                                            value: item.first_name + ' ' + item.last_name //don't change value
                                        }
                                    }
                                    ));
                            }
                            else if(response.status == 'fail')
                            {
                                $('#error-user').addClass('text-danger').html('User Does Not Exist!');
                            }
                        }
                    })
                }
                else
                {
                    // console.log(req.term.length);
                    $('#user_id').val('');
                }
            },
            select: function (event, ui)
            {
                var e = ui.item;

                $('#error-user').html('');

                $('#user_id').val(e.user_id);

                // console.log(e.user_id);

                $('form').find("button[type='submit']").prop('disabled',false);
            },
            minLength: 0,
            autoFocus: true
        });
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

          var user_id = $('#user_id').val();

          window.location = SITE_URL+"/admin/request_payments/csv?startfrom="+startfrom
          +"&endto="+endto
          +"&status="+status
          +"&currency="+currency
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

          var status = $('#status').val();

          var currency = $('#currency').val();

          var user_id = $('#user_id').val();

          window.location = SITE_URL+"/admin/request_payments/pdf?startfrom="+startfrom
          +"&endto="+endto
          +"&status="+status
          +"&currency="+currency
          +"&user_id="+user_id;
        });
    });
</script>

@endpush