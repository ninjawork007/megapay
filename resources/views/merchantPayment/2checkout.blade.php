
<?php
  if($mode == 'sandbox'){
    $url = "https://sandbox.2checkout.com/checkout/spurchase";
  }else{
    $url = "https://2checkout.com/checkout/spurchase";
  }
  $sid = $seller_id;
?>

<form id="2checkout" action="{{$url}}" method="post">
  <input type="hidden" name="sid" value="{{$sid}}"/>
  <input type="hidden" name="mode" value="2CO"/>
  <input type="hidden" name="li_0_name" value="{{$item_name}}"/>
  <input type="hidden" name="li_0_price" value="{{$amount}}"/>
  <input type="hidden" name="currency_code" value="{{$currency}}"/>
  <input type="hidden" name="x_receipt_link_url" value="{{url('payment/twocheckout_payment_store')}}"/>
  <input type="submit" value="Click here if you are not redirected automatically" />
</form>

<script type="text/javascript">document.getElementById('2checkout').submit();</script>