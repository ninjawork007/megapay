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
						<img src='{{ public_path("/images/logos/".$companyInfo["value"]) }}' alt="Logo"/>
                    @else
                        <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="120" height="80">
                    @endif
				</td>
			</tr>
		</table>

		@if ($transactionDetails->transaction_type_id == Voucher_Created)
			<table>
				<tr>
					<td>
						<table>
							<tr>
								<td style="font-size: 16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.voucher-created.voucher-code')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ $transactionDetails->voucher->code }}</td>
							</tr>
							<br><br>
						</table>
					</td>
				</tr>

				<tr>
					<td>
						<table>
							<tr>
								<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-id')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{$transactionDetails->uuid}}</td>
							</tr>

							<br><br>

							<tr>
								<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-date')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ dateFormat($transactionDetails->created_at) }}</td>
							</tr>

							<br><br>

							<tr>
								<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.form.status')</td>
							</tr>
							<tr>
							</tr>
						</table>
					</td>
				</tr>

				<tr>
					<td>
						{{-- <table style="margin-top:20px; width:300px;" class="code"> --}}
						<table style="margin-top:20px; width:300px;">
							<tr>
								<td colspan="2" style="font-size:16px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.details')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#000000;">@lang('message.dashboard.left-table.voucher-created.voucher-amount')</td>
								<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{ moneyFormat($transactionDetails->currency->symbol, formatNumber($transactionDetails->subtotal)) }}</td>


							</tr>
							<tr style="padding-bottom:10px;">
								<td style="font-size:15px; color:#000000;">@lang('message.dashboard.left-table.fee')</td>
								<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{ moneyFormat($transactionDetails->currency->symbol, formatNumber($transactionDetails->charge_percentage + $transactionDetails->charge_fixed)) }}</td>
							</tr>
							<tr>
								<td colspan="2" style="border-top:1px solid #eaeaea; padding-top:0; margin-bottom:3px;"></td>
							</tr>

							@php
								$total = $transactionDetails->subtotal + $transactionDetails->charge_percentage + $transactionDetails->charge_fixed
							@endphp
							<tr>
								<td style="font-size:15px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.total')</td>
								<td style="font-size:15px; color:#4e5c6e; text-align:right; font-weight:bold;">{{ moneyFormat($transactionDetails->currency->symbol, formatNumber($total)) }}</td>
							</tr>
						</table>
					</td>
				</tr>
				<br><br>
			</table>
		@else
			<table>
				<tr>
					<td>
						<table>
							<tr>
								<td style="font-size: 16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.voucher-created.voucher-code')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ $transactionDetails->voucher->code }}</td>
							</tr>
							<br><br>
						</table>
					</td>
				</tr>

				<tr>
					<td>
						<table>
							<tr>
								<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-id')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{$transactionDetails->uuid}}</td>
							</tr>

							<br><br>

							<tr>
								<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.dashboard.left-table.transaction-date')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ dateFormat($transactionDetails->created_at) }}</td>
							</tr>

							<br><br>

							<tr>
								<td style="font-size:16px; color:#000000; line-height:25px; font-weight:bold;">@lang('message.form.status')</td>
							</tr>
							<tr>
								<td style="font-size:15px; color:#4e5c6e; line-height:22px;">{{ __($transactionDetails->status) }}</td>
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
									<td style="font-size:15px; color:#000000;">@lang('message.dashboard.left-table.voucher-created.voucher-amount')</td>
									<td style="font-size:15px; color:#4e5c6e; text-align:right;">{{ moneyFormat($transactionDetails->currency->symbol, formatNumber($transactionDetails->subtotal)) }}</td>
								</tr>
							<tr>
								<td colspan="2" style="border-top:1px solid #eaeaea; padding-top:0; margin-bottom:3px;"></td>
							</tr>
								<tr>
									<td style="font-size:15px; color:#000000; font-weight:bold;">@lang('message.dashboard.left-table.total')</td>
									<td style="font-size:15px; color:#4e5c6e; text-align:right; font-weight:bold;">{{ moneyFormat($transactionDetails->currency->symbol, formatNumber($transactionDetails->subtotal)) }}</td>
								</tr>
						</table>
					</td>
				</tr>
				<br><br>
			</table>
		@endif

	</div>
</body>
</html>
