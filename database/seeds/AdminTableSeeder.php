<?php

// use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdminTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('admins')->insert([
            [
                'id'         => '1',
                'username'   => 'admin',
                'first_name' => 'admin',
                'last_name'  => 'techvill',
                'email'      => 'admin@techvill.net',
                'password'   => Hash::make('123456'),
                'status'     => true,
                'role_id'    => '1',
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
