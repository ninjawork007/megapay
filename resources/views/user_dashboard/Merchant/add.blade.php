@extends('user_dashboard.layouts.app')
@section('content')
<section class="section-06 history padding-30">
	<div class="container">
		<div class="row">
			<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
				@include('user_dashboard.layouts.common.alert')

				<form action="{{url('merchant/store')}}"  method="post" enctype="multipart/form-data" accept-charset="utf-8" id="merchant_add_form">
					<input type="hidden" value="{{csrf_token()}}" name="_token" id="token">

					<div class="card">
						<div class="card-header">
							<h4>@lang('message.dashboard.button.new-merchant')</h4>
						</div>
						<div class="wap-wed mt20 mb20">
							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.name')</label>
								<input value="{{Input::old('business_name')}}" class="form-control" name="business_name" id="business_name"  type="text">
								@if($errors->has('business_name'))
								<span class="help-block">
									<strong class="text-danger">{{ $errors->first('business_name') }}</strong>
								</span>
								@endif
							</div>

							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.site-url')</label>
								<input value="{{Input::old('site_url')}}" class="form-control" name="site_url" id="site_url"  placeholder="http://www.example.com" type="text">
								@if($errors->has('site_url'))
								<span class="help-block">
									<strong class="text-danger">{{ $errors->first('site_url') }}</strong>
								</span>
								@endif
							</div>

                            <div class="form-group">
                            <label for="exampleInputPassword1">@lang('message.dashboard.send-request.common.currency')</label>
                                <select class="form-control" name="currency_id">
                                    <!--pm_v2.3-->
                                    @foreach($activeCurrencies as $result)
                                            <option value="{{ $result->id }}" {{ $defaultWallet->currency_id == $result->id ? 'selected="selected"' : '' }}>{{ $result->code }}</option>
                                    @endforeach
                                </select>
                            </div>

							<div class="form-group">
								<label>@lang('message.dashboard.merchant.add.type')</label>
								<select class="form-control" name="type" id="type">
									<option <?= old('type')=='standard'?'selected':''?> value="standard">Standard</option>
									<option <?= old('type')=='express'?'selected':''?> value="express">Express</option>
								</select>
								@if($errors->has('type'))
								<span class="help-block">
									<strong class="text-danger">{{ $errors->first('type') }}</strong>
								</span>
								@endif
							</div>

								<div class="form-group">
									<label>@lang('message.dashboard.merchant.add.note')</label>
									<textarea name="note" class="form-control" id="note">{{Input::old('note')}}</textarea>
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
							</div>


						</div>
						<div class="card-footer">
							<button type="submit" class="btn btn-cust col-12" id="merchant_create">
	                  			<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="merchant_create_text">@lang('message.dashboard.button.submit')</span>
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

$('#merchant_create').on('click',function(){
    $('.help-block').hide();
});

jQuery.extend(jQuery.validator.messages, {
    required: "{{__('This field is required.')}}",
    url: "{{__("Please enter a valid URL.")}}",
})

$('#merchant_add_form').validate({
	rules: {
		business_name: {
			required: true,
		},
		site_url: {
			required: true,
			url: true,
		},
		type: {
			required: true,
		},
		note: {
			required: true,
		},
		logo: {
            extension: "png|jpg|jpeg|bmp",
        },
	},
	messages: {
      logo: {
        extension: "{{__("Please select (png, jpg, jpeg or bmp) file!")}}"
      }
    },
	submitHandler: function(form)
    {
        $("#merchant_create").attr("disabled", true);
        $(".spinner").show();
        $("#merchant_create_text").text('Submitting...');
        form.submit();
    }
});

</script>
@endsection