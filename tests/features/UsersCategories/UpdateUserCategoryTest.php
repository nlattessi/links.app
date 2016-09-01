<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateUserCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_categories_update_can_update_an_existing_category()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $patchData = [
            'name' => 'New Category Name',
        ];

        $this->patch(
            "/user/categories/{$category->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );
        
        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson($patchData)
            ->seeInDatabase('categories', ['name' => 'New Category Name',])
            ->notSeeInDatabase('categories', ['name' => $category->name]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function test_user_categories_update_method_no_update_if_no_data_sended()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $this->patch(
            "/user/categories/{$category->uuid}?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson([
                'name' => $category->name,
            ])
            ->seeInDatabase('categories', ['name' => $category->name]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    public function test_user_categories_updating_an_invalid_category_should_return_a_404()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->patch(
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

    public function test_user_categories_update_should_not_match_an_invalid_route()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->patch(
            "/user/categories/invalid-route?token={$token}",
            [],
            ['Accept' => 'application/json']
        );

        $this
            ->assertNotRegExp(
                '/Category not found/',
                $this->response->getContent(),
                'UserCategoriesController@update route matching when it should not.'
            );
    }

    public function test_user_categories_update_invalidates_name_when_name_is_just_too_long()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();
        
        $newName = str_repeat('a', 256);
        $patchData = [
            'name' => $newName,
        ];

        $this->patch(
            "/user/categories/{$category->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals(
            ["The name may not be greater than 255 characters."],
            $data['name']
        );

        $this->notSeeInDatabase('categories', ['name' => $newName]);
    }

    public function test_user_categories_update_is_valid_when_name_is_just_long_enough()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $category = $user->categories->first();

        $patchData = [
            'name' => str_repeat('a', 255),
        ];

        $this->patch(
            "/user/categories/{$category->uuid}?token={$token}",
            $patchData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_OK);

        $this->seeInDatabase('categories', $patchData);
    }
}
