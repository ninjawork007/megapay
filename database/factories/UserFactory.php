<?php

use App\User;
use App\Role;
use App\Image;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

$factory->define(App\User::class, function (Faker $faker) {
    // static $password;
    // $users  = User::pluck('id')->all();
    // $roles  = Role::pluck('id')->all();
    // $images = Image::pluck('id')->all();

    // return [

        // 'role_id'        => 1,
        // 'image_id'       => 1,
        // 'user_type'      => $faker->boolean($chanceOfGettingTrue = 10),
        // 'first_name'     => 'parvez',
        // 'last_name'      => 'robi',
        // 'phone'          => '01521108069',
        // 'email'          => 'parvezrobi@yahoo.com',
        // 'phrase'         => 'iamparvez',
        // 'status'         => rand(0,1),
        // 'password'       => $password ?: $password = bcrypt('parvezsecret'),
        // 'remember_token' => str_random(10),
    // ];
});
