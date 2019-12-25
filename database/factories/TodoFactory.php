<?php

use App\User;
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
$factory->define(App\Todo::class, function (Faker $faker) {

    // $users = User::pluck('id')->all();

    // return [
    //     'user_id' => $faker->randomElement($users),
    //     'task' => $faker->sentence,
    //     'done' => rand(0,1),
    // ];
});
