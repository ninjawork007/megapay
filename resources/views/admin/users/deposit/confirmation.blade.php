@extends('admin.layouts.master')

@section('title', 'Deposit')

@section('page_content')

<div class="box">
   <div class="panel-body">
        <ul class="nav nav-tabs cus" role="tablist">
            <li class="active">
              <a href='{{url("admin/users/edit/$users->id")}}'>Profile</a>
            </li>

            <li>
              <a href="{{url("admin/users/transactions/$users->id")}}">Transactions</a>
            </li>
            <li>
              <a href="{{url("admin/users/wallets/$users->id")}}">Wallets</a>
            </li>
            <li>
              <a href="{{url("admin/users/tickets/$users->id")}}">Tickets</a>
            </li>
            <li>
              <a href="{{url("admin/users/disputes/$users->id")}}">Disputes</a>
            </li>
       </ul>
      <div class="clearfix"></div>
   </div>
</div>

<div class="row">
    <div class="col-md-2">
        &nbsp;&nbsp;&nbsp;<button style="margin-top: 15px;"  type="button" class="btn button-secondary btn-flat active">Deposit</button>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-4">
        <div class="pull-right">
            <h3>{{ $users->first_name.' '.$users->last_name }}</h3>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-7">

                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h3 class="text-center"><strong>Details</strong></h3>
                                <div class="row">
                                    <div class="col-md-6 pull-left">Amount</div>
                                    <div class="col-md-6  text-right"><strong>{{ moneyFormat($transInfo['currSymbol'], isset($transInfo['amount']) ? formatNumber($transInfo['amount']) : 0.00) }}</strong></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 pull-left">Fee</div>
                                    <div class="col-md-6 text-right"><strong>{{ moneyFormat($transInfo['currSymbol'], isset($transInfo['fee']) ? formatNumber($transInfo['fee']) : 0.00) }}</strong></div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-md-6 pull-left"><strong>Total</strong></div>
                                    <div class="col-md-6 text-right"><strong>{{ moneyFormat($transInfo['currSymbol'], isset($transInfo['totalAmount']) ? formatNumber($transInfo['totalAmount']) : 0.00) }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div style="margin-left: 0 auto">
                            <div style="float: left;">
                                  <a onclick="window.history.back();" href="#" class="btn button-secondary">
                                     <strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;Back</strong>
                                  </a>
                            </div>
                            <div style="float: right;">
                                <form action="{{ url('admin/users/deposit/storeFromAdmin') }}" style="display: block;" method="POST" accept-charset="UTF-8" id="admin-user-deposit-confirm" novalidate="novalidate">
                                    <input value="{{csrf_token()}}" name="_token" id="token" type="hidden">
                                    <input value="{{$transInfo['totalAmount']}}" name="amount" id="amount" type="hidden">

                                    <button type="submit" class="btn button-secondary" id="confirm_money">
                                        <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i>
                                        <span id="confirm_money_text">
                                            <strong>Confirm&nbsp; <i class="fa fa-angle-right"></i></strong>
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

    $('#admin-user-deposit-confirm').validate({
        rules: {
            amount: {
                required: false,
            },
        },
        submitHandler: function(form)
        {
            $("#confirm_money").attr("disabled", true);
            $(".spinner").show();
            var pretext=$("#confirm_money_text").text();
            $("#confirm_money_text").text('Sending...');
            form.submit();
            setTimeout(function(){
                $("#confirm_money_text").html(pretext + '<i class="fa fa-angle-right"></i>');
                $("#confirm_money").removeAttr("disabled");
                $(".spinner").hide();
            },10000);
        }
    });
</script>
@endpush