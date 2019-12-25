@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
				<div class="col-md-12 col-xs-12 mb20 marginTopPlus">  
					@include('user_dashboard.layouts.common.alert')	
                <div class="card">
                    <div class="card-header">
						 	<div class="chart-list float-left">
								<ul>
									<li class="active"><a href="{{url('/exchanges')}}">list</a></li>
									<li><a href="{{url('/exchange')}}">New Exchange</a></li>
								</ul>								  
						  </div>
                    </div>
                     <div class="wap-wed mt20 mb20">	 	
          <div class="card-body" style="overflow: auto;">
            @if($list->count() > 0)
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Exchange From</th>
                  <th>Exchange To</th>
                  <th>Exchange Rate</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($list as $result)
                <tr>
                  @if($result->type == 'Out')
                  <td>
                    {{$defaultCurrency->code}}
                  </td>
                  @else
                  <td>
                    {{$result->code}}
                  </td>
                  @endif
                  @if($result->type == 'In')
                  <td>
                    {{$defaultCurrency->code}}
                  </td>
                  @else
                  <td>
                    {{$result->code}}
                  </td>
                  @endif
                  <td>{{ decimalFormat($result->exchange_rate) }}</td>
                  <td>
                    @if($result->type == 'Out')
                    {{-- {{$defaultCurrency->symbol}} --}}
                    {{  moneyFormat($defaultCurrency->symbol, decimalFormat($result->amount)) }}
                    @else
                    {{-- {{$result->symbol}} --}}
                    {{  moneyFormat($result->symbol, decimalFormat($result->amount)) }}
                    @endif
                    {{-- {{  moneyFormat($result->symbol, decimalFormat($result->amount)) }} --}}
                  </td>
                  <td>{{ dateFormat($result->created_at) }}</td>
                  <td><a href="{{url('exchange/view/'.$result->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a></td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            <h4>Data not found!</h4>
            @endif
          </div>

					 </div>
					
                    <div class="card-footer">
						{{ $list->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
              
            </div>
            </div>
        </div>
    </section>

@endsection
@section('js')

<script type="text/javascript">
	
</script>

@endsection