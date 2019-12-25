@extends('admin.layouts.master')

@section('title', 'Payout')

@section('page_content')

<style type="text/css">
/*---------------------confirmation---------------------*/
.confirm-btns {
    width: 35px;
    height: 35px;
    background-color: #58c42b !important;
    border-radius: 50%;
    border: 1px solid #247701;
    color: #FFFFFF;
    text-align: center;
    line-height: 25px;
    font-size: 25px;
    text-shadow: #009933;
    margin: 0 auto;
}
</style>


<div class="box">
   <div class="panel-body">
        <ul class="nav nav-tabs cus" role="tablist">
            <li class="active">
              <a href='{{url("admin/users/edit/$user_id")}}'>Profile</a>
            </li>

            <li>
              <a href="{{url("admin/users/transactions/$user_id")}}">Transactions</a>
            </li>
            <li>
              <a href="{{url("admin/users/wallets/$user_id")}}">Wallets</a>
            </li>
            <li>
              <a href="{{url("admin/users/tickets/$user_id")}}">Tickets</a>
            </li>
            <li>
              <a href="{{url("admin/users/disputes/$user_id")}}">Disputes</a>
            </li>
       </ul>
      <div class="clearfix"></div>
   </div>
</div>



<div class="row">
    <div class="col-md-2">
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-4">
        <div class="pull-right">
            <h3>{{ $name }}</h3>
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

                                <div class="text-center">
                                  <div class="confirm-btns"><i class="fa fa-check"></i></div>
                                </div>
                                <div class="text-center">
                                    <div class="h3 mt6 text-success">Success</div>
                                </div>
                                <div class="text-center"><p><strong>Payout Completed successfully</strong></p></div>
                                    <h5 class="text-center mt10">Amount : {{ moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) }}</h5>
                                </div>
                        </div>
                    </div>

                    <div class="col-md-7">

                        <div style="margin-left: 0 auto">
                            <div style="float: left;">
                                  <a href="{{url("admin/users/withdraw/print/$transaction->id")}}" target="_blank" class="btn button-secondary"><strong>Print</strong></a>
                            </div>
                            <div style="float: right;">
                                <a href="{{url("admin/users/withdraw/create/$user_id")}}" class="btn button-secondary"><strong>Payout Again</strong></a>
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
<script type="text/javascript">
</script>
@endpush