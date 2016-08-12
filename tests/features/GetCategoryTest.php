<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    /** @test **/
    public function show_should_return_a_valid_category()
    {
        $link = $this->linkFactory();
        $category = $link->category;

        $this
            ->get("/categories/{$category->id}", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->seeJson([
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'created_at' => $category->created_at->toDateTimeString(),
            'updated_at' => $category->updated_at->toDateTimeString(),
        ]);
    }

    /** @test **/
    public function show_should_fail_on_an_invalid_category()
    {
        $this
            ->get('categories/999', ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND);
        
        $this->seeJson([
            'error' => [
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'status' => Response::HTTP_NOT_FOUND,
            ],
        ]);
    }

    /** @test **/
    public function show_should_not_match_an_invalid_route()
    {
        $this
            ->get('/categories/invalid-route', ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this
            ->assertNotRegExp(
                '/Category not found/',
                $this->response->getContent(),
                'CategoriesController@show route matching when it should not.'
            );
    }
}