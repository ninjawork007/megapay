<?php

use Illuminate\Database\Seeder;

class TransactionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = [
            ['id' => 1, 'name' => 'Deposit'],
            ['id' => 2, 'name' => 'Withdrawal'],
            ['id' => 3, 'name' => 'Transferred'],
            ['id' => 4, 'name' => 'Received'],
            ['id' => 5, 'name' => 'Exchange_From'],
            ['id' => 6, 'name' => 'Exchange_To'],
            ['id' => 7, 'name' => 'Voucher_Created'],
            ['id' => 8, 'name' => 'Voucher_Activated'],
            ['id' => 9, 'name' => 'Request_From'],
            ['id' => 10, 'name' => 'Request_To'],
            ['id' => 11, 'name' => 'Payment_Sent'],
            ['id' => 12, 'name' => 'Payment_Received'],
        ];
        \DB::table('transaction_types')->insert($type);
    }
}
