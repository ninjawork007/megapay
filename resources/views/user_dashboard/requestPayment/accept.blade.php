@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
            	<div class="col-md-7 col-xs-12 mb20 marginTopPlus">
            		@include('user_dashboard.layouts.common.alert')

            		<form method="POST" action="{{url('request_payment/accepted')}}" id="requestpayment_create_form" accept-charset='UTF-8'>

    					<input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
    					<input type="hidden" value="{{$requestPayment->id}}" name="id" id="id">
    					<input type="hidden" value="{{$requestPayment->currency_id}}" name="currency_id" id="currency_id" >
    					<input type="hidden" value="{{$requestPayment->currency->symbol}}" name="currencySymbol" id="currencySymbol" >
    					<input type="hidden" value="{{ $requestPayment->amount * (@$transfer_fee->charge_percentage/100) }}" name="percentage_fee" id="percentage_fee" >
    					<input type="hidden" value="{{ @$transfer_fee->charge_fixed }}" name="fixed_fee" id="fixed_fee">
    					<input type="hidden" name="fee" class="total_fees" value="0.00">

            			<div class="card">
            				<div class="card-header">
            					<div class="chart-list float-left">
            						<ul>
            							<li class="active">@lang('message.dashboard.send-request.request.accept.title')</li>
            						</ul>
            					</div>
            				</div>
            				<div class="wap-wed mt20 mb20">
            					<div class="form-group">
            						@if (!empty($requestPayment->user->email))
            							<label for="exampleInputEmail1">@lang('message.form.email')</label>
            						@elseif (!empty($requestPayment->phone))
            							<label for="exampleInputEmail1">@lang('message.registration.phone')</label>
            						@endif
            						<input type="text" name="emailOrPhone" id="emailOrPhone" class="form-control"
            						value="{{ ($requestPayment->phone) ? $requestPayment->phone : $requestPayment->user->email }}" readonly>
            						@if($errors->has('emailOrPhone'))
		        						<span class="error">
		        							{{ $errors->first('emailOrPhone') }}
		        						</span>
            						@endif
            					</div>
            					<div class="row">
            						<div class="col-md-6">
            							<div class="form-group">
            								<label for="exampleInputPassword1">@lang('message.dashboard.left-table.amount')</label>
            								<input type="text" class="form-control amount" name="amount" placeholder="0.00" type="text" id="amount" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" value="{{ $requestPayment->amount }}">
            								<span class="amountLimit" style="color: red;font-weight: bold"></span>
            								@if($errors->has('amount'))
            								<span class="error">
            									{{ $errors->first('amount') }}
            								</span>
            								@endif
            							</div>
            						</div>

            						<div class="col-md-6">
            							<div class="form-group">
            								<label>@lang('message.form.currency')</label>
            								<input class="form-control" name="currency" data-rel="{{$requestPayment->currency->id}}" id="currency" type="text" value="{{$requestPayment->currency->code}}" readonly>

            								<span class="currencyError" style="color: red;font-weight: bold"></span>
            								<small id="walletlHelp" class="form-text text-muted">
            									Fee(<span class="pFees">{{@$transfer_fee->charge_percentage}}</span>%+<span class="fFees">{{@$transfer_fee->charge_fixed}}</span>)
            									Total Fee : <span class="total_fees">{{($requestPayment->amount*(@$transfer_fee->charge_percentage/100))+(@$transfer_fee->charge_fixed)}}</span>
            								</small>
            							</div>
            						</div>
            					</div>
            					<div class="form-group">
            						<label for="exampleInputEmail1">@lang('message.dashboard.left-table.transferred.note')</label>
            						<textarea readonly class="form-control" rows="5" placeholder="Enter Note" name="note" id="note">{{$requestPayment->note}}</textarea>
            						@if($errors->has('note'))
            						<span class="error">
            							{{ $errors->first('note') }}
            						</span>
            						@endif
            					</div>
            				</div>
            				<div class="card-footer">
            					<button type="submit" class="btn btn-cust col-12" id="rp_money">
            						<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="rp_text">@lang('message.dashboard.send-request.request.accept.title')</span>
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

<script type="text/javascript">
	jQuery.extend(jQuery.validator.messages, {
        required: "{{__('This field is required.')}}",
    })

	$('#requestpayment_create_form').validate({
		rules: {
			amount: {
				required: true,
			},
		},
		submitHandler: function(form)
	    {
	        $("#rp_money").attr("disabled", true);
	        $(".spinner").show();
	        $("#rp_text").text('Sending Request...');
	        form.submit();
	    }
	});

	// Code for Amount Limit  check when window load
	$(window).on('load',function(e) {
		checkAmountLimitAndFeesLimit();
	});
	// Code for Amount Limit  check

	$(document).on('input','.amount',function(e) {
		checkAmountLimitAndFeesLimit();
	});

	function checkAmountLimitAndFeesLimit()
	{
		var token       = $("#token").val();
		var amount      = $('#amount').val();
		var currency_id = $('#currency').attr('data-rel');
		$.ajax({
			method: "POST",
			url: SITE_URL+"/request-payment/amount-limit",
			dataType: "json",
			data: {
				"_token":token,'amount':amount,
				'currency_id':currency_id,
				'transaction_type_id':{{Request_To}}
			}
		})
		.done(function(response)
		{
			console.log(response.success);
			if(response.success.status == 200)
			{
			    $("#percentage_fee").val(response.success.feesPercentage);
				$("#fixed_fee").val(response.success.feesFixed);
				$(".percentage_fees").html(response.success.feesPercentage);
				$(".fixed_fees").html(response.success.feesFixed);
				$(".total_fees").val(response.success.totalFees);
				$('.total_fees').html(response.success.totalFeesHtml);
				$('.pFees').html(response.success.pFeesHtml);
				$('.fFees').html(response.success.fFeesHtml);
		        $('#rp_money').removeAttr('disabled');
		        $('.amountLimit').text('');
			    return true;
			}
			else if (response.success.status == 404)
			{
				$('.amountLimit').text('');
				$('.currencyError').text("{{__("You do not have the requested currency !")}}");
		        $('#walletlHelp').hide();
				$('#rp_money').attr('disabled',true);
			}
			else
			{
				$('#walletlHelp').show();
				if(amount=='')
				{
					$('.amountLimit').text('');
					$('#rp_money').removeAttr('disabled');
				}
				else
				{
					$('.amountLimit').text(response.success.message);
					$('#rp_money').attr('disabled',true);
				}
			}
		});
	}
</script>

@endsection