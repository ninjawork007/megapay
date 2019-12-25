@extends('user_dashboard.layouts.app')
@section('content')
<!--Start Section-->
<section class="section-06 history padding-30">
  <div class="container">
    <div class="row">
      <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
        <div class="card">
          <div class="card-header">
           <h4 class=""> @lang('message.dashboard.dispute.dispute')</h4>
          </div>
          <div class="wap-wed mt20 mb20">

            @if($list->count() > 0)
              @foreach($list as $result)
              <div class="card-body-custom">
                <div class="row">
                  <div class="col-md-10">
                    <div class="h4">@lang('message.dashboard.dispute.title') :<span class="ash-font"> {{$result->title}} </span></div>

                    <h5 class="mt10"><strong><ins>@lang('message.dashboard.dispute.dispute-id')</ins></strong>: {{ isset($result->code) ? $result->code :"-" }}</h5>

                    <h5 class="mt10"><strong><ins>@lang('message.dashboard.dispute.transaction-id')</ins></strong>: {{ isset($result->transaction) ? $result->transaction->uuid :"-" }}</h5>

                    @if(Auth::user()->id != $result->claimant_id)
                    <div class="mt10"><strong><ins>@lang('message.dashboard.dispute.claimant')</ins></strong> :
                        {{ $result->claimant->first_name .' '.$result->claimant->last_name}}
                    </div>
                    @endif

                    @if(Auth::user()->id != $result->defendant_id)
                    <div class="mt10"><strong><ins>@lang('message.dashboard.dispute.defendant')</ins></strong> :
                      {{ $result->defendant->first_name .' '.$result->defendant->last_name }}
                    </div>
                    @endif

                    <div class="mt10"><strong><ins>@lang('message.dashboard.dispute.created-at')</ins></strong> : {{ dateFormat($result->created_at) }} </div>
                    <div class="mt10"><strong>@lang('message.dashboard.dispute.status')</strong> :

                  @if($result->status =='Open')
                    <span class="badge badge-primary">@lang('message.dashboard.dispute.status-type.open')</span>
                  @elseif($result->status =='Solve')
                    <span class="badge badge-success">@lang('message.dashboard.dispute.status-type.solved')</span>
                  @elseif($result->status =='Close')
                    <span class="badge badge-danger">@lang('message.dashboard.dispute.status-type.closed')</span>
                  @endif

                    </div>
                  </div>
                  <div class="col-md-2">
                    <p class="text-right">
                      <a href='{{url("dispute/discussion/$result->id") }}' class="btn btn-cust">
                        @lang('message.dashboard.button.details')
                      </a>
                    </p>
                  </div>
                </div>
              </div>
            <hr>

            @endforeach
            @else
            <h4>@lang('message.dashboard.dispute.no-dispute')</h4>
            <br>
            @endif

          </div>
          <div class="card-footer">
             {{ $list->links('vendor.pagination.bootstrap-4') }}
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