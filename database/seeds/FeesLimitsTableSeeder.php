<?php

use App\Models\FeesLimit;
use Illuminate\Database\Seeder;

class FeesLimitsTableSeeder extends Seeder
{

    public function run()
    {
        $data = [
            //deposits
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 1,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 2,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 3,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 4,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 5,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 6,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 7,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 1,
                'payment_method_id'   => 8,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],

            //Payouts
            [
                'currency_id'         => 1,
                'transaction_type_id' => 2,
                'payment_method_id'   => 1,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 2,
                'payment_method_id'   => 3,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 2,
                'payment_method_id'   => 6,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 3,
                'payment_method_id'   => null,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 5,
                'payment_method_id'   => null,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
            [
                'currency_id'         => 1,
                'transaction_type_id' => 10,
                'payment_method_id'   => null,
                'charge_percentage'   => 0.00000000,
                'charge_fixed'        => 0.00000000,
                'min_limit'           => 1.00000000,
                'max_limit'           => null,
                'has_transaction'     => 'Yes',
            ],
        ];
        FeesLimit::insert($data);
    }
}
