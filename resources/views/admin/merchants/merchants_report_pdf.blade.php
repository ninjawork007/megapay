<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>
            Merchants
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
                        Print Date : {{ dateFormat(date('d-m-Y'))}}
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
                                <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="200" height="70">
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
                        <td>ID</td>
                        <td>Type</td>
                        <td>Business Name</td>
                        <td>User</td>
                        <td>Url</td>
                        <td>Group</td>
                        <td>Logo</td>
                        <td>Status</td>
                    </tr>

                    @foreach($merchants as $merchant)

                    <tr style="background-color:#fff; text-align:center; font-size:12px; font-weight:normal;">

                        <td>{{ dateFormat($merchant->created_at) }}</td>

                        <td>{{ (isset($merchant->merchant_uuid)) ? $merchant->merchant_uuid : '-' }}</td>

                        <td>{{ ucfirst($merchant->type) }}</td>

                        <td>{{ $merchant->business_name }}</td>

                        <td>{{ isset($merchant->user) ? $merchant->user->first_name.' '.$merchant->user->last_name :"-" }}</td>

                        <td>{{ $merchant->site_url }}</td>

                        @if (isset($merchant->logo))
                            <td>{{ $merchant->logo }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ isset($merchant->merchant_group) ? $merchant->merchant_group->name: "-" }}</td>

                        <td>{{ $merchant->status }}</td>
                    </tr>

                    @endforeach

                </table>
            </div>
        </div>
    </body>
</html>
