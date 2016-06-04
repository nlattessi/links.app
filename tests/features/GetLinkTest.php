<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetLink()
    {
        $link = factory(\App\Link::class)->create();

        $this
            ->get("/links/{$link->id}")
            ->seeStatusCode(200)
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

    public function testShouldFailIfLinkIdNotExist()
    {
        $this
            ->get('links/999')
            ->seeStatusCode(404)
            ->seeJson([
                'error' => [
                    'message' => 'Link not found',
                ],
            ]);
    }

    public function testShouldNotMatchAnInvalidRoute()
    {
        $this->get('/links/invalid-route');

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@show route matching when it should not.'
            );
    }
}
