<?php

use App\Models\RoleUser;
use Illuminate\Database\Seeder;

class RolesUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$data = [
            ['user_id' => 1, 'role_id' => '1', 'user_type' => 'Admin'],
        ];
        RoleUser::truncate();
        RoleUser::insert($data);
    }
}
