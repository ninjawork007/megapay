@extends('frontend.layouts.app')
@section('content')
@include('frontend.layouts.common.content_title')
<!--Start Section-->
<section class="section-02 history padding-30">
  <div class="container">
    <div class="row">
      @include('frontend.layouts.common.dashboard_menu')
      <div class="col-md-8">
        @include('frontend.layouts.common.alert')
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-md-6">
                <h4 class="float-left">Request Payments(Pending)</h4>
              </div>
              <div class="col-md-6">
                <a href="{{url('request_payment/add')}}" class="btn btn-sm btn-info float-right">Request Payment</a>
              </div>
            </div>
          </div>
          <div class="card-body" style="overflow: auto;">
            @if(!empty($list))
            @foreach($list as $result)
            @if($result->user_id == Auth::user()->id)
            <div class="row">
              <div class="col-md-2">
                {{ date('F', strtotime($result->created_at)) }}<br>
                {{ date('d', strtotime($result->created_at)) }}
              </div>
              <div class="col-md-8">
                <div class="row">
                  <small>Request To : &nbsp;{{ isset($result->receiver_id) ? $result->receiver_first_name.' '.$result->receiver_last_name : $result->email }}</small>
                </div>

                <div class="row">
                  <small>
                  Request sent for :  &nbsp;{{ moneyFormat($result->code, decimalFormat($result->amount)) }}
                  </small>
                </div>
                    <div class="row">
                      <small>

                      <a href="{{url('request_payment/edit/'.$result->id)}}"><span class="text-info">Edit Amount</span></a> | <a href="{{url('request_payment/detail/'.$result->id)}}"><span class="text-info">Detail</span></a>| <a id="cancel_rp" href="{{url('request_payment/cancel/'.$result->id)}}"><span class="text-info" id="cancel_text">Cancel</span></a>

                    </small>
                    </div>
              </div>
              <div class="col-md-2">
                 <small>
                 <strong>+ {{ moneyFormat($result->code, decimalFormat($result->amount)) }}</strong>
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
                  <small>Request From : &nbsp;{{ $result->sender_first_name.' '.$result->sender_last_name }}</small>
                </div>

                <div class="row">
                  <small>
                  Request sent for :  &nbsp;{{ moneyFormat($result->code, decimalFormat($result->amount)) }}
                  </small>
                </div>

                  <div class="row"><small>Fee : &nbsp;
                    {{ moneyFormat($result->code, decimalFormat((($result->amount * $transfer_fee->charge_percentage)/100 + $transfer_fee->charge_fixed))) }}

                    </small>
                  </div>

                    <div class="row">
                      <small>
                      This request is waiting. <a href="{{url('request_payment/accept/'.$result->id)}}"><span class="text-info">Send Payment</span></a> | <a href="{{url('request_payment/detail/'.$result->id)}}"><span class="text-info">Detail</span></a>|<a href="{{url('request_payment/cancel/'.$result->id)}}">Cancel</a>

                    </small>
                    </div>
              </div>
              <div class="col-md-2">
                 <small>
                 <strong> - {{ moneyFormat($result->code, decimalFormat($result->amount)) }} </strong>
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
        <br>
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-md-6">
                <h4 class="float-left">Request Payments(Completed)</h4>
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
            <p class="text-center"><a href="{{url('request_payment/completes')}}"><strong><span class="text-info">View All</span></strong></a></p>
            @else
            No data available!
            @endif
          </div>
        </div>

      </div>
      <!--/col-->
    </div>
    <!--/row-->
  </div>
</section>
<!--End Section-->
@endsection
@section('js')
<script>

  $(document).ready(function() {
    // $("#cancel_rp").click(function(e){
    //     e.preventDefault();
    // });

    $('#cancel_rp').on('click', function() {
        // $(this).off("click").attr('href', "javascript: void(0);");
        $('#cancel_text').text('Cancelling...');
    });
  });
</script>
@endsection