<!DOCTYPE html>
@php
	$productinfo = 'Merchant Payment';
	$surl = url('/payment/payumoney_success');
	$furl = url('/payment/payumoney_fail');
	$service_provider = 'payu_paisa';
	$hashSequence = "$key|$txnid|$amount|$productinfo|$firstname|$email|||||||||||$salt";
	$hash = hash("sha512", $hashSequence);
	if($mode == 'sandbox'){
	  $action = "https://test.payu.in/_payment";
	}else{
	   $action = "https://secure.payu.in/_payment";
	}
@endphp
<html>
    <head>
    </head>
    <body>
        <form action="{{$action}}" id="payuform" method="POST" name="payuform">
            <input name="key" type="hidden" value="{{$key}}"/>
            <input name="hash" type="hidden" value="{{$hash}}"/>
            <input name="txnid" type="hidden" value="{{$txnid}}"/>
            <input name="amount" type="hidden" value="{{$amount}}"/>
            <input id="email" name="email" type="hidden" value="{{$email}}"/>
            <input id="firstname" name="firstname" type="hidden" value="{{$firstname}}"/>
            <input name="productinfo" type="hidden" value="{{$productinfo}}"/>
            <input name="surl" size="64" type="hidden" value="{{$surl}}"/>
            <input name="furl" size="64" type="hidden" value="{{$furl}}"/>
            <input name="service_provider" type="hidden" value="{{$service_provider}}"/>
            <input type="submit" value="Click here if you are not redirected automatically"/>
        </form>
        <script type="text/javascript">
            document.getElementById('payuform').submit();
        </script>
    </body>
</html>