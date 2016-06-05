<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testDeleteLink()
    {
        $link = factory(\App\Link::class)->create();

        $this
            ->seeInDatabase('links', [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'created_at' => $link->created_at,
                'updated_at' => $link->updated_at,
            ]);

        $this
            ->delete("/links/{$link->id}")
            ->seeStatusCode(204)
            ->isEmpty();

        $this->notSeeInDatabase('links', ['id' => $link->id]);
    }

    public function testShouldFailIfLinkIdNotExist()
    {
        $this
            ->delete('links/999')
            ->seeStatusCode(404)
            ->seeJson([
                'error' => [
                    'message' => 'Link not found',
                ],
            ]);
    }

    public function testShouldNotMatchAnInvalidRoute()
    {
        $this->delete('/links/invalid-route');

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@update route matching when it should not.'
            );
    }
}
