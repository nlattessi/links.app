<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteUserLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_links_delete_can_remove_a_category_and_his_links()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $link = $user->categories->first()->links->first();

        $this->delete(
            "/user/links/{$link->uuid}?token={$token}",
            [],
            ['Accept' => 'application/json']
        );
        
        $this
            ->seeStatusCode(Response::HTTP_NO_CONTENT)
            ->isEmpty();

        $this->notSeeInDatabase('links', ['id' => $link->id]);
    }

    public function test_user_links_deleting_an_invalid_category_should_return_a_404()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->delete(
            "/user/links/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->seeStatusCode(Response::HTTP_NOT_FOUND)
            ->seeJson([
                'error' => [
                    'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ]);
    }

    public function test_user_links_delete_should_not_match_an_invalid_route()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->delete(
            "/user/links/invalid-route?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'UserLinksController@delete route matching when it should not.'
            );
    }
}
