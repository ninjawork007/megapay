<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            Vouchers
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
                            @if (isset($company_logo))
                                <img src="{{ url('public/images/logos/'.$company_logo) }}">
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
                        <td>Code</td>
                        <td>Amount</td>
                        <td>Currency</td>
                        <td>Redeemed</td>
                        <td>Status</td>
                    </tr>

                    @foreach($vouchers as $voucher)
                        <tr style="background-color:#fff; text-align:center; font-size:12px; font-weight:normal;">
                            <td>{{ dateFormat($voucher->created_at) }}</td>

                            <td>{{ isset($voucher->user) ? $voucher->user->first_name.' '.$voucher->user->last_name :"-" }}</td>

                            <td>{{ $voucher->code }}</td>

                            <td>{{ formatNumber($voucher->amount) }}</td>

                            <td>{{ $voucher->currency->code }}</td>

                            <td>{{ $voucher->redeemed }}</td>

                            <td>{{ ($voucher->status == 'Blocked') ? 'Cancelled' : $voucher->status }}</td>
                        </tr>
                    @endforeach

                </table>
            </div>
        </div>
    </body>
</html>
