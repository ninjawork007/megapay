<?php

use App\Models\PermissionRole;
use Illuminate\Database\Seeder;

class PermissionsRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        $data = [
            //admin
            ['permission_id' => 1, 'role_id' => '1'],
            ['permission_id' => 2, 'role_id' => '1'],
            ['permission_id' => 3, 'role_id' => '1'],
            ['permission_id' => 4, 'role_id' => '1'],
            ['permission_id' => 5, 'role_id' => '1'],
            ['permission_id' => 6, 'role_id' => '1'],
            ['permission_id' => 7, 'role_id' => '1'],
            ['permission_id' => 8, 'role_id' => '1'],
            ['permission_id' => 9, 'role_id' => '1'],
            ['permission_id' => 10, 'role_id' => '1'],
            ['permission_id' => 11, 'role_id' => '1'],
            ['permission_id' => 12, 'role_id' => '1'],
            ['permission_id' => 13, 'role_id' => '1'],
            ['permission_id' => 14, 'role_id' => '1'],
            ['permission_id' => 15, 'role_id' => '1'],
            ['permission_id' => 16, 'role_id' => '1'],
            ['permission_id' => 17, 'role_id' => '1'],
            ['permission_id' => 18, 'role_id' => '1'],
            ['permission_id' => 19, 'role_id' => '1'],
            ['permission_id' => 20, 'role_id' => '1'],
            ['permission_id' => 21, 'role_id' => '1'],
            ['permission_id' => 22, 'role_id' => '1'],
            ['permission_id' => 23, 'role_id' => '1'],
            ['permission_id' => 24, 'role_id' => '1'],
            // ['permission_id' => 25, 'role_id' => '1'],
            // ['permission_id' => 26, 'role_id' => '1'],
            // ['permission_id' => 27, 'role_id' => '1'],
            // ['permission_id' => 28, 'role_id' => '1'],
            ['permission_id' => 29, 'role_id' => '1'],
            ['permission_id' => 30, 'role_id' => '1'],
            ['permission_id' => 31, 'role_id' => '1'],
            ['permission_id' => 32, 'role_id' => '1'],
            ['permission_id' => 33, 'role_id' => '1'],
            ['permission_id' => 34, 'role_id' => '1'],
            ['permission_id' => 35, 'role_id' => '1'],
            ['permission_id' => 36, 'role_id' => '1'],
            ['permission_id' => 37, 'role_id' => '1'],
            ['permission_id' => 38, 'role_id' => '1'],
            ['permission_id' => 39, 'role_id' => '1'],
            ['permission_id' => 40, 'role_id' => '1'],
            ['permission_id' => 41, 'role_id' => '1'],
            ['permission_id' => 42, 'role_id' => '1'],
            ['permission_id' => 43, 'role_id' => '1'],
            ['permission_id' => 44, 'role_id' => '1'],
            ['permission_id' => 45, 'role_id' => '1'],
            ['permission_id' => 46, 'role_id' => '1'],
            ['permission_id' => 47, 'role_id' => '1'],
            ['permission_id' => 48, 'role_id' => '1'],
            ['permission_id' => 49, 'role_id' => '1'],
            ['permission_id' => 50, 'role_id' => '1'],
            ['permission_id' => 51, 'role_id' => '1'],
            ['permission_id' => 52, 'role_id' => '1'],
            ['permission_id' => 53, 'role_id' => '1'],
            ['permission_id' => 54, 'role_id' => '1'],
            ['permission_id' => 55, 'role_id' => '1'],
            ['permission_id' => 56, 'role_id' => '1'],
            ['permission_id' => 57, 'role_id' => '1'],
            ['permission_id' => 58, 'role_id' => '1'],
            ['permission_id' => 59, 'role_id' => '1'],
            ['permission_id' => 60, 'role_id' => '1'],
            ['permission_id' => 61, 'role_id' => '1'],
            ['permission_id' => 62, 'role_id' => '1'],
            ['permission_id' => 63, 'role_id' => '1'],
            ['permission_id' => 64, 'role_id' => '1'],
            ['permission_id' => 65, 'role_id' => '1'],
            ['permission_id' => 66, 'role_id' => '1'],
            ['permission_id' => 67, 'role_id' => '1'],
            ['permission_id' => 68, 'role_id' => '1'],
            ['permission_id' => 69, 'role_id' => '1'],
            ['permission_id' => 70, 'role_id' => '1'],
            ['permission_id' => 71, 'role_id' => '1'],
            ['permission_id' => 72, 'role_id' => '1'],
            ['permission_id' => 73, 'role_id' => '1'],
            ['permission_id' => 74, 'role_id' => '1'],
            ['permission_id' => 75, 'role_id' => '1'],
            ['permission_id' => 76, 'role_id' => '1'],
            ['permission_id' => 77, 'role_id' => '1'],
            ['permission_id' => 78, 'role_id' => '1'],
            ['permission_id' => 79, 'role_id' => '1'],
            ['permission_id' => 80, 'role_id' => '1'],
            ['permission_id' => 85, 'role_id' => '1'],
            ['permission_id' => 86, 'role_id' => '1'],
            ['permission_id' => 87, 'role_id' => '1'],
            ['permission_id' => 88, 'role_id' => '1'],
            ['permission_id' => 89, 'role_id' => '1'],
            ['permission_id' => 90, 'role_id' => '1'],
            ['permission_id' => 91, 'role_id' => '1'],
            ['permission_id' => 92, 'role_id' => '1'],
            ['permission_id' => 93, 'role_id' => '1'],
            ['permission_id' => 94, 'role_id' => '1'],
            ['permission_id' => 95, 'role_id' => '1'],
            ['permission_id' => 96, 'role_id' => '1'],
            ['permission_id' => 97, 'role_id' => '1'],
            ['permission_id' => 98, 'role_id' => '1'],
            ['permission_id' => 99, 'role_id' => '1'],
            ['permission_id' => 100, 'role_id' => '1'],
            ['permission_id' => 101, 'role_id' => '1'],
            ['permission_id' => 102, 'role_id' => '1'],
            ['permission_id' => 103, 'role_id' => '1'],
            ['permission_id' => 104, 'role_id' => '1'],
            ['permission_id' => 105, 'role_id' => '1'],
            ['permission_id' => 106, 'role_id' => '1'],
            ['permission_id' => 107, 'role_id' => '1'],
            ['permission_id' => 108, 'role_id' => '1'],
            //end

            //user
            ['permission_id' => 109, 'role_id' => '2'],
            ['permission_id' => 110, 'role_id' => '2'],
            ['permission_id' => 111, 'role_id' => '2'],
            ['permission_id' => 112, 'role_id' => '2'],
            ['permission_id' => 113, 'role_id' => '2'],
            // ['permission_id' => 114, 'role_id' => '2'],
            ['permission_id' => 115, 'role_id' => '2'],
            //end

            //admin
            ['permission_id' => 118, 'role_id' => '1'],
            ['permission_id' => 119, 'role_id' => '1'],
            ['permission_id' => 120, 'role_id' => '1'],
            ['permission_id' => 121, 'role_id' => '1'],
            ['permission_id' => 122, 'role_id' => '1'],
            ['permission_id' => 123, 'role_id' => '1'],
            ['permission_id' => 124, 'role_id' => '1'],
            ['permission_id' => 125, 'role_id' => '1'],
            ['permission_id' => 126, 'role_id' => '1'],
            ['permission_id' => 127, 'role_id' => '1'],
            ['permission_id' => 128, 'role_id' => '1'],
            ['permission_id' => 129, 'role_id' => '1'],
            ['permission_id' => 130, 'role_id' => '1'],
            ['permission_id' => 131, 'role_id' => '1'],
            ['permission_id' => 132, 'role_id' => '1'],
            ['permission_id' => 133, 'role_id' => '1'],
            //end

            //user
            ['permission_id' => 134, 'role_id' => '2'],
            ['permission_id' => 135, 'role_id' => '2'],
            ['permission_id' => 136, 'role_id' => '2'],
            //end

            //admin
            ['permission_id' => 137, 'role_id' => '1'],
            ['permission_id' => 138, 'role_id' => '1'],
            ['permission_id' => 139, 'role_id' => '1'],
            ['permission_id' => 140, 'role_id' => '1'],
            ['permission_id' => 145, 'role_id' => '1'],
            ['permission_id' => 146, 'role_id' => '1'],
            ['permission_id' => 147, 'role_id' => '1'],
            ['permission_id' => 148, 'role_id' => '1'],
            ['permission_id' => 149, 'role_id' => '1'],
            ['permission_id' => 150, 'role_id' => '1'],
            ['permission_id' => 151, 'role_id' => '1'],
            ['permission_id' => 152, 'role_id' => '1'],
            ['permission_id' => 153, 'role_id' => '1'],
            ['permission_id' => 154, 'role_id' => '1'],
            ['permission_id' => 155, 'role_id' => '1'],
            ['permission_id' => 156, 'role_id' => '1'],
            //end

            //merchant - pm 1.5
            ['permission_id' => 109, 'role_id' => '13'],
            ['permission_id' => 110, 'role_id' => '13'],
            ['permission_id' => 111, 'role_id' => '13'],
            ['permission_id' => 112, 'role_id' => '13'],
            ['permission_id' => 113, 'role_id' => '13'],
            ['permission_id' => 114, 'role_id' => '13'],
            ['permission_id' => 115, 'role_id' => '13'],
            ['permission_id' => 116, 'role_id' => '13'], //merchant
            ['permission_id' => 117, 'role_id' => '13'], //merchant payment
            ['permission_id' => 134, 'role_id' => '13'],
            ['permission_id' => 135, 'role_id' => '13'],
            ['permission_id' => 136, 'role_id' => '13'],

            //user - pm 1.7
            // ['permission_id' => 157, 'role_id' => '2'],
            ['permission_id' => 157, 'role_id' => '1'],
            ['permission_id' => 158, 'role_id' => '1'],
            ['permission_id' => 159, 'role_id' => '1'],
            ['permission_id' => 160, 'role_id' => '1'],

            ['permission_id' => 161, 'role_id' => '1'],
            ['permission_id' => 162, 'role_id' => '1'],
            ['permission_id' => 163, 'role_id' => '1'],
            ['permission_id' => 164, 'role_id' => '1'],
        ];
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        PermissionRole::insert($data);
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    }
}
