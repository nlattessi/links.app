<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function test_delete_link()
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
            ->delete("/links/{$link->id}", [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NO_CONTENT)
            ->isEmpty();

        $this->notSeeInDatabase('links', ['id' => $link->id]);
    }

    public function test_should_fail_if_id_not_exist()
    {
        $this
            ->delete('links/999', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND)
            ->seeJson([
                'error' => [
                    'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ]);
    }

    public function test_should_not_match_an_invalid_route()
    {
        $this->delete('/links/invalid-route', [], ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@update route matching when it should not.'
            );
    }
}
