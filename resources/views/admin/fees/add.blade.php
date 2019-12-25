@php
$form_data = [
    'page_title'=> 'Add Fees',
    'page_subtitle'=> '',
    'form_name' => 'Add Fees Form',
    'action' => URL::to('/').'/admin/settings/add_fees',
    'fields' => [
                    [
                      'type' => 'select',
                      'options' => [
                         NULL => 'Select Transaction Type',
                        'Deposit' => 'Deposit',
                        'Withdrawal' => 'Withdrawal',
                        'Transfer' => 'Transfer',
                        'Voucher' => 'Voucher',
                        'Merchant' => 'Merchant',
                      ],
                      'class' => 'validate_field',
                      'label' => 'Transaction Type',
                      'name' => 'transaction_type',
                      'value' => old('transaction_type'),
                    ],
                    [
                      'type' => 'select',
                      'options' => [ NULL => 'Select Payment Method'] + $payment_met,
                      'class' => 'validate_field',
                      'label' => 'Payment Methods',
                      'name' => 'payment_met[]',
                      'value' => '',
                    ],
                    [
                      'type' => 'text',
                      'class' => 'validate_field',
                      'label' => '(%) Charge',
                      'name' => 'charge_percentage',
                      'value' => old('charge_percentage'),
                    ],
                    [
                      'type' => 'text',
                      'class' => 'validate_field',
                      'label' => 'Fixed Charge',
                      'name' => 'charge_fixed',
                      'value' => old('charge_fixed'),
                    ],
                ],
            ];
@endphp
@include("admin.common.form.setting", $form_data)
