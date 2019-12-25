@extends('user_dashboard.layouts.app')

@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')

                    <div class="card">
                        <div class="card-header">
                            <div class="chart-list float-left">
                                <ul>
                                    <li><a href="{{url('/payouts')}}">@lang('message.dashboard.payout.menu.payouts')</a></li>
                                    <li class="active"><a href="{{url('/payout/setting')}}">@lang('message.dashboard.payout.menu.payout-setting')</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="wap-wed mt20 mb20">
                            <button data-toggle="modal" data-target="#addModal" id="addBtn" style="margin-bottom: 20px"
                                    class="btn btn-cust" type="button">@lang('message.dashboard.payout.payout-setting.add-setting')
                            </button>

                            <div class="table-responsive">
                                @if($payoutSettings->count() > 0)
                                    <table class="table recent_activity">
                                        <thead>
                                        <tr>
                                            <td><strong>@lang('message.dashboard.payout.payout-setting.payout-type')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.payout-setting.account')</strong></td>
                                            <td><strong>@lang('message.dashboard.payout.payout-setting.action')</strong></td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($payoutSettings as $row)
                                            <tr class="row_id_{{$row->id}}">
                                                <td>{{$row->paymentMethod->name}}</td>

                                                <td>
                                                    @if($row->paymentMethod->name == "Paypal")
                                                        {{$row->email }}
                                                    @elseif($row->paymentMethod->name == "Payeer")
                                                        {{ $row->account_number }}
                                                    @elseif($row->paymentMethod->name == "PerfectMoney")
                                                        {{ $row->account_number }}
                                                    @else
                                                        {{$row->account_name}} (*****{{substr($row->account_number,-4)}}
                                                        )<br/>
                                                        {{$row->bank_name}}
                                                    @endif
                                                </td>
                                                <td>
                                                    <a data-id="{{$row->id}}" data-type="{{$row->type}}" data-obj="{{json_encode($row->getAttributes())}}" class="btn btn-sm btn-info edit-setting"><i class="fa fa-pencil"></i></a>

                                                    <form action="{{url('payout/setting/delete')}}" method="post" style="display: inline">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{$row->id}}">
                                                        <a class="btn btn-sm btn-danger" data-toggle="modal"
                                                           data-target="#delete-warning-modal" data-title="{{__("Delete Data")}}"
                                                           data-message="{{__("Are you sure you want to delete this Data ?")}}"
                                                           data-row="{{$row->id}}"
                                                           href=""><i class="fa fa-trash"></i></a>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <h5 style="padding: 15px 10px; ">@lang('message.dashboard.payout.list.not-found')</h5>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer">
                            {{ $payoutSettings->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- addModal Modal-->
    <div class="modal fade" id="addModal" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@lang('message.dashboard.payout.payout-setting.modal.title')</h4>
                </div>
                <div class="modal-body">
                    <form id="payoutSettingForm" method="post">
                        {{csrf_field()}}
                        <div id="settingId"></div>
                        <div class="col-md-10">
                            <div class="form-group">
                                <label>@lang('message.dashboard.payout.payout-setting.payout-type')</label>
                                <select name="type" id="type" class="form-control">
                                    @foreach($paymentMethods as $method)
                                        <option value="{{$method->id}}">{{$method->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="bankForm">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.bank-account-holder-name')</label>
                                    <input name="account_name" id="" class="form-control">

                                </div>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.account-number')</label>
                                    <input name="account_number" id="" class="form-control">

                                </div>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.swift-code')</label>
                                    <input name="swift_code" id="" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.bank-name')</label>
                                    <input name="bank_name" id="" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.branch-name')</label>
                                    <input name="branch_name" id="" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.branch-city')</label>
                                    <input name="branch_city" id="" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.branch-address')</label>
                                    <input name="branch_address" id="" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.country')</label>
                                    <select name="country" id="" class="form-control">
                                        @foreach($countries as $country)
                                            <option value="{{$country->id}}">{{$country->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div id="paypalForm" style="margin:0 auto;display: none">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.email')</label>
                                    <input name="email" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div id="perfectMoneyForm" style="margin:0 auto;display: none">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.perfect-money-account-number')</label>
                                    <input name="perfect_money_account_no" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div id="payeerForm" style="margin:0 auto;display: none">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>@lang('message.dashboard.payout.payout-setting.modal.payeer-account-number')</label>
                                    <input name="payeer_account_no" class="form-control">
                                </div>
                            </div>
                        </div>



                        <div class="card-footer" style="background-color: inherit;border: 0">
                            <div class="col-md-3" style="margin: 0 auto">
                                <button type="submit" class="btn btn-cust col-12" id="submit_btn">
                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span
                                            id="submit_text">@lang('message.form.submit')</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('message.form.close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

    <script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

    <script>
        $(document).ready(function(){
            $('#bankForm').hide();
            $('#payeerForm').hide();
            $('#perfectMoneyForm').hide();
            $('#paypalForm').css('display', 'flex');
        });
        $('#type').on('change', function () {
            if ($('option:selected', this).text() == 'Paypal') {
                $('#bankForm').hide();
                $('#payeerForm').hide();
                $('#perfectMoneyForm').hide();
                $('#paypalForm').css('display', 'flex');
            } else if($('option:selected', this).text() == 'Bank'){
                $('#bankForm').css('display', 'flex');
                $('#paypalForm').hide();
                $('#payeerForm').hide();
                $('#perfectMoneyForm').hide();
            } else if($('option:selected', this).text() == 'Payeer'){
                $('#bankForm').hide();
                $('#paypalForm').hide();
                $('#payeerForm').css('display', 'flex');
                $('#perfectMoneyForm').hide();
            }else if($('option:selected', this).text() == 'PerfectMoney'){
                $('#bankForm').hide();
                $('#paypalForm').hide();
                $('#payeerForm').hide();
                $('#perfectMoneyForm').css('display', 'flex');
            }
        });
        $('#addBtn').on('click', function () {
            $('#settingId').html('');
            var form = $('#payoutSettingForm');
            form.attr('action', '{{url('payout/setting/store')}}');
            $.each(form[0].elements, function (index, elem) {
                if (elem.name != "_token" && elem.name != "setting_id") {
                    $(this).val("");
                    if (elem.name == "type") {
                        $(this).val(1).change().removeAttr('disabled');
                    }
                }
            });
        });

        jQuery.extend(jQuery.validator.messages, {
            required: "{{__('This field is required.')}}",
        })

        $('#payoutSettingForm').validate({
            rules: {
                type: {
                    required: true
                },
                account_name: {
                    required: true
                },
                account_number: {
                    required: true
                },
                swift_code: {
                    required: true
                },
                bank_name: {
                    required: true
                },
                branch_name: {
                    required: true
                },
                branch_city: {
                    required: true
                },
                branch_address: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true
                },
                country: {
                    required: true
                },
                perfect_money_account_no: {
                    required: true
                },
                payeer_account_no: {
                    required: true
                }

            },
            submitHandler: function (form) {
                $("#submit_btn").attr("disabled", true);
                $(".spinner").show();
                $("#submit_text").text('Submitting...');
                form.submit();
            }
        });


        $('.edit-setting').on('click', function (e)
        {
            e.preventDefault();
            var obj = JSON.parse($(this).attr('data-obj'));
            var settingId = $(this).attr('data-id');
            var form = $('#payoutSettingForm');
            form.attr('action', '{{url('payout/setting/update')}}');
            form.attr('method', 'post');
            var html = '<input type="hidden" name="setting_id" value="' + settingId + '">';
            $('#settingId').html(html);
            if (obj.type == 6) {
                $.each(form[0].elements, function (index, elem) {
                    switch (elem.name) {
                        case "type":
                            $(this).val(obj.type).change().attr('disabled', 'true');
                            break;
                        case "account_name":
                            $(this).val(obj.account_name);
                            break;
                        case "account_number":
                            $(this).val(obj.account_number);
                            break;
                        case "branch_address":
                            $(this).val(obj.bank_branch_address);
                            break;
                        case "branch_city":
                            $(this).val(obj.bank_branch_city);
                            break;
                        case "branch_name":
                            $(this).val(obj.bank_branch_name);
                            break;
                        case "bank_name":
                            $(this).val(obj.bank_name);
                            break;
                        case "country":
                            $(this).val(obj.country);
                            break;
                        case "swift_code":
                            $(this).val(obj.swift_code);
                            break;
                        default:

                            break;
                    }
                })
            } else if(obj.type == 3) {
                $.each(form[0].elements, function (index, elem) {
                    if (elem.name == 'email') {
                        $(this).val(obj.email);
                    } else if (elem.name == 'type') {
                        $(this).val(obj.type).change().attr('disabled', 'true');
                    }
                })
            }else if(obj.type == 8) {
                $.each(form[0].elements, function (index, elem) {
                    if (elem.name == 'payeer_account_no') {
                        $(this).val(obj.account_number);
                    } else if (elem.name == 'type') {
                        $(this).val(obj.type).change().attr('disabled', 'true');
                    }
                })
            }else if(obj.type == 9) {
                $.each(form[0].elements, function (index, elem) {
                    if (elem.name == 'perfect_money_account_no') {
                        $(this).val(obj.account_number);
                    } else if (elem.name == 'type') {
                        $(this).val(obj.type).change().attr('disabled', 'true');
                    }
                })
            }
            $('#addModal').modal();
        });
    </script>
@endsection
