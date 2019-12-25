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
</style>
<body>
	<div style="width:900px; margin:auto; top:20px; position:relative;">
		<table style="margin-bottom:40px;">
			<tr>
				<td>
					@if (!empty($companyInfo['value']))
						<img src='{{ public_path("/images/logos/".$companyInfo["value"]) }}' alt="Logo" height="80" weight="80"/>
                    @else
                        <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="80" height="80">
                    @endif
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td>
					<table>
						<tr>
							<td style="font-size: 16px; color:#000000; line-height:25px; font-weight:bold;">Name</td>
						</tr>
						<tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">
								{{ $voucherDetails->user->first_name.' '.$voucherDetails->user->last_name }}
							</td>
						</tr>
						<br><br>

						<tr>
							<td style="font-size: 16px; color:#000000; line-height:25px; font-weight:bold;">Voucher Code</td>
						</tr>
						<tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ $voucherDetails->code }}</td>
						</tr>
						<br><br>
					</table>
				</td>
			</tr>

			<tr>
				<td>
					<table>
						<tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">Transaction ID</td>
						</tr>
						<tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{$voucherDetails->uuid}}</td>
						</tr>

						<br><br>

						<tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">Transaction Date</td>
						</tr>
						<tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ dateFormat($voucherDetails->created_at) }}</td>
						</tr>

						<br><br>

						<tr>
							<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">Status</td>
						</tr>
						<tr>
							<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ $voucherDetails->status }}</td>
						</tr>
					</table>
				</td>
			</tr>

			<tr>
				<td>
					{{-- <table style="margin-top:20px; width:300px;" class="code"> --}}
					<table style="margin-top:20px; width:300px;">
						<tr>
							<td colspan="2" style="font-size:16px; color:#000000; font-weight:bold;">Details</td>
						</tr>
						<tr>
							<td style="font-size:15px; color:#000000;">Amount</td>
							<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{$voucherDetails->currency->symbol}} {{ formatNumber($voucherDetails->amount) }}</td>
						</tr>
						<tr style="padding-bottom:10px;">
							<td style="font-size:15px; color:#000000;">Fee</td>
							<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{$voucherDetails->currency->symbol}} {{ formatNumber($vFee) }}</td>
						</tr>
						<tr>
							<td colspan="2" style="border-top:1px solid #eaeaea; padding-top:0; margin-bottom:3px;"></td>
						</tr>

						@php
							$total = $voucherDetails->amount + $vFee
						@endphp
						<tr>
							<td style="font-size:15px; color:#000000; font-weight:bold;">Total</td>
							<td style="font-size:15px; color:#4e5c6e; text-align:right; font-weight:bold;">{{ $voucherDetails->currency->symbol }} {{ "-".formatNumber($total) }}</td>
						</tr>
					</table>
				</td>
			</tr>
			<br><br>
		</table>
	</div>
</body>
</html>
