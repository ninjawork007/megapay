@php
    $breadcrumb = [
        [
            'icon' => 'fa fa-home',
            'href' => url('admin/home'),
            'name' => 'Dashboard'
        ]
    ];
@endphp

@extends('admin.layouts.master', $breadcrumb)

@section('title', 'Dashboard')

@section('page_content')
<section class="content">

      <div class="row">
        <!--Graph Line Chart last 30 days start-->
          <div class="col-md-12">
          <!-- LINE CHART -->
          <div class="box box-info">
            <div class="box-header with-border">
              <div id="row">
                <div class="col-md-12">
                  <div class="text-center">
                   <strong>Last 30 days</strong>
                  </div>
                </div>
              </div>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <canvas id="lineChart" style="height: 246px; width: 1069px;" height="246" width="1069"></canvas>
              </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer with-border">
              <div id="row">
                <div class="col-md-3">
                  <div class="row">
                    <div class="col-md-1">
                      <div id="deposit">
                      </div>
                    </div>
                    <div class="col-md-8 scp">
                      Deposit
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="row">
                    <div class="col-md-1">
                      <div id="withdrawal">
                      </div>
                    </div>
                    <div class="col-md-8 scp">
                      Payout
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="row">
                    <div class="col-md-1">
                    <div id="transfer">
                    </div>
                    </div>
                    <div class="col-md-8 scp">
                      Transfer
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
          <!-- /.box -->
        </div>
        <!--Graph Line Chart last 30 days end-->
      </div>

    <div class="row">
     <div class="col-md-8">
        <div class="box box-info">
          <div class="box box-body">

           <!-- Custom Tabs (Pulled to the right) -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_1" data-toggle="tab">This Week</a></li>
              <li><a href="#tab_2" data-toggle="tab">Last Week</a></li>
              <li><a href="#tab_3" data-toggle="tab">This Month</a></li>
              <li><a href="#tab_4" data-toggle="tab">Last Month</a></li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
                  <div class="box-header with-border">
                    <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 72px;">{{moneyFormat($defaultCurrency->symbol,formatNumber($this_week_revenue))}}</span></h3>
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
                    <div>
                        <span class="progress-label col-md-3"><strong>Deposit Profit</strong></span>
                        <div class="progress">
                          <div class="progress-bar progress-bar-deposit" role="progressbar" aria-valuenow="{{$this_week_deposit_percentage}}" aria-valuemin="0" aria-valuemax="100"
                          style='width:<?php  echo $this_week_deposit_percentage ?>%'>
                            <span class="">

                              @if ($this_week_deposit_percentage >= 12.5)
                                {{moneyFormat($defaultCurrency->symbol,formatNumber($this_week_deposit))}}
                              @else
                                {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($this_week_deposit))}}
                              @endif

                              </span>
                           </div>
                        </div>
                    </div>
                    <div>
                        <span class="progress-label col-md-3"><strong>Payout Profit</strong></span>
                        <div class="progress">
                          <div class="progress-bar progress-bar-withdrawal" role="progressbar" aria-valuenow="{{$this_week_withdrawal_percentage}}" aria-valuemin="0" aria-valuemax="100"
                          style='width:<?php  echo $this_week_withdrawal_percentage ?>%'>
                            <span class="">
                              @if ($this_week_withdrawal_percentage >= 12.5)
                                {{moneyFormat($defaultCurrency->symbol,formatNumber($this_week_withdrawal))}}
                              @else
                                {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($this_week_withdrawal))}}
                              @endif
                            </span>
                           </div>
                        </div>
                    </div>
                    <div>
                        <span class="progress-label col-md-3"><strong>Transfer Profit</strong></span>
                        <div class="progress">
                          <div class="progress-bar progress-bar-transfer" role="progressbar" aria-valuenow="{{$this_week_transfer_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $this_week_transfer_percentage ?>%'>
                            <span class="">
                              @if ($this_week_transfer_percentage >= 12.5)
                                {{moneyFormat($defaultCurrency->symbol,formatNumber($this_week_transfer))}}
                              @else
                                {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($this_week_transfer))}}
                              @endif
                            </span>
                           </div>
                        </div>
                    </div>


                  </div>
                  <!-- /.box-body -->
              <!-- /.box -->
              </div>


              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_2">
                  <div class="box-header with-border">
                    {{-- <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 43px;">{{$last_week_revenue}}</span></h3> --}}
                    <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 72px;">{{  moneyFormat($defaultCurrency->symbol, formatNumber($last_week_revenue)) }}</span></h3>
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
                    <div>
                      <span class="progress-label col-md-3"><strong>Deposit Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-deposit" role="progressbar" aria-valuenow="{{$last_week_deposit_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $last_week_deposit_percentage ?>%'>

                          <span class="">
                            @if ($last_week_deposit_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($last_week_deposit))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($last_week_deposit))}}
                            @endif
                          </span>

                         </div>
                      </div>
                  </div>
                  <div>
                      <span class="progress-label col-md-3"><strong>Payout Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-withdrawal" role="progressbar" aria-valuenow="{{$last_week_withdrawal_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $last_week_withdrawal_percentage ?>%'>
                          <span class="">
                            @if ($last_week_withdrawal_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($last_week_withdrawal))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($last_week_withdrawal))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                  <div>
                      <span class="progress-label col-md-3"><strong>Transfer Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-transfer" role="progressbar" aria-valuenow="{{$last_week_transfer_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $last_week_transfer_percentage ?>%'>
                          <span class="">
                            @if ($last_week_transfer_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($last_week_transfer))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($last_week_transfer))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                  </div>
                  <!-- /.box-body -->
          <!-- /.box -->
        </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="tab_3">
                <div class="box-header with-border">
                  {{-- <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 43px;">{{$this_month_revenue}}</span></h3> --}}
                  <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 72px;">{{  moneyFormat($defaultCurrency->symbol, formatNumber($this_month_revenue)) }}</span></h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <div>
                      <span class="progress-label col-md-3"><strong>Deposit Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-deposit" role="progressbar" aria-valuenow="{{$this_month_deposit_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $this_month_deposit_percentage ?>%'>
                          <span class="">
                            @if ($this_month_deposit_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($this_month_deposit))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($this_month_deposit))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                  <div>
                      <span class="progress-label col-md-3"><strong>Payout Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-withdrawal" role="progressbar" aria-valuenow="{{$this_month_withdrawal_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $this_month_withdrawal_percentage ?>%'>
                          <span class="">
                            @if ($this_month_withdrawal_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($this_month_withdrawal))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($this_month_withdrawal))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                  <div>
                      <span class="progress-label col-md-3"><strong>Transfer Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-transfer" role="progressbar" aria-valuenow="{{$this_month_transfer_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $this_month_transfer_percentage ?>%'>

                          <span class="">
                            @if ($this_month_transfer_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($this_month_transfer))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($this_month_transfer))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                </div>
                <!-- /.box-body -->
          <!-- /.box -->
        </div>
          <!-- /.tab-pane -->

           <div class="tab-pane" id="tab_4">
                <div class="box-header with-border">
                  {{-- <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 43px;">{{$last_month_revenue}}</span></h3> --}}
                  <h3 class="box-title"><span style="margin-left: 14px;">Total Profit</span><span style="margin-left: 72px;">{{  moneyFormat($defaultCurrency->symbol, formatNumber($last_month_revenue)) }}</span></h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <div>
                      <span class="progress-label col-md-3"><strong>Deposit Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-deposit" role="progressbar" aria-valuenow="{{$last_month_deposit_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $last_month_deposit_percentage ?>%'>
                          <span class="">
                            @if ($last_month_deposit_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($last_month_deposit))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($last_month_deposit))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                  <div>
                      <span class="progress-label col-md-3"><strong>Payout Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-withdrawal" role="progressbar" aria-valuenow="{{$last_month_withdrawal_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $last_month_withdrawal_percentage ?>%'>
                          <span class="">
                            @if ($last_month_withdrawal_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($last_month_withdrawal))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($last_month_withdrawal))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                  <div>
                      <span class="progress-label col-md-3"><strong>Transfer Profit</strong></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-transfer" role="progressbar" aria-valuenow="{{$last_month_transfer_percentage}}" aria-valuemin="0" aria-valuemax="100" style='width:<?php  echo $last_month_transfer_percentage ?>%'>
                          <span class="">
                            @if ($last_month_transfer_percentage >= 12.5)
                              {{moneyFormat($defaultCurrency->symbol,formatNumber($last_month_transfer))}}
                            @else
                              {{moneyFormatForDashboardProgressBars($defaultCurrency->symbol,formatNumber($last_month_transfer))}}
                            @endif
                          </span>
                         </div>
                      </div>
                  </div>
                </div>
                <!-- /.box-body -->
          <!-- /.box -->
        </div>
      </div>
      <!-- /.tab-content -->
    </div>
    <!-- nav-tabs-custom -->
  </div>
 </div>

   <div class="box box-info">
    <div class="box box-body">
     <div class="col-md-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3>{{$totalUser}}</h3>

            <p>Total Users</p>
          </div>
          <div class="icon">
            <i class="ion ion-person-add"></i>
          </div>
          <a href="{{url('admin/users')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-md-6">
        <!-- small box -->
        <div class="small-box bg-red">
          <div class="inner">
            <h3>{{$totalMerchant}}</h3>

            <p>Total Merchants</p>
          </div>
          <div class="icon">
            <i class="ion ion-person-add"></i>
          </div>
          <a href="{{url('admin/merchants')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-md-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3>{{$totalTicket}}</h3>
            <p>Total Tickets</p>
          </div>
          <div class="icon">
            <i class="fa fa-envelope-o"></i>
          </div>
          <a href="{{url('admin/tickets/list')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
     <div class="col-md-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3>{{$totalDispute}}</h3>

              <p>Total Dispute</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{url('admin/disputes')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
    </div>
    </div>
  </div>

   <div class="box box-info">
        <div class="box-header text-center">
          <h4 class="text-info text-justify"><b>Latest Ticket</b></h4>
        </div>
            <div class="box box-body">
              @if(!empty($latestTicket))
              <div class="table-responsive">
              <table class="table table-bordered">
                  <thead class="text-left">
                  <tr>
                  <th>Subject</th>
                  <th>User</th>
                  <th>Priority</th>
                  <th>Created Date</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($latestTicket as $item)
                  <tr class="text-left">
                   {{--  <td align="center" style="width: 20%;"><a href='{{ url("admin/users/edit/$item->user_id") }}'>{{$item->first_name.' '.$item->last_name}}</a></td>
                    <td align="center" style="width: 40%;"><a href='{{ url("admin/tickets/reply/$item->id") }}'>{{$item->subject}}</a></td>
                    <td align="center" style="width: 20%;">{{$item->priority}}</td>
                    <td align="center" style="width: 20%;">{{dateFormat($item->created_at)}}</td> --}}


                    <td style="width: 40%;"><a href='{{ url("admin/tickets/reply/$item->id") }}'>{{$item->subject}}</a></td>
                    <td style="width: 20%;"><a href='{{ url("admin/users/edit/$item->user_id") }}'>{{$item->first_name.' '.$item->last_name}}</a></td>
                    <td style="width: 20%;">{{$item->priority}}</td>
                    <td style="width: 20%;">{{dateFormat($item->created_at)}}</td>
                  </tr>
                  @endforeach
                  </tbody>
              </table>
            </div>
              @else
              <h4 class="text-center">No Latest Ticket</h4>
              @endif
            </div>
        </div>

        <div class="box box-info">
        <div class="box-header text-center">
          <h4 class="text-info text-justify"><b>Latest Dispute</b></h4>
        </div>
            <div class="box box-body">
              @if(!empty($latestDispute))
              <div class="table-responsive">
              <table class="table table-bordered">
                  {{-- <thead>
                  <tr>
                  <th class="text-center">Claimant</th>
                  <th class="text-center">Dispute</th>
                  <th class="text-center">Created Date</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($latestDispute as $item)
                  <tr>
                    <td align="center" style="width: 20%;"><a href='{{ url("admin/users/edit/$item->claimant_id") }}'>{{$item->first_name.' '.$item->last_name}}</a></td>
                    <td align="center" style="width: 40%;"><a href='{{ url("admin/dispute/discussion/$item->id") }}'>{{$item->title}}</a></td>
                    <td align="center" style="width: 20%;">{{dateFormat($item->created_at)}}</td>
                  </tr> --}}
                  <thead class="text-left">
                    <tr>
                      <th>Dispute</th>
                      <th>Claimant</th>
                      <th>Created Date</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach($latestDispute as $item)
                    <tr class="text-left">
                      <td style="width: 40%;"><a href='{{ url("admin/dispute/discussion/$item->id") }}'>{{$item->title}}</a></td>
                      <td style="width: 30%;"><a href='{{ url("admin/users/edit/$item->claimant_id") }}'>{{$item->first_name.' '.$item->last_name}}</a></td>
                      <td style="width: 30%;">{{dateFormat($item->created_at)}}</td>
                    </tr>
                  @endforeach
                  </tbody>
              </table>
            </div>
              @else
              <h4 class="text-center">No Latest Dispute</h4>
              @endif
            </div>
        </div>
 </div>


  <div class="col-md-4">
    <div class="box box-info">
        <div class="box-header">
                <div style="font-weight:bold; font-size:20px;" class="text-info">
                  Wallet Balance
                </div>
        </div>
        <div class="box box-body">
          @if(!empty($wallets))

           @foreach($wallets as $code=>$wallet_amount)

            <div style="min-height:45px;border-bottom: 1px solid gray;padding: 5px 0px;">
              <div style="width:60%;float: left;">

                {{-- <div style="font-weight: bold; min-height: 25px;">{{$code}}</div><div class="clearfix"></div> --}}
                <div style="min-height: 25px;">{{$code}}</div><div class="clearfix"></div>
                <div class="clearfix"></div>

              </div>

              {{-- <div style="width:40%;float: left;text-align: right;font-weight: bold;"> --}}
              <div style="width:40%;float: left;text-align: right;">
              {{$wallet_amount}}

              {{-- {{  moneyFormat($defaultCurrency->symbol, formatNumber($last_month_transfer)) }} --}}
              </div>
            </div>
            <div class="clearfix"></div>
            @endforeach

          @else
          <h5 class="text-center">No Wallet Balance</h5>
          @endif
        </div>
    </div>
   {{-- <div class="box box-info">

    <div class="box-header">
          <div style="font-weight:bold; font-size:20px;">
            Exchange Rate
          </div>
        </div>
        <div class="box box-body">
          @if(!empty($currencies))

           @foreach($currencies as $result)
            <div style="min-height:45px;border-bottom: 1px solid gray;padding: 5px 0px;">
              <div style="width:60%;float: left;">
                <div style="font-weight: bold; min-height: 25px;"><strong>{{ $defaultCurrency->rate.' '.$defaultCurrency->code}}</strong></div><div class="clearfix"></div>
                <div class="clearfix"></div>
              </div>
              <div style="width:40%;float: left;text-align: right;font-weight: bold;">
              {{$result['rate'].' '.$result['code'] }}
              </div>
            </div>
            <div class="clearfix"></div>
            @endforeach

          @else
          <h5 class="text-center">No Exchange Rate</h5>
          @endif
        </div>


   </div> --}}
  </div>

</div>
</section>
@endsection

@push('extra_body_scripts')

<script src="{{ asset('public/backend/chart.js/Chart.min.js') }}" type="text/javascript"></script>

<script>

  $(function () {
   'use strict';
      var areaChartData = {
        labels: jQuery.parseJSON('{!! $date !!}'),
        datasets: [
          {
            label: "Deposit" + " " + "({!! $defaultCurrency->symbol !!})",
            // fillColor: "rgba(66,155,206, 1)",
            // strokeColor: "rgba(66,155,206, 1)",
            // pointColor: "rgba(66,155,206, 1)",

            fillColor: "#78BEE6",
            strokeColor: "#78BEE6",
            pointColor: "#78BEE6",

            pointStrokeColor: "#429BCE",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(66,155,206, 1)",
            data: {!! $depositArray !!}
          },
          {
            label: "Payout" + " " + "({!! $defaultCurrency->symbol !!})",

            // fillColor: "rgba(255,105,84,1)",
            // strokeColor: "rgba(255,105,84,1)",
            // pointColor: "#F56954",

            fillColor: "#FBB246",
            strokeColor: "#FBB246",
            pointColor: "#FBB246",

            pointStrokeColor: "rgba(255,105,84,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(255,105,84,1)",
            data: {!!$withdrawalArray !!}
          },
          {
            label: "Transfer" + " " + "({!! $defaultCurrency->symbol !!})",

            // fillColor: "rgba(47, 182, 40,0.9)",
            // strokeColor: "rgba(47, 182, 40,0.8)",
            // pointColor: "#2FB628",

            fillColor: "#67FB4A",
            strokeColor: "#67FB4A",
            pointColor: "#67FB4A",

            pointStrokeColor: "rgba(47, 182, 40,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(47, 182, 40,1)",
            data : {!!$transferArray!!}
          }
        ]
      };

      var areaChartOptions = {
        //Boolean - If we should show the scale at all
        showScale: true,
        //Boolean - Whether grid lines are shown across the chart
        scaleShowGridLines: false,
        //String - Colour of the grid lines
        scaleGridLineColor: "rgba(0,0,0,.05)",
        //Number - Width of the grid lines
        scaleGridLineWidth: 1,
        //Boolean - Whether to show horizontal lines (except X axis)
        scaleShowHorizontalLines: true,
        //Boolean - Whether to show vertical lines (except Y axis)
        scaleShowVerticalLines: true,
        //Boolean - Whether the line is curved between points
        bezierCurve: true,
        //Number - Tension of the bezier curve between points
        bezierCurveTension: 0.3,
        //Boolean - Whether to show a dot for each point
        pointDot: false,
        //Number - Radius of each point dot in pixels
        pointDotRadius: 4,
        //Number - Pixel width of point dot stroke
        pointDotStrokeWidth: 1,
        //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
        pointHitDetectionRadius: 20,
        //Boolean - Whether to show a stroke for datasets
        datasetStroke: true,
        //Number - Pixel width of dataset stroke
        datasetStrokeWidth: 2,
        //Boolean - Whether to fill the dataset with a color
        datasetFill: true,
        //String - A legend template
        legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
        //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
        maintainAspectRatio: true,
        //Boolean - whether to make the chart responsive to window resizing
        responsive: true
      };
      //-------------
      //- LINE CHART -
      //--------------
      var lineChartCanvas = $("#lineChart").get(0).getContext("2d");
      var lineChart = new Chart(lineChartCanvas);
      var lineChartOptions = areaChartOptions;
      lineChartOptions.datasetFill = false;
      lineChart.Line(areaChartData, lineChartOptions);
    });
  </script>

@endpush
