<?php

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
    ];
});

$factory->define(App\Category::class, function ($faker) {
    return [
        'name' => $faker->word,
    ];
});
