@php
$form_data = [
	'page_title'=> 'Payment Setting Form',
	'page_subtitle'=> 'Payment Setting Page',

	'tab_names' => ['paypal' => 'Paypal', 'stripe' => 'Stripe', 'twoCheckout' => '2Checkout','payUMoney' => 'PayUMoney','coinPayments'=>'CoinPayments'],

	'tab_forms' => [
		'paypal' => [
			'action' => URL::to('/').'/admin/settings/payment_methods',
			'form_class' => 'form-submit-jquery',
			'fields' => [
        ['type' => 'hidden', 'class' => '', 'label' => '', 'name' => 'gateway', 'value' => 'paypal'],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'PayPal Client ID', 'name' => 'client_id', 'value' => @$paypal['client_id']],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'PayPal Client Secret', 'name' => 'client_secret', 'value' => @$paypal['client_secret']],
            ['type' => 'select', 'options' => ['sandbox' => 'sandbox', 'live' => 'live'], 'class' => 'validate_field', 'label' => 'PayPal Mode', 'name' => 'mode', 'value' => @$paypal['mode']],
      ]
    ],
    'stripe' => [
      'action' => URL::to('/').'/admin/settings/payment_methods',
      'form_class' => 'form-submit-jquery',
      'fields' => [
        ['type' => 'hidden', 'class' => '', 'label' => '', 'name' => 'gateway', 'value' => 'stripe'],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'Stripe Secret Key', 'name' => 'secret_key', 'value' => @$stripe['secret']],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'Stripe Publishable Key', 'name' => 'publishable_key', 'value' => @$stripe['publishable']]
      ]
    ],
    'twoCheckout' => [
      'action' => URL::to('/').'/admin/settings/payment_methods',
      'form_class' => 'form-submit-jquery',
      'fields' => [
        ['type' => 'hidden', 'class' => '', 'label' => '', 'name' => 'gateway', 'value' => 'twoCheckout'],
            ['type' => 'text', 'class' => 'validate_field', 'label' => '2Checkout Seller ID', 'name' => 'seller_id', 'value' => @$twoCheckout['seller_id']],
            ['type' => 'select', 'options' => ['sandbox' => 'sandbox', 'live' => 'live'], 'class' => 'validate_field', 'label' => '2Checkout Mode', 'name' => 'mode', 'value' => @$twoCheckout['mode']],
      ]
    ],

    'payUMoney' => [
      'action' => URL::to('/').'/admin/settings/payment_methods',
      'form_class' => 'form-submit-jquery',
      'fields' => [
        ['type' => 'hidden', 'class' => '', 'label' => '', 'name' => 'gateway', 'value' => 'payUMoney'],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'PayUMoney Secret Key', 'name' => 'key', 'value' => @$payUmoney['key']],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'PayUMoney Salted Key', 'name' => 'salt', 'value' => @$payUmoney['salt']],
            ['type' => 'select', 'options' => ['sandbox' => 'sandbox', 'live' => 'live'], 'class' => 'validate_field', 'label' => 'PayUMoney Mode', 'name' => 'mode', 'value' => @$payUmoney['mode']],
      ]
    ],

    'coinPayments'=>
    [
        'action'=>url('admin/settings/payment_methods'),
        'form_class'=>'form-submit-jquery',
        'fields'=>[
            ['type' => 'hidden', 'class' => '', 'label' => '', 'name' => 'gateway', 'value' => 'coinPayments'],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'Merchant ID', 'name' => 'merchant_id', 'value' => @$coinPayments['merchant_id']],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'Private Key', 'name' => 'private_key', 'value' => @$coinPayments['private_key']],
            ['type' => 'text', 'class' => 'validate_field', 'label' => 'Public Key', 'name' => 'public_key', 'value' => @$coinPayments['public_key']],
        ]
    ]
  ]
];
@endphp

@include("admin.common.form.setting-multi-tab", $form_data)
