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

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        foreach ($links as $link) {
            $this->seeJson([
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
