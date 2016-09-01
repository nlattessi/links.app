<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateUserLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_links_update_can_update_an_existing_link()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $link = $user->categories->first()->links->first();

        $patchData = [
            'title' => 'Links.app',
            'url' => 'http://links.app',
        ];

        $this->patch(
            "/user/links/{$link->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );
        
        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson($patchData)
            ->seeInDatabase('links', ['title' => 'Links.app',])
            ->notSeeInDatabase('links', ['title' => $link->title]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function test_user_links_update_can_update_a_link_category()
    {
        $user = $this->userFactory(2);
        $token = Auth::tokenById($user->id);

        $link = $user->categories->get(0)->links->first();
        $category = $user->categories->get(1);

        $patchData = [
            'category' => $category->uuid,
        ];

        $this->patch(
            "/user/links/{$link->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson([
                'title' => $link->title,
                'url' => $link->url,
                'category' => $category->name,
            ])
            ->seeInDatabase('links', ['title' => $link->title, 'category_id' => $category->id]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function test_user_links_update_method_no_update_if_no_data_sended()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $link = $user->categories->first()->links->first();

        $this->patch(
            "/user/links/{$link->uuid}?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson([
                'title' => $link->title,
            ])
            ->seeInDatabase('links', ['title' => $link->title]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function test_user_links_updating_an_invalid_link_should_return_a_404()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->patch(
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

    public function test_user_links_update_should_not_match_an_invalid_route()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->patch(
            "/user/links/invalid-route?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'UserLinksController@update route matching when it should not.'
            );
    }

    public function test_user_links_update_invalidates_title_when_title_is_just_too_long()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $link = $user->categories->first()->links->first();

        $newTitle = str_repeat('a', 256);
        $patchData = [
            'title' => $newTitle,
        ];

        $this->patch(
            "/user/links/{$link->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertEquals(
            ["The title may not be greater than 255 characters."],
            $data['title']
        );

        $this->notSeeInDatabase('links', ['title' => $newTitle]);
    }

    public function test_user_links_update_is_valid_when_name_is_just_long_enough()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $link = $user->categories->first()->links->first();

        $patchData = [
            'title' => str_repeat('a', 255),
        ];

        $this->patch(
            "/user/links/{$link->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_OK);

        $this->seeInDatabase('links', $patchData);
    }
}
