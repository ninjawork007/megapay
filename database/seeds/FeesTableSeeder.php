<?php

use App\Models\Fee;
use Illuminate\Database\Seeder;

class FeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Fee::truncate();
        $data = [
            [
                'transaction_type' => 'deposit', 'charge_percentage' => 1.00, 'charge_fixed' => 200.00, 'payment_method_id' => 1,
            ],
            [
                'transaction_type' => 'exchange', 'charge_percentage' => 3.00, 'charge_fixed' => 250.00, 'payment_method_id' => 2,
            ], [
                'transaction_type' => 'transfer', 'charge_percentage' => 2.00, 'charge_fixed' => 150.00, 'payment_method_id' => 3,
            ], [
                'transaction_type' => 'withdrawl', 'charge_percentage' => 4.00, 'charge_fixed' => 300.00, 'payment_method_id' => 1,
            ],
        ];
        Fee::insert($data);
    }
}
