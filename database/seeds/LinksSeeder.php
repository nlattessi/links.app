<?php

use Illuminate\Database\Seeder;

class LinksSeeder extends Seeder
{
    public function run()
    {
        factory(App\User::class)->create([
            'email' => 'user@email.com',
            'password' => app('hash')->make('password'),
        ])->each(function ($user) {

            factory(App\Category::class, 10)->create([
                'user_id' => $user->id
            ])->each(function ($category) {

                $linksCount = rand(1, 5);

                while ($linksCount > 0) {
                    $category->links()->save(factory(App\Link::class)->make());
                    $linksCount--;
                }
            });

        });

        factory(App\User::class, 4)->create()->each(function ($user) {

            factory(App\Category::class, 10)->create([
                'user_id' => $user->id
            ])->each(function ($category) {

                $linksCount = rand(1, 5);

                while ($linksCount > 0) {
                    $category->links()->save(factory(App\Link::class)->make());
                    $linksCount--;
                }
            });

        });
    }
}
