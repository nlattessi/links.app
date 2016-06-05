<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinksTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetLinks()
    {
        $links = factory(\App\Link::class, 5)->create();

        $expected = [
            'data' => $links->toArray(),
        ];

        $this
            ->get('/links')
            ->seeStatusCode(200)
            ->seeJsonEquals($expected);
    }
}
