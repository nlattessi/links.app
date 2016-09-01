<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteUserCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_categories_delete_can_remove_a_category_and_his_links()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $this->delete(
            "/user/categories/{$category->uuid}?token={$token}",
            [],
            ['Accept' => 'application/json']
        );
        
        $this
            ->seeStatusCode(Response::HTTP_NO_CONTENT)
            ->isEmpty();

        $this
            ->notSeeInDatabase('categories', ['id' => $category->id])
            ->notSeeInDatabase('links', ['link_id' => $category->id]);
    }

    public function test_user_categories_deleting_an_invalid_category_should_return_a_404()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->delete(
            "/user/categories/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a?token={$token}",
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

    public function test_user_categories_delete_should_not_match_an_invalid_route()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->delete(
            "/user/categories/invalid-route?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->assertNotRegExp(
                '/Category not found/',
                $this->response->getContent(),
                'UserCategoriesController@delete route matching when it should not.'
            );
    }
}
