<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinksTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetLinks()
    {
        $links = factory(\App\Link::class, 5)->create();

        $this
            ->get('/links')
            ->seeStatusCode(200);

        foreach ($links as $link) {
            $this
                ->seeInDatabase('links', [
                    'id' => $link->id,
                    'title' => $link->title,
                    'url' => $link->url,
                    'description' => $link->description,
                    'created_at' => $link->created_at,
                    'updated_at' => $link->updated_at,
                ])
                ->seeJson([
                    'id' => $link->id,
                    'title' => $link->title,
                    'url' => $link->url,
                    'description' => $link->description,
                    'created_at' => $link->created_at->toDateTimeString(),
                    'updated_at' => $link->updated_at->toDateTimeString(),
                ]);
        }
    }
}
