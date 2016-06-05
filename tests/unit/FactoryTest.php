<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class FactoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testFactoryLinksCreatedAreInDatabase()
    {
        $links = factory(\App\Link::class, 5)->create();

        foreach ($links as $link) {
            $this
                ->seeInDatabase('links', [
                    'id' => $link->id,
                    'title' => $link->title,
                    'url' => $link->url,
                    'description' => $link->description,
                    'created_at' => $link->created_at,
                    'updated_at' => $link->updated_at,
                ]);
        }
    }
}
