<?php

// use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id'         => '1',
                'first_name' => 'lionel',
                'last_name'  => 'messi',
                'phone'      => '01848363013',
                'email'      => 'customer@techvill.net',
                'password'   => Hash::make('123456'),
                'status'     => true,
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ], [
                'id'         => '2',
                'first_name' => 'cristiano',
                'last_name'  => 'ronaldo',
                'phone'      => '01748363013',
                'email'      => 'cr7@techvill.net',
                'password'   => Hash::make('123456'),
                'status'     => true,
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ], [
                'id'         => '3',
                'first_name' => 'andres',
                'last_name'  => 'iniesta',
                'phone'      => '01648363013',
                'email'      => 'andres@techvill.net',
                'password'   => Hash::make('123456'),
                'status'     => true,
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
