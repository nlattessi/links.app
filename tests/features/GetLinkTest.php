<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetLink()
    {
        $link = factory(\App\Link::class)->create();

        $expected = [
            'data' => $link->toArray(),
        ];

        $this
            ->get("/links/{$link->id}")
            ->seeStatusCode(200)
            ->seeJsonEquals($expected);
    }

    public function testShouldFailIfLinkIdNotExist()
    {
        $this
            ->get('links/999', ['Accept' => 'application/json'])
            ->seeStatusCode(404)
            ->seeJson([
                'error' => [
                    'message' => 'Not Found',
                    'status' => 404,
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
