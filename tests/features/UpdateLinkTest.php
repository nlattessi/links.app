<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testUpdateLink()
    {
        $link = factory(\App\Link::class)->create();

        $this->notSeeInDatabase('links', ['url' => "https://links.app"]);

        $this
            ->put("/links/{$link->id}", [
                'id' => 10,
                'title' => 'Links app',
                'url' => "https://links.app",
                'description' => "A links storage service",
            ]);

        $this
            ->seeStatusCode(200)
            ->seeJson([
                'id' => $link->id,
                'title' => 'Links app',
                'url' => "https://links.app",
                'description' => "A links storage service",
            ])
            ->seeInDatabase('links', ['url' => "https://links.app"]);

        $this->notSeeInDatabase('links', ['url' => $link->url]);
    }

    public function testShouldFailIfLinkIdNotExist()
    {
        $this
            ->put('links/999', [], ['Accept' => 'application/json'])
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
        $this->put('/links/invalid-route', []);

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@update route matching when it should not.'
            );
    }
}
