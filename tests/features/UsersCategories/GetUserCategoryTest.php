<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_categories_show_should_return_a_valid_category()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories()->first();

        $this->get(
            "/user/categories/{$category->uuid}?token={$token}",
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $category->uuid,
            'name' => $category->name,
        ]);
    }

    public function test_user_categories_show_should_fail_on_an_invalid_category()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->get(
            "/user/categories/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a?token={$token}",
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

    public function test_user_categories_show_should_not_match_an_invalid_route()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->get(
            "/user/categories/invalid-route?token={$token}",
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this
            ->assertNotRegExp(
                '/Category not found/',
                $this->response->getContent(),
                'UserCategoriesController@show route matching when it should not.'
            );
    }
}
