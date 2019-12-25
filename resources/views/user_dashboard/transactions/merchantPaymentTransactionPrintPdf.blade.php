<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Print</title>
</head>
    <style>
	   body{ font-family: 'Lato', sans-serif; color:#121212;}

	   hr { border-top:1px solid #f0f0f0;}
	   table { border-collapse:collapse;}
	   .code td{ padding:5px;}

		/*logo -- css*/
		.setting-img{
		overflow: hidden;
		max-width: 100%;
		}
		.img-wrap-general-logo {
		/*width: 300px;*/
		overflow: hidden;
		margin: 5px;
		background: rgba(74, 111, 197, 0.9) !important;
		/*height: 100px;*/
		max-width: 100%;
		}
		.img-wrap-general-logo > img {
		max-width: 100%;
		height: auto !important;
		max-height: 100%;
		width: auto !important;
		object-fit: contain;
		}
		/*logo -- css*/
	</style>
	<body>
	    <div style="width:900px; margin:auto; top:20px; position:relative;">
			<table style="margin-bottom:40px;">
				<tr>
				 <td>
				 	 @if(!empty($companyInfo['value']))
				 	 	<div class="setting-img">
			                <div class="img-wrap-general-logo">
								<img src='{{ public_path("/images/logos/".$companyInfo["value"]) }}' alt="Logo"/>
			                </div>
			            </div>
				     @else
                        <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="120" height="80">
                    @endif
				 </td>
				</tr>
			</table>

			@if ($transaction->transaction_type_id == Payment_Sent)
				<table>
					<tr>
					  <td>
					   <table style="margin-top:20px;">
						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.merchant.payment.merchant')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ $transaction->merchant->business_name }}</td>
						  </tr>
						  <br><br>

						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-id')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{$transaction->uuid}}</td>
						  </tr>
						  <br><br>

						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-date')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ dateFormat($transaction->created_at) }}</td>
						  </tr>
						  <br><br>

						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.form.status')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ __($transaction->status) }}</td>
						  </tr>
					   </table>
					  </td>
					  </tr>

					<tr>
					  <td>
					   <table style="margin-top:20px; width:300px;">
						  <tr>
							<td colspan="2" style="font-size:16px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.details')</td>
						  </tr>
							  <tr>
								<td style="font-size:15px; color:#000000;">@lang('message.dashboard.left-table.payment-Sent.payment-amount')</td>
								<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{ moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) }}</td>
							  </tr>
						  <tr>
						    <td colspan="2" style="border-top:1px solid #eaeaea; padding-top:0; margin-bottom:3px;"></td>
						  </tr>
							  <tr>
								<td style="font-size:15px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.total')</td>
								<td style="font-size:15px; color:#4e5c6e; text-align:right; font-weight:bold;">{{ moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) }}</td>
							  </tr>
						  </table>
					    </td>
					  </tr>
				</table>
			@else
				<table>
					<tr>
					  <td>
					   <table style="margin-top:20px;">
						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-id')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{$transaction->uuid}}</td>
						  </tr>
						  <br><br>

						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-date')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ dateFormat($transaction->created_at) }}</td>
						  </tr>
						  <br><br>

						  <tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.form.status')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ __($transaction->status) }}</td>
						  </tr>
					   </table>
					  </td>
					  </tr>

					<tr>
					  <td>
					   <table style="margin-top:20px; width:300px;">
						  <tr>
							<td colspan="2" style="font-size:16px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.details')</td>
						  </tr>
						  <tr>
							<td style="font-size:15px; color:#000000;">@lang('message.dashboard.left-table.payment-Sent.payment-amount')</td>
							<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{ moneyFormat($transaction->currency->symbol, formatNumber($transaction->subtotal)) }}</td>
						  </tr>
						   <tr style="padding-bottom:10px;">
								<td style="font-size:15px; color:#000000;">@lang('message.dashboard.left-table.fee')</td>

								<td style="font-size:15px; color:#4e5c6e; text-align:right;">
									{{ moneyFormat($transaction->currency->symbol, formatNumber($transaction->charge_percentage+$transaction->charge_fixed)) }}
								</td>
							</tr>
						  <tr>
						    <td colspan="2" style="border-top:1px solid #eaeaea; padding-top:0; margin-bottom:3px;"></td>
						  </tr>
							  <tr>
								<td style="font-size:15px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.total')</td>
								<td style="font-size:15px; color:#4e5c6e; text-align:right; font-weight:bold;">{{ moneyFormat($transaction->currency->symbol, formatNumber($transaction->total)) }}</td>
							  </tr>
						  </table>
					    </td>
					  </tr>
				</table>
			@endif

	    </div>
	</body>
</html>
