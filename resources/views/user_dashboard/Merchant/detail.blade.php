@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-xs-12 mb20 marginTopPlus">
                    <div class="card">
                        <div class="card-header">
                            <div class="chart-list float-left">
                                <ul>
                                    <li class="active">@lang('message.dashboard.merchant.menu.details')
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.dashboard.merchant.details.merchant-id')</h4>
                                        <p>{{ $merchant->merchant_uuid }}</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.dashboard.merchant.details.business-name')</h4>
                                        <p>{{ $merchant->business_name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.dashboard.merchant.details.site-url')</h4>
                                        <p>{{ $merchant->site_url }}</p>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.form.currency')</h4>
                                        <p>{{ !empty($merchant->currency->code) ? $merchant->currency->code : $defaultWallet->currency->code }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.dashboard.merchant.details.status')</h4>
                                        <p>
                                            @if ($merchant->status == 'Moderation')
                                                <span class="badge badge-warning">@lang('message.dashboard.merchant.table.moderation')</span>
                                            @elseif ($merchant->status == 'Disapproved')
                                                <span class="badge badge-danger">@lang('message.dashboard.merchant.table.disapproved')</span>
                                            @elseif ($merchant->status == 'Approved')
                                                <span class="badge badge-success">@lang('message.dashboard.merchant.table.approved')</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.dashboard.merchant.details.note')</h4>
                                        <p>{{ $merchant->note }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>@lang('message.dashboard.merchant.details.date')</h4>
                                        <p>{{ dateFormat($merchant->create_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <a class="btn btn-cust" href="{{url('merchant/edit/'.$merchant->id)}}">@lang('message.form.edit')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section-->
@endsection
@section('js')
    <script>
    </script>
@endsection