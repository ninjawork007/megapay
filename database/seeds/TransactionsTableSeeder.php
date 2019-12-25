<?php

use Illuminate\Database\Seeder;

// Carbon::now()->toDateTimeString();

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('transactions')->truncate();

        DB::table('transactions')->insert([
            [
                'id'          => '1',
                'uuid'        => strtoupper(bin2hex(random_bytes(10))),
                'type'        => 'Transfer',
                'subtotal'    => '200',
                'fee'         => '20',
                'total'       => '220',
                'note'        => 'please check',
                'currency_id' => '1',
                'receiver_id' => '2',
                'sender_id'   => '1',
            ],
            [
                'id'          => '2',
                'uuid'        => strtoupper(bin2hex(random_bytes(10))),
                'type'        => 'Exchange',
                'subtotal'    => '300',
                'fee'         => '30',
                'total'       => '320',
                'note'        => 'sending to X',
                'currency_id' => '12',
                'receiver_id' => '2',
                'sender_id'   => '1',
            ],
            [
                'id'          => '3',
                'uuid'        => strtoupper(bin2hex(random_bytes(10))),
                'type'        => 'Withdrawal',
                'subtotal'    => '400',
                'fee'         => '40',
                'total'       => '450',
                'note'        => 'sending to Y',
                'currency_id' => '13',
                'receiver_id' => '2',
                'sender_id'   => '1',
            ],
            [
                'id'          => '4',
                'uuid'        => strtoupper(bin2hex(random_bytes(10))),
                'type'        => 'Deposit',
                'subtotal'    => '500',
                'fee'         => '20',
                'total'       => '520',
                'note'        => 'sending to Z',
                'currency_id' => '14',
                'receiver_id' => '2',
                'sender_id'   => '1',
            ],
            [
                'id'          => '5',
                'uuid'        => strtoupper(bin2hex(random_bytes(10))),
                'type'        => 'Transfer',
                'subtotal'    => '700',
                'fee'         => '20',
                'total'       => '720',
                'note'        => 'sending to A',
                'currency_id' => '15',
                'receiver_id' => '2',
                'sender_id'   => '1',
            ],
            [
                'id'          => '6',
                'uuid'        => strtoupper(bin2hex(random_bytes(10))),
                'type'        => 'Withdrawal',
                'subtotal'    => '900',
                'fee'         => '40',
                'total'       => '940',
                'note'        => 'sending to B',
                'currency_id' => '16',
                'receiver_id' => '2',
                'sender_id'   => '1',
            ],
        ]);
    }
}
