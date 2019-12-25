@php

// dd($transaction_type);
$form_data = [
    'page_title'=> 'Edit Fees',
    'page_subtitle'=> '',
    'form_name' => 'Edit Fees Form',
    'action' => URL::to('/').'/admin/settings/edit_fees/'.$result->id,
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
                      'value' => $result->transaction_type,
                    ],
                    [
                      'type' => 'select',
                      'options' => [ NULL => 'Select Payment Method'] + $payment_met,
                      'class' => 'validate_field',
                      'label' => 'Payment Methods',
                      'name' => 'payment_met[]',

                      'value' => (empty($result->payment_method)) ? NULL : $result->payment_method->name,
                    ],
                    [
                      'type' => 'text',
                      'class' => 'validate_field',
                      'label' => '(%) Charge',
                      'name' => 'charge_percentage',
                      'value' => $result->charge_percentage,
                    ],
                    [
                      'type' => 'text',
                      'class' => 'validate_field',
                      'label' => 'Fixed Charge',
                      'name' => 'charge_fixed',
                      'value' => $result->charge_fixed,
                    ],
            ]
  ];
@endphp

{{-- {{ ($result->transaction_type == $transaction_type) ? 'selected' : '' }} --}}

{{-- {{ ($pm_name == $payment_method) ? 'selected' : '' }} --}}

@include("admin.common.form.setting", $form_data)
