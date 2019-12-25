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
								<h4 class="float-left">Request Payment</h4>
							</div>
						</div>
					</div>
					<div class="card-body">
						<form action="{{url('request_payment/update')}}"  method="POST" accept-charset="utf-8" id="requestPayment_edit_form">
							<input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
							<input type="hidden" value="{{$requestPayment->id}}" name="id" id="id">

							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label>Amount</label>
										<input class="form-control" name="amount" id="amount" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" placeholder="0.00" type="text" value="{{$requestPayment->amount}}">
										@if($errors->has('amount'))
										<span class="error">
											{{ $errors->first('amount') }}
										</span>
										@endif
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label>Currency</label>
										<select class="form-control" name="currency_id">
											@foreach($currencies as $currency)
												<option value="{{$currency->id}}" {{ ($currency->id == $requestPayment->currency_id) ? 'selected' : '' }}>{{$currency->code}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label>Invoice No</label>
										<input class="form-control" name="invoice_no" id="invoice_no"  placeholder="INV-123" type="text" value="{{$requestPayment->invoice_no}}">
										@if($errors->has('invoice_no'))
										<span class="error">
											{{ $errors->first('invoice_no') }}
										</span>
										@endif
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label>Email</label>
										<input class="form-control" name="email" id="email" type="email" value="{{$requestPayment->email}}" readonly>
										@if($errors->has('email'))
										<span class="error">
											{{ $errors->first('email') }}
										</span>
										@endif
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<label>Purpose of payment</label>
										<input class="form-control" name="purpose" id="purpose" type="text" value="{{$requestPayment->purpose}}">
										@if($errors->has('purpose'))
										<span class="error">
											{{ $errors->first('purpose') }}
										</span>
										@endif
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label>Note</label>
										<textarea name="note" class="form-control" id="note">{{$requestPayment->note}}</textarea>
										@if($errors->has('note'))
										<span class="error">
											{{ $errors->first('note') }}
										</span>
										@endif
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="pull-right">
										<br>
										<button type="submit" class="btn btn-cust" id="rp_update">
				                  			<i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="rp_update_text">Update</span>
				                  		</button>
									</div>
								</div>
							</div>
						</form>

					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection

@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>
<script>
	$('#requestPayment_edit_form').validate({
	    rules: {
	        amount: {
	            required: true,
	        },
	        purpose: {
	            required: true,
	        },
	        note: {
	            required: true,
	        },
	    },
	    submitHandler: function(form)
	    {
	        $("#rp_update").attr("disabled", true);
	        $(".spinner").show();
	        $("#rp_update_text").text('Updating...');
	        form.submit();
	    }
	});
</script>
@endsection