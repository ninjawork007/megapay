@php
$form_data = [
'page_title'=> 'Fees Setting Form',
'page_subtitle'=> 'Fees Setting Page',
'form_name' => 'Fees Setting Form',
'action' => URL::to('/').'/admin/settings/fees',
'fields' => [
				[
					'type' => 'select',
					'options' => ['withdrawl' => 'Withdrawl', 'deposit' => 'Deposit'],
					'class' => 'validate_field',
					'label' => 'Transaction Type',
					'name' => 'transaction_type ',
					'value' => @$result['transaction_type '],
					'hint' => 'Select Transaction Type'
				],
				[
					'type' => 'select',
					'options' => ['paypal' => 'Paypal', 'stripe' => 'Stripe', 'swift' => 'Swift', ],
					'class' => 'validate_field',
					'label' => 'Payment Methods',
					'name' => 'payment_method ',
					'value' => @$result['payment_method '],
					'hint' => 'Select Payment Method'
				],
				[
					'type' => 'text',
					'class' => 'validate_field',
					'label' => '(&#37;) Charge',
					'name' => 'charge_percentage',
					'value' => @$result['charge_percentage'],
					'hint' => ''
				],
				[
					'type' => 'text',
					'class' => 'validate_field',
					'label' => 'Fixed Charge',
					'name' => 'charge_fixed',
					'value' => @$result['charge_fixed'],
					'hint' => ''
				],
			]
	];
@endphp
@include("admin.common.form.setting", $form_data)