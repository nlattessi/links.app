<?php

use Illuminate\Database\Seeder;

class LinksSeeder extends Seeder
{
    public function run()
    {
        factory(App\Category::class, 10)->create()->each(function ($category) {
            $linksCount = rand(1, 5);

            while ($linksCount > 0) {
                $category->links()->save(factory(App\Link::class)->make());
                $linksCount--;
            }
        });

        // factory(App\Link::class, 20)->create();
    }
}
