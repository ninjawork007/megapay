<?php
  if($mode == 'sandbox')
  {
    $actionUrl = "https://sandbox.2checkout.com/checkout/purchase";
  }
  else
  {
    $actionUrl = "https://2checkout.com/checkout/purchase";
  }
?>
<form action="{{ $actionUrl }}" id="2checkout" method="post">
    <input name="sid" type="hidden" value="{{$seller_id}}"/>
    <input name="mode" type="hidden" value="2CO"/>
    <input name="li_0_name" type="hidden" value="Test Product"/>
    <input name="li_0_price" type="hidden" value="{{$amount}}"/>
    <input name="currency_code" type="hidden" value="{{$currency->code}}"/>

    <input name="x_receipt_link_url" type="hidden" value="{{url('deposit/checkout/payment/success')}}"/>

    <input type="submit" value="Click here if you are not redirected automatically"/>
</form>
<script type="text/javascript">
    document.getElementById('2checkout').submit();
</script>
