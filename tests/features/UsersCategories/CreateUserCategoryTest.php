<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateUserCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_categories_store_can_create_new_category()
    {
        $user = factory(\App\User::class)->create();
        $token = Auth::tokenById($user->id);

        $postData = [
            'name' => 'PHP',
        ];

        $this->post(
            "/user/categories?token={$token}",
            $postData,
            ['Accept' =>  'application/json']
        );
        
        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeHeaderWithRegExp('Location', '#/categories/' . env('UUID_REGEX') . '$#');

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson($postData);

        $this->seeInDatabase('categories', $postData);
    }

    public function test_user_categories_store_method_validates_required_fields()
    {
        $user = factory(\App\User::class)->create();
        $token = Auth::tokenById($user->id);

        $this->post(
            "/user/categories?token={$token}",
            [],
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['name'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    public function test_user_categories_store_invalidates_name_when_name_is_just_too_long()
    {
        $user = factory(\App\User::class)->create();
        $token = Auth::tokenById($user->id);

        $postData = [
            'name' => str_repeat('a', 256),
        ];

        $this->post(
            "/user/categories?token={$token}",
            $postData,
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
    }

    public function test_user_categories_store_is_valid_when_name_is_just_long_enough()
    {
        $user = factory(\App\User::class)->create();
        $token = Auth::tokenById($user->id);

        $postData = [
            'name' => str_repeat('a', 255),
        ];

        $this->post(
            "/user/categories?token={$token}",
            $postData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_CREATED);

        $this->seeInDatabase('categories', $postData);
    }

    public function test_user_categories_store_returns_a_valid_location_header()
    {
        $user = factory(\App\User::class)->create();
        $token = Auth::tokenById($user->id);

        $postData = [
            'name' => 'PHP',
        ];

        $this->post(
            "/user/categories?token={$token}",
            $postData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_CREATED);
        
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        $id = $data['data']['id'];
        $this->seeHeaderWithRegExp('Location', "#/categories/{$id}$#");
    }
}
