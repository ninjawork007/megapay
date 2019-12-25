<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            Exchanges
        </title>
    </head>
    <style>
        body {
        font-family: "DeJaVu Sans", Helvetica, sans-serif;
        color: #121212;
        line-height: 15px;
    }

    table, tr, td {
        padding: 6px 6px;
        border: 1px solid black;
    }

    tr {
        height: 40px;
    }

     /*logo -- css*/
    .setting-img{
        overflow: hidden;
        max-width: 100%;
    }
    .img-wrap-general-logo {
        /*width: 300px;*/
        overflow: hidden;
        margin: 5px;
        background: rgba(74, 111, 197, 0.9) !important;
        /*height: 100px;*/
        max-width: 100%;
    }
    .img-wrap-general-logo > img {
        max-width: 100%;
        height: auto !important;
        max-height: 100%;
        width: auto !important;
        object-fit: contain;
    }
    /*logo -- css*/
    </style>

    <body>
        <div style="width:100%; margin:0px auto;">
            <div style="height:80px">
                <div style="width:80%; float:left; font-size:13px; color:#383838; font-weight:400;">
                    <div>
                        <strong>
                            {{ ucwords(Session::get('name')) }}
                        </strong>
                    </div>
                    <br>
                    <div>
                        Period : {{ $date_range }}
                    </div>
                    <br>
                    <div>
                        Print Date : {{ dateFormat(\Carbon\Carbon::now()->toDateString())}}
                    </div>
                </div>
                <div style="width:20%; float:left;font-size:15px; color:#383838; font-weight:400;">
                    <div>
                        <div>
                            @if (!empty($company_logo))
                                <div class="setting-img">
                                    <div class="img-wrap-general-logo">
                                        <img src="{{ url('public/images/logos/'.$company_logo) }}" class="img-responsive">
                                    </div>
                                </div>
                            @else
                                <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="120" height="80">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both">
            </div>
            <div style="margin-top:30px;">
                <table style="width:100%; border-radius:1px;  border-collapse: collapse;">

                    <tr style="background-color:#f0f0f0;text-align:center; font-size:12px; font-weight:bold;">

                        <td>Date</td>
                        <td>User</td>
                        <td>Amount</td>
                        <td>Fees</td>
                        <td>Total</td>
                        <td>Rate</td>
                        <td>From</td>
                        <td>To</td>
                        <td>Status</td>
                    </tr>

                    @foreach($exchanges as $exchange)

                    <tr style="background-color:#fff; text-align:center; font-size:12px; font-weight:normal;">

                        <td>{{ dateFormat($exchange->created_at) }}</td>

                        <td>{{ $exchange->first_name.' '.$exchange->last_name }}</td>

                        <td>
                          @if($exchange->type == 'Out')
                            @if ($exchange->amount > 0)
                                {{ formatNumber($exchange->amount) }}
                            @endif
                          @elseif($exchange->type == 'In')
                            @if ($exchange->amount > 0)
                                {{ formatNumber($exchange->amount) }}
                            @endif
                          @endif
                        </td>

                        <td>{{ ($exchange->fee == 0) ? '-' : formatNumber($exchange->fee) }}</td>

                        @php
                            $total = $exchange->fee + $exchange->amount;
                        @endphp

                        <td>
                          @if($exchange->type == 'Out')
                            @if ($total > 0)
                                {{ '-'.formatNumber($total) }}
                            @endif
                          @elseif($exchange->type == 'In')
                            @if ($total > 0)
                                {{ '-'.formatNumber($total) }}
                            @endif
                          @endif
                        </td>

                        <td>{{  moneyFormat($exchange->tc_symbol, formatNumber($exchange->exchange_rate)) }}</td>

                        @if($exchange->type == 'Out')
                            <td>{{$exchange->fc_code}}</td>
                        @else
                            <td>{{$exchange->fc_code}} </td>
                        @endif

                        @if($exchange->type == 'In')
                            <td>{{$exchange->tc_code}}</td>
                        @else
                            <td>{{$exchange->tc_code}}</td>
                        @endif

                        <td>{{ ($exchange->status == 'Blocked') ? 'Cancelled' : $exchange->status }}</td>

                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </body>
</html>
