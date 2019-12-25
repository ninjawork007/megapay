@extends('user_dashboard.layouts.app')

@section('content')
<section class="section-06 history padding-30">
	<div class="container">
		<div class="row">
			<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
				@include('user_dashboard.layouts.common.alert')
				<form action="{{url('merchant/update')}}"  method="post" accept-charset="utf-8" id="EditForm" enctype="multipart/form-data">
					<input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
					<input type="hidden" value="{{$merchant->id}}" name="id" id="id">
					<div class="card">
						<div class="card-header">
							<div class="chart-list float-left">
								<ul>
                                    <li class="active">@lang('message.dashboard.merchant.menu.edit-merchant')</li>
								</ul>
							</div>
						</div>
						<div class="wap-wed mt20 mb20">
							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.name')</label>
								<input class="form-control" name="business_name" id="business_name"  type="text" value="{{$merchant->business_name}}">

								@if($errors->has('business_name'))
								<span class="help-block">
									<strong class="text-danger">{{ $errors->first('business_name') }}</strong>
								</span>
								@endif
							</div>

							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.site-url')</label>
								<input class="form-control" name="site_url" id="site_url"  placeholder="http://www.example.com" type="text" value="{{$merchant->site_url}}">

								@if($errors->has('site_url'))
								<span class="help-block">
									<strong class="text-danger">{{ $errors->first('site_url') }}</strong>
								</span>
								@endif
							</div>

							<div class="form-group">
                            	<label>@lang('message.dashboard.send-request.common.currency')</label>
                                <select class="form-control" name="currency_id">
                                    <!--pm_v2.3-->
                                    @foreach($activeCurrencies as $result)
                                            @if (!empty($merchant->currency_id))
                                            	<option value="{{ $result->id }}" {{ $merchant->currency_id == $result->id ? 'selected="selected"' : ''}}>{{ $result->code }}</option>
                                           	@else
                                            	<option value="{{ $result->id }}" {{ $defaultWallet->currency_id == $result->id ? 'selected="selected"' : ''}}>{{ $result->code }}</option>
                                            @endif
                                    @endforeach
                                </select>
                            </div>

							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.type')</label>
								<input readonly class="form-control" value="{{ucfirst($merchant->type)}}">
							</div>

							<div class="form-group">
								<label>@lang('message.dashboard.merchant.edit.comment-for-administration')</label>
								<textarea name="note" class="form-control" id="note">{{$merchant->note}}</textarea>
								@if($errors->has('note'))
									<span class="help-block">
										<strong class="text-danger">{{ $errors->first('note') }}</strong>
									</span>
								@endif
							</div>

							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.logo')</label>
								<input class="form-control" name="logo" id="logo" type="file">

								@if($errors->has('logo'))
								<span class="help-block">
									<strong class="text-danger">{{ $errors->first('logo') }}</strong>
								</span>
								@endif

								@if($merchant->logo)
									<p style="height: 70px;width: 70px;"><img src="{{url('public/user_dashboard/merchant/thumb/').'/'.$merchant->logo}}"></p>
								@endif
							</div>

						</div>
						<div class="card-footer">
							<button type="submit" class="btn btn-cust col-12" id="merchant_update">
	                  			<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="merchant_update_text">@lang('message.dashboard.button.update')</span>
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
    url: "{{__("Please enter a valid URL.")}}",
})

$('#EditForm').validate({
	rules: {
		business_name: {
			required: true,
		},
		site_url: {
			required: true,
			url: true,
		},
		password: {
			required: true,
		},
		note: {
			required: true,
		},
		logo: {
            extension: "png|jpg|jpeg|gif|bmp",
        },
	},
	messages: {
      logo: {
        extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
      }
    },
	submitHandler: function(form)
    {
        $("#merchant_update").attr("disabled", true);
        $(".spinner").show();
        $("#merchant_update_text").text('Updating...');
        form.submit();
    }
});

</script>
@endsection