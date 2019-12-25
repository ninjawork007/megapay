@extends('user_dashboard.layouts.app')
@section('css')
    <!-- sweetalert -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/sweetalert.css')}}">
@endsection
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    <div class="right mb10">
                        <a href="{{url('/payout')}}" class="btn btn-cust ticket-btn"><i class="fa fa-arrow-up"></i>&nbsp;@lang('message.dashboard.payout.new-payout.title')</a>
                    </div>
                    <div class="clearfix"></div>

                    <div class="card">
                        <div class="card-header">
                                <div class="chart-list float-left">
                                    <ul>
                                        <li class="active"><a href="{{url('/payouts')}}">@lang('message.dashboard.payout.menu.payouts')</a></li>
                                        <li><a href="{{url('/payout/setting')}}">@lang('message.dashboard.payout.menu.payout-setting')</a></li>
                                    </ul>
                              </div>
                        </div>

                             <div class="table-responsive">
                                @if($payouts->count() > 0)
                                <table class="table recent_activity">
                                    <thead>
                                        <tr>
                                            <td><strong>@lang('message.dashboard.payout.list.date')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.list.method')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.list.method-info')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.list.fee')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.list.amount')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.list.currency')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.list.status')</strong></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payouts as $payout)

                                        <tr>
                                            <td>{{ dateFormat($payout->created_at) }}</td>
                                            <td>{{ ($payout->payment_method->name == "Mts") ? getCompanyName() : $payout->payment_method->name }}</td>

                                            <td>
                                                @if($payout->payment_method->name == "Bank")
                                                    @if ($payout->withdrawal_detail)
                                                        {{$payout->withdrawal_detail->account_name}} (*****{{substr($payout->withdrawal_detail->account_number,-4)}}
                                                        )<br/>
                                                        {{$payout->withdrawal_detail->bank_name}}
                                                    @else
                                                        {{ '-' }}
                                                    @endif
                                                @elseif($payout->payment_method->name == "Mts")
                                                    {{ '-' }}
                                                @else
                                                    {{ $payout->payment_method_info }}
                                                @endif
                                            </td>

                                            <td>{{ formatNumber($payout->amount-$payout->subtotal) }}</td>
                                            <td>{{ formatNumber($payout->amount) }}</td>
                                            <td>{{ $payout->currency->code }}</td>
                                            <td>
                                               @php
                                                    if ($payout->status == 'Success') {
                                                        echo '<span class="badge badge-success">'.$payout->status.'</span>';
                                                    }
                                                    elseif ($payout->status == 'Pending') {
                                                        echo '<span class="badge badge-primary">'.$payout->status.'</span>';
                                                    }
                                                    elseif ($payout->status == 'Blocked') {
                                                        echo '<span class="badge badge-danger">Cancelled</span>';
                                                    }
                                                @endphp
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                                @else
                                  <h5 style="padding: 15px 10px; ">@lang('message.dashboard.payout.list.not-found')</h5>
                                @endif


                        <div class="card-footer">
                            {{ $payouts->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('js')
<script src="{{asset('public/user_dashboard/js/sweetalert.min.js')}}" type="text/javascript"></script>
<script>
    $(document).ready(function()
    {
        var payoutSetting = {!! count($payoutSettings) !!}
        $( ".ticket-btn" ).click(function()
        {
            if ( payoutSetting <= 0 )
            {
                swal({
                        title: "{{__("Error")}}!",
                        text: "{{__("No Payout Setting Exists!")}}",
                        type: "error"
                    }
                );
                event.preventDefault();
            }
        });
    });
</script>
@endsection
