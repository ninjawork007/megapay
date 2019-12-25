<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            Transfers
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
                        <td>Currency</td>
                        <td>Receiver</td>
                        <td>Status</td>
                    </tr>

                    @foreach($transfers as $transfer)

                    <tr style="background-color:#fff; text-align:center; font-size:12px; font-weight:normal;">

                        <td>{{ dateFormat($transfer->created_at) }}</td>

                        <td>{{ isset($transfer->sender) ? $transfer->sender->first_name.' '.$transfer->sender->last_name :"-" }}</td>

                        <td>{{ formatNumber($transfer->amount) }}</td>

                        <td>{{ ($transfer->fee == 0) ? '-' : formatNumber($transfer->fee) }}</td>

                        <td>{{ '-'.formatNumber($transfer->amount + $transfer->fee) }}</td>

                        <td>{{ $transfer->currency->code }}</td>

                        <td>
                            @if ($transfer->receiver)
                                {{ $transfer->receiver->first_name.' '.$transfer->receiver->last_name }}
                            @elseif ($transfer->email)
                                {{ $transfer->email }}
                            @elseif ($transfer->phone)
                                {{ $transfer->phone }}
                            @else
                                {{ '-' }}
                            @endif
                        </td>

                        <td>{{ (($transfer->status == 'Blocked') ? "Cancelled" :(($transfer->status == 'Refund') ? "Refunded" : $transfer->status)) }}</td>

                    </tr>
                    @endforeach

                </table>
            </div>
        </div>
    </body>
</html>
