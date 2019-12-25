<form action="https://perfectmoney.is/api/step1.asp" id="perfectmoney" method="POST">
		<input type="hidden" name="PAYEE_ACCOUNT" value="{{ $payee_account }}">
		<input type="hidden" name="PAYEE_NAME" value="{{ $payee_name }}">
		<input type="hidden" name="PAYMENT_ID" value=""><BR>
		<input type="hidden" name="PAYMENT_AMOUNT" value="{{ $payment_amount }}"><BR>
		<input type="hidden" name="PAYMENT_UNITS" value="{{ $payment_units }}">
		<input type="hidden" name="STATUS_URL" value="{{ url('deposit/ipn/perfect_money') }}">
		<input type="hidden" name="PAYMENT_URL" value="{{ url('deposit/perfect_money_success') }}">
		<input type="hidden" name="PAYMENT_URL_METHOD" value="GET">
		<input type="hidden" name="NOPAYMENT_URL" value="{{ url('deposit/perfect_money_fail') }}">
		<input type="hidden" name="NOPAYMENT_URL_METHOD" value="LINK">
		<input type="hidden" name="SUGGESTED_MEMO" value="">
		<input type="hidden" name="amountwithoutfees" value="{{ $amount }}">
		<input type="hidden" name="methodid" value="{{ $method_id }}">
        <input type="hidden" name="currencyid" value="{{ $currency_id }}">
        <input type="hidden" name="userid" value="{{ $user_id }}">
        <input type="hidden" name="BAGGAGE_FIELDS" value="methodid currencyid userid amountwithoutfees">
		<input type="submit" name="PAYMENT_METHOD" value="Click here if you are not redirected automatically">
</form>
<script type="text/javascript">document.getElementById('perfectmoney').submit();</script>