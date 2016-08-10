<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

// $factory->define(App\User::class, function ($faker) {
//     return [
//         'name' => $faker->name,
//         'email' => $faker->email,
//     ];
// });

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->safeEmail,
        'password' => app('hash')->make(str_random(10)),
    ];
});

$factory->define(App\Link::class, function ($faker) {
    return [
        'title' => $faker->domainName,
        'url' => $faker->url,
        'description' => $faker->sentence,
    ];
});

$factory->define(App\Category::class, function ($faker) {
    return [
        'name' => $faker->word,
        'description' => join(" ", $faker->sentences(rand(3, 5))),
    ];
});
