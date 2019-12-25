<?php

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::truncate();

        PaymentMethod::insert([
            ['id' => 1, 'name' => 'Mts', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Stripe', 'status' => 'Active'],
            ['id' => 3, 'name' => 'Paypal', 'status' => 'Active'],
            ['id' => 4, 'name' => '2Checkout', 'status' => 'Active'],
            ['id' => 5, 'name' => 'PayUmoney', 'status' => 'Active'],
            ['id' => 6, 'name' => 'Bank', 'status' => 'Active'],
            ['id' => 7, 'name' => 'Coinpayments', 'status' => 'Active'],
            ['id' => 8, 'name' => 'Payeer', 'status' => 'Active'],
        ]);
    }
}
