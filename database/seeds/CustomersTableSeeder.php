<?php

// use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('customers')->truncate();
        DB::table('customers')->insert([
            [
                'username'   => 'messi',
                'first_name' => 'lionel',
                'last_name'  => 'messi',
                'phone'      => '09911111111',
                'email'      => 'customer@techvill.net',
                'password'   => Hash::make('123456'),
                'phrase'     => '1st customer',
                'status'     => true,
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
