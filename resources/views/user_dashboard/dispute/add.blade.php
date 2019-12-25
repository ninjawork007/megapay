@extends('user_dashboard.layouts.app')

@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">

				<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
					@include('user_dashboard.layouts.common.alert')
				     	<form method="POST" action="{{url('dispute/open')}}" id="dispute_add_form" accept-charset='UTF-8'>
						<input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
						<input type="hidden" name="transaction_id" value="{{$transaction->id}}">
						<input type="hidden" name="claimant_id" value="{{$transaction->user_id}}">
						<input type="hidden" name="defendant_id" value="{{$transaction->end_user_id}}">

                <div class="card">
                     <div class="wap-wed mt20 mb20">

						  <div class="form-group">
							<label for="exampleInputEmail1">@lang('message.dashboard.dispute.title')</label>
							<input type="text" class="form-control" value="{{old('title')}}" name="title" id="title">
						  </div>

							<div class="form-group">
							<label for="exampleInputPassword1">@lang('message.dashboard.dispute.discussion.sidebar.reason')</label>
								<select class="form-control" name="reason_id" id="reason_id">
						    		@foreach ($reasons as $reason)
						    			<option value="{{ $reason->id }}">{{ $reason->title }}</option>
						    		@endforeach
							    </select>
							</div>

						  <div class="form-group">
							<label for="exampleInputEmail1">@lang('message.dashboard.dispute.description')</label>
			                    <textarea class="form-control" rows="5" name="description" id="description">{{old('description')}}</textarea>
							</textarea>
						  </div>
					 </div>

                    <div class="card-footer">
                  		<button type="submit" class="btn btn-cust col-12" id="submit">
                  			<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="send_text">@lang('message.dashboard.button.submit')</span>
                  		</button>
                    </div>
                </div>
                </form>
            </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>
    </section>
@endsection

@section('js')
<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>
<script>

jQuery.extend(jQuery.validator.messages, {
    required: "{{__('This field is required.')}}",
})

$('#dispute_add_form').validate({
    rules: {
        title: {
            required: true,
        },
        description: {
            required: true,
        },
    },
    submitHandler: function(form)
    {
        $("#submit").attr("disabled", true);
        $(".spinner").show();
        $("#send_text").text('Submit...');
        form.submit();
    }
});
</script>
@endsection