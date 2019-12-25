@extends('frontend.layouts.app')

@section('content')
@include('frontend.layouts.common.content_title')
     <section class="section-05 history padding-30">
        <div class="container">
            <div class="row">

                @include('frontend.layouts.common.dashboard_menu')

                <div class="col-md-8">
                   <div class="padding-bottom30">
                        <div class="row">
                          <div class="col-md-9">
                           <div class="sec-title trans-inline">
                               <h2 class="trans-inline">Transcation ID : {{ $transactions->uuid }} </h2>
                           </div>
                       </div>
                       <div class="col-md-3">
                        @if(isset($defendant_id))
                          <button type="button" class="btn btn-danger float-right" data-toggle="modal" data-target="#myModal">Open dispute</button>
                        @endif
                       </div>
                        </div>
                    </div>
                     <div class="clearfix"></div>
                        <div class="card-header1 serial">
                          <div class="set-Box clearfix">
                                <ul>
                                    <li>Date
                                        <div class="setTop-txt">{{ dateFormat($transactions->created_at) }}</div>
                                    </li>
                                    <li>Type
                                       <div class="setTop-txt">{{ $transactions->type }}</div>
                                    </li>
                                    <li>Status
                                       <div class="setTop-txt active2">{{$transactions->status}}</div>
                                    </li>
                                </ul>
                          </div>

                          <div class="set-Box clearfix mt20">
                                <ul>
                                    <li>Amount
                                        <div class="setTop-txt">
                                            {{ moneyFormat($transactions->currency->symbol, decimalFormat($transactions->total)) }}
                                        </div>
                                    </li>
                                    <li>Fee
                                       <div class="setTop-txt">

                                    {{ moneyFormat($transactions->currency->symbol, decimalFormat(abs(abs($transactions->total)-abs($transactions->subtotal)))) }}

                                       </div>
                                    </li>
                                    <li>Sum
                                       <div class="setTop-txt">
                                           {{ moneyFormat($transactions->currency->symbol, decimalFormat(abs($transactions->subtotal))) }}

                                       </div>
                                    </li>
                                </ul>
                          </div>

                          <div class="set-Box clearfix mt20">
                                <ul>
                                        @if (isset($transactions->type) && $transactions->type == 'Deposit')
                                            <li>Sender
                                                <div class="setTop-txt">{{ isset($transactions->deposit->user) ? $transactions->deposit->user->first_name.' '.$transactions->deposit->user->last_name :"-" }}</div>
                                            </li>
                                            <li>Receiver
                                               <div class="setTop-txt">-</div>
                                            </li>
                                        @elseif (isset($transactions->type) && $transactions->type == 'Transferred')
                                            <li>Sender
                                                <div class="setTop-txt">{{ isset($transactions->transfer->sender) ? $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name :"-" }}</div>
                                            </li>
                                            <li>Receiver
                                               <div class="setTop-txt">{{ isset($transactions->transfer->receiver) ? $transactions->transfer->receiver->first_name.' '.$transactions->transfer->receiver->last_name :"-" }}</div>
                                            </li>


                                        @elseif (isset($transactions->type) && $transactions->type == 'Received')

                                          <li>Sender
                                              <div class="setTop-txt">
                                                  {{ $transactions->transfer->sender->first_name.' '.$transactions->transfer->sender->last_name }}
                                              </div>
                                          </li>
                                          <li>Receiver
                                             <div class="setTop-txt">
                                                  {{ $transactions->transfer->receiver->first_name.' '.$transactions->transfer->receiver->last_name }}
                                              </div>
                                          </li>

                                        @elseif (isset($transactions->type) && $transactions->type == 'Exchange_From')
                                          <li>Sender
                                              <div class="setTop-txt">
                                                 {{ $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name }}
                                              </div>
                                          </li>
                                          <li>Receiver
                                             <div class="setTop-txt">
                                                  -
                                              </div>
                                          </li>

                                        @elseif (isset($transactions->type) && $transactions->type == 'Exchange_To')

                                          <li>Sender
                                              <div class="setTop-txt">
                                                  {{ $transactions->currency_exchange->user->first_name.' '.$transactions->currency_exchange->user->last_name }}
                                              </div>
                                          </li>
                                          <li>Receiver
                                             <div class="setTop-txt">
                                                 -
                                              </div>
                                          </li>

                                        @elseif (isset($transactions->type) && $transactions->type == 'Voucher_Created')
                                          <li>Sender
                                              <div class="setTop-txt">
                                                 {{ $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name }}
                                              </div>
                                          </li>
                                          <li>Receiver
                                             <div class="setTop-txt">
                                                 -
                                              </div>
                                          </li>


                                        @elseif (isset($transactions->type) && $transactions->type == 'Voucher_Activated')
                                        <li>Sender
                                            <div class="setTop-txt">
                                               {{ $transactions->voucher->user->first_name.' '.$transactions->voucher->user->last_name }}
                                            </div>
                                        </li>
                                        <li>Receiver
                                           <div class="setTop-txt">
                                             @if ($transactions->voucher->activator == null)
                                                   -
                                                @else
                                                   {{ $transactions->voucher->activator->first_name.' '.$transactions->voucher->activator->last_name }}
                                                @endif

                                            </div>
                                        </li>


                                        @elseif (isset($transactions->type) && $transactions->type == 'Request_Created')
                                          <li>Sender
                                              <div class="setTop-txt">
                                                 {{ $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name }}
                                              </div>
                                          </li>
                                          <li>Receiver
                                             <div class="setTop-txt">
                                                -
                                              </div>
                                          </li>

                                        @elseif (isset($transactions->type) && $transactions->type == 'Request_Accepted')
                                            <li>Sender
                                                <div class="setTop-txt">
                                                  {{ $transactions->request_payment->user->first_name.' '.$transactions->request_payment->user->last_name }}
                                                </div>
                                            </li>
                                            <li>Receiver
                                               <div class="setTop-txt">
                                                @if ($transactions->request_payment->receiver == null)
                                                    <span>-</span>
                                                @else
                                                    {{ $transactions->request_payment->receiver->first_name.' '.$transactions->request_payment->receiver->last_name }}
                                                @endif
                                                </div>
                                            </li>

                                        @elseif (isset($transactions->type) && $transactions->type == 'Withdrawl')
                                            <li>Sender
                                                <div class="setTop-txt">
                                                   {{ $transactions->withdrawal->user->first_name.' '.$transactions->withdrawal->user->last_name }}
                                                </div>
                                            </li>
                                            <li>Receiver
                                               <div class="setTop-txt">
                                                   -
                                                </div>
                                            </li>


                                        @elseif (isset($transactions->type) && $transactions->type == 'Payment_Sent')
                                            <li>Sender
                                                <div class="setTop-txt">
                                                  -
                                                </div>
                                            </li>
                                            <li>Receiver
                                               <div class="setTop-txt">
                                                   {{ isset($transactions->merchant_payment->merchant->user) ? $transactions->merchant_payment->merchant->user->first_name.' '.$transactions->merchant_payment->merchant->user->last_name :"-" }}
                                                </div>
                                            </li>


                                        @elseif (isset($transactions->type) && $transactions->type == 'Payment_Received')
                                            <li>Sender
                                                <div class="setTop-txt">
                                                  -
                                                </div>
                                            </li>
                                            <li>Receiver
                                               <div class="setTop-txt">{{ isset($transactions->merchant_payment->merchant->user) ? $transactions->merchant_payment->merchant->user->first_name.' '.$transactions->merchant_payment->merchant->user->last_name :"-" }}</div>
                                            </li>
                                        @endif

                                    <li>Comment
                                       <div class="setTop-txt">{{$transactions->note}}</div>
                                    </li>
                                </ul>
                          </div>
                           <div class="clearfix"></div>
                        </div>
                    <div class="clearfix"></div>
                    <div class="mt20 pull-right">
                            <a href="{{url('transactions')}}" class="btn btn-cust">Cancel</a>
                        </div>

                </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>

<!-- The Modal -->
<div class="modal fade" id="myModal">
<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Opening dispute</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <form class="form-horizontal" method="post" action="{{url('dispute/open')}}" id="open_dispute">
      {{csrf_field()}}
      <input type="hidden" name="transaction_id" value="{{$transactions->id}}">
      <input type="hidden" name="claimant_id" value="{{$transactions->user_id}}">
      <input type="hidden" name="defendant_id" value="{{ isset($defendant_id) ? $defendant_id : null }}">
    <!-- Modal body -->
    <div class="modal-body">

       <div class="form-group">
        <label for="title">Titile</label>
        <input type="text" class="form-control" id="title" name="title">
      </div>

      <div class="form-group">
        <label for="reason">Reason</label>
        <select class="form-control" id="reason_id" name="reason_id">
          @if($reasons->count()>0)
          @foreach($reasons as $value)
          <option value="{{$value->id}}">{{$value->title}}</option>
          @endforeach
          @endif
        </select>

      </div>

      <div class="form-group">
        <label for="email">Description</label>
        <textarea name="description" id="description" class="form-control"></textarea>
      </div>

    </div>
    <div class="modal-footer float-right">
      <button type="button" class="btn btn-default btn-custom" data-dismiss="modal">Close</button>
      <button type="submit" class="btn btn-secondary btn-custom">Submit</button>
    </div>
    </form>
  </div>
</div>
</div>
<!-- The Modal -->

</section>

@endsection
@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script>

jQuery.extend(jQuery.validator.messages, {
    required: "{{__('This field is required.')}}",
})

$('#open_dispute').validate({
    rules: {
        title: {
            required: true,
        },
        reason_id: {
            required: true,
        },
        description: {
            required: true,
        }
    }
});

</script>
@endsection