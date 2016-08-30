<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function test_show_should_return_a_valid_category()
    {
        $user = factory(\App\User::class)->create();
        $link = $this->linkFactory();
        $category = $link->category;
        $category->user()->associate($user)->save();

        $this
            ->get("/user/{$user->uuid}/categories/{$category->uuid}", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $category->uuid,
            'name' => $category->name,
        ]);
    }

    /** @test **/
    // public function show_should_fail_on_an_invalid_category()
    // {
    //     $this
    //         ->get('categories/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a', ['Accept' => 'application/json'])
    //         ->seeStatusCode(Response::HTTP_NOT_FOUND);
        
    //     $this->seeJson([
    //         'error' => [
    //             'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
    //             'status' => Response::HTTP_NOT_FOUND,
    //         ],
    //     ]);
    // }

    // /** @test **/
    // public function show_should_not_match_an_invalid_route()
    // {
    //     $this
    //         ->get('/categories/invalid-route', ['Accept' => 'application/json'])
    //         ->seeStatusCode(Response::HTTP_NOT_FOUND);

    //     $this
    //         ->assertNotRegExp(
    //             '/Category not found/',
    //             $this->response->getContent(),
    //             'CategoriesController@show route matching when it should not.'
    //         );
    // }
}
