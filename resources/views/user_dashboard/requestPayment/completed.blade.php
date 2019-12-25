@extends('frontend.layouts.app')
@section('content')
@include('frontend.layouts.common.content_title')
<section class="section-02 history padding-30">
  <div class="container">
    <div class="row">
       @include('frontend.layouts.common.dashboard_menu')
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-md-6">
                <h4 class="float-left">Completed request payments</h4>
              </div>
              <div class="col-md-6">
                <a href="{{url('request_payment/add')}}" class="btn btn-sm btn-info float-right">Request Payment</a>
              </div>
            </div>
          </div>
          <div class="card-body" style="overflow: auto;">
            @if(!empty($listComplets))
            @foreach($listComplets as $result)
            @if($result->user_id == Auth::user()->id)
            <div class="row">
              <div class="col-md-2">
                {{ date('F', strtotime($result->created_at)) }}<br>
                {{ date('d', strtotime($result->created_at)) }}
              </div>

              <div class="col-md-8">
                <div class="row">
                  <small>{{ isset($result->receiver_id) ? $result->receiver_first_name.' '.$result->receiver_last_name : $result->email }}</small>
                </div>

                <div class="row">
                  <small>
                  <strong>{{$result->status}}</strong> -  Request send for {{ moneyFormat($result->code, decimalFormat($result->amount)) }}
                  </small>
                </div>
              </div>

              <div class="col-md-2">
                 <small>
                 <strong>+ {{ moneyFormat($result->code, decimalFormat($result->accept_amount)) }}</strong>
                </small>
              </div>
            </div>
<br>
            @else

            <div class="row">
              <div class="col-md-2 text-center">
                {{ date('F', strtotime($result->created_at)) }}<br>
                {{ date('d', strtotime($result->created_at)) }}
              </div>

              <div class="col-md-8">
                <div class="row">
                  <small>{{ $result->sender_first_name.' '.$result->sender_last_name }}</small>
                </div>

                <div class="row">
                  <small>
                  <strong>{{$result->status}}</strong> - Request send for {{ moneyFormat($result->code, decimalFormat($result->amount)) }}
                  </small>
                </div>

              </div>
              <div class="col-md-2">
                <br>
                <br>
                 <small>
                 <strong> - {{ moneyFormat($result->code, decimalFormat($result->accept_amount)) }} </strong>
                </small>
              </div>
            </div>
            <br>
            @endif
            @endforeach
            
            @else
            No data available!
            @endif
          </div>
      </div>
    </div>
    </div>
  </div>
</section>
@endsection
@section('js')
<script>
</script>
@endsection