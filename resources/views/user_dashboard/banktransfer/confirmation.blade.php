@extends('user_dashboard.layouts.app')
@section('content')
	<section class="section-06 history padding-30">
	        <div class="container">
	            <div class="row">
					<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
		                <div class="card">
		                    <div class="card-header">
							   <h4>@lang('message.dashboard.send-request.send-to-bank.title')</h4>
		                    </div>

		                    <form action="{{ url('bank-transfer/success') }}" style="display: block;" method="POST" accept-charset="UTF-8" id="bank_transfer_form" enctype="multipart/form-data">
		                        <input value="{{csrf_token()}}" name="_token" id="token" type="hidden">

		                        <div class="wap-wed mt20 mb20">
	                                <div class="form-group">
	                                    <label for="account_name" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.bank-account-holder-name')</strong></label>
	                                    <input type="text" name="account_name" class="form-control">
	                                </div>
	                                <div class="form-group">
	                                    <label for="account_number" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.account-number')</strong></label>
	                                    <input type="text" name="account_number" class="form-control">
	                                </div>

	                                <div class="form-group">
	                                    <label for="bank_branch_name" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.branch-name')</strong></label>
	                                    <input type="text" name="bank_branch_name" class="form-control">
	                                </div>

	                                <div class="form-group">
	                                    <label for="bank_branch_city" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.branch-city')</strong></label>
	                                    <input type="text" name="bank_branch_city" class="form-control">
	                                </div>

	                                <div class="form-group">
	                                    <label for="swift_code" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.swift-code')</strong></label>
	                                    <input type="text" name="swift_code" class="form-control">
	                                </div>

	                                <div class="form-group">
	                                    <label for="bank_branch_address" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.branch-address')</strong></label>
	                                    <input type="text" name="bank_branch_address" class="form-control">
	                                </div>

									<div class="form-group">
	                                    <label for="bank_name" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.bank-name')</strong></label>
	                                    <input type="text" name="bank_name" class="form-control">
	                                </div>

	                                <div class="form-group">
	                                    <label>@lang('message.dashboard.payout.payout-setting.modal.country')</label>
	                                    <select name="country" id="country" class="form-control">
	                                        @foreach($countries as $country)
	                                            <option value="{{$country->id}}">{{$country->name}}</option>
	                                        @endforeach
	                                    </select>
	                                </div>

	                                <div class="form-group">
	                                    <label for="attached_file" class="h6"><strong>@lang('message.dashboard.payout.payout-setting.modal.attached-file')</strong></label>
	                                    <input type="file" name="attached_file" class="form-control input-file-field" data-rel="">
	                                </div>
		                        </div>

		                        <div class="wap-wed mt20 mb20">
								    <div class="h5"><strong>@lang('message.dashboard.confirmation.details')</strong></div>
								    <div class="row mt20">
									  <div class="col-md-6">@lang('message.dashboard.send-request.send.confirmation.transfer-amount')</div>
									  <div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['amount'])) }}</strong></div>
									</div>
			                        <div class="row mt10">
									  <div class="col-md-6">@lang('message.dashboard.confirmation.fee')</div>
									  <div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['fee'])) }}</strong></div>
									</div>
									<hr />
									<div class="row">
									  <div class="col-md-6 h6"><strong>@lang('message.dashboard.confirmation.total')</strong></div>
									  <div class="col-md-6 text-right"><strong>{{  moneyFormat($transInfo['currSymbol'], formatNumber($transInfo['totalAmount'])) }}</strong></div>
									</div>
								</div>

			                    <div class="card-footer">
								    <div class="text-center">
										<a onclick="window.history.back();" href="#" class="btn btn-cust float-left">
										 <strong><i class="fa fa-angle-left"></i>&nbsp;&nbsp;@lang('message.dashboard.button.back')</strong>
										</a>

				                  		<div style="float: right;">
											<button type="submit" class="btn btn-cust" id="send_money">
					                  			<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="send_text">@lang('message.dashboard.button.confirm')&nbsp; <i class="fa fa-angle-right"></i></span>
					                  		</button>
			                            </div>
									</div>
			                    </div>

							</form>

		                </div>
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
    number: "{{__("Please enter a valid number.")}}",
    minlength: $.validator.format( "{{__("Please enter at least")}}"+" {0} "+"{{__("characters.")}}" ),
})


$('#bank_transfer_form').validate({
    rules: {
        account_name: {
            required: true,
        },
        account_number: {
            required: true,
            number: true,
            minlength: 20,
        },
        bank_branch_name: {
            required: true,
        },
        bank_branch_city: {
            required: true,
        },
        swift_code: {
            required: true,
            number: true,
        },
        bank_branch_address: {
            required: true,
        },
        bank_name: {
            required: true,
        },
        attached_file: {
            // required: true,
            extension: "docx|rtf|doc|pdf|png|jpg|jpeg|gif|bmp",
        },
    },
    messages: {
      attached_file: {
        extension: "Please select (docx, rtf, doc, pdf, png, jpg, jpeg, gif or bmp) file!"
      },
    },
    submitHandler: function(form)
    {
        $("#send_money").attr("disabled", true);
        $(".spinner").show();
        var pretext=$("#send_text").text();
        $("#send_text").text('Sending...');
        form.submit();
        setTimeout(function(){
            $("#send_money").removeAttr("disabled");
            $(".spinner").hide();
            $("#send_text").text(pretext);
        },10000);
    }
});
</script>

@endsection
