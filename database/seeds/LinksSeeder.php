<?php

use Illuminate\Database\Seeder;

class LinksSeeder extends Seeder
{
    public function run()
    {
        factory(App\Link::class, 20)->create();
    }
}
