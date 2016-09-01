
<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_links_show_should_return_a_valid_link()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $link = $user->categories->first()->links->first();

        $this->get(
            "/user/links/{$link->uuid}?token={$token}",
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $link->uuid,
            'title' => $link->title,
            'url' => $link->url,
        ]);
    }

    public function test_user_links_show_should_fail_on_an_invalid_category()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->get(
            "/user/links/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a?token={$token}",
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this->seeJson([
            'error' => [
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'status' => Response::HTTP_NOT_FOUND,
            ],
        ]);
    }

    public function test_user_links_show_should_not_match_an_invalid_route()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->get(
            "/user/links/invalid-route?token={$token}",
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'UserLinksController@show route matching when it should not.'
            );
    }
}
