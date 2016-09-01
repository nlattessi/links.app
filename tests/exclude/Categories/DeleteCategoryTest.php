<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    /** @test **/
    public function delete_can_remove_a_category_and_his_links()
    {
        $category = factory(\App\Category::class)->create();

        $this
            ->delete("/categories/{$category->uuid}", [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NO_CONTENT)
            ->isEmpty();
            
        $this
            ->notSeeInDatabase('categories', ['id' => $category->id])
            ->notSeeInDatabase('links', ['link_id' => $category->id]);
    }

    /** @test **/
    public function deleting_an_invalid_category_should_return_a_404()
    {
        $this
            ->delete("/categories/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a", [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND)
            ->seeJson([
                'error' => [
                    'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ]);
    }

    /** @test **/
    public function delete_should_not_match_an_invalid_route()
    {
        $this->delete('/categories/invalid-route', [], ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/Category not found/',
                $this->response->getContent(),
                'CategoriesController@delete route matching when it should not.'
            );
    }
}
