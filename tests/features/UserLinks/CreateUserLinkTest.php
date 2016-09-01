<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateUserLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_links_store_can_create_new_link()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $postData = [
            'title' => 'Links.app',
            'url' => 'http://links.app',
            'category' => $category->uuid,
        ];

        $this->post(
            "/user/links?token={$token}",
            $postData,
            ['Accept' =>  'application/json']
        );

        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeHeaderWithRegExp('Location', '#/links/' . env('UUID_REGEX') . '$#');

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson([
            'title' => 'Links.app',
            'url' => 'http://links.app',
        ]);

        $this->seeInDatabase('links', [
            'title' => 'Links.app',
            'url' => 'http://links.app',
        ]);
    }

    public function test_user_links_store_method_validates_required_fields()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->post(
            "/user/links?token={$token}",
            [],
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['title', 'url', 'category'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    public function test_user_links_store_invalidates_title_when_is_just_too_long()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $postData = [
            'title' => str_repeat('a', 256),
            'url' => 'http://links.app',
            'category' => $category->uuid,
        ];

        $this->post(
            "/user/links?token={$token}",
            $postData,
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
    }

    public function test_user_links_store_is_valid_when_title_is_just_long_enough()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $postData = [
            'title' => str_repeat('a', 255),
            'url' => 'http://links.app',
            'category' => $category->uuid,
        ];

        $this->post(
            "/user/links?token={$token}",
            $postData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_CREATED);

        $this->seeInDatabase('links', [
            'title' => str_repeat('a', 255),
            'url' => 'http://links.app',
        ]);
    }

    public function test_user_links_store_returns_a_valid_location_header()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $postData = [
            'title' => str_repeat('a', 255),
            'url' => 'http://links.app',
            'category' => $category->uuid,
        ];

        $this->post(
            "/user/links?token={$token}",
            $postData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_CREATED);
        
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        $id = $data['data']['id'];
        $this->seeHeaderWithRegExp('Location', "#/links/{$id}$#");
    }

    public function test_user_links_store_fails_if_category_not_exists()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $postData = [
            'title' => 'Links.app',
            'url' => 'http://links.app',
            'category' => '25769c6c-d34d-4bfe-ba98-e0ee856f3e7a',
        ];

        $this->post(
            "/user/links?token={$token}",
            $postData,
            ['Accept' =>  'application/json']
        );

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('category', $data);
        $this->assertEquals(
            ["The selected category is invalid."],
            $data['category']
        );

        $this->notSeeInDatabase('links', [
            'title' => 'Links.app',
            'url' => 'http://links.app',
        ]);
    }

    public function test_user_links_store_fails_if_category_not_belongs_to_user()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $user2 = $this->userFactory();
        $category = $user2->categories->first();

        $postData = [
            'title' => 'Links.app',
            'url' => 'http://links.app',
            'category' => $category->uuid,
        ];

        $this->post(
            "/user/links?token={$token}",
            $postData,
            ['Accept' =>  'application/json']
        );

        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this->seeJson([
            'error' => [
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'status' => Response::HTTP_NOT_FOUND,
            ],
        ]);
    }
}
