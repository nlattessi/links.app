<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateCategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /** @test **/
    public function update_can_update_an_existing_category()
    {
        $category = factory(\App\Category::class)->create();

        $requestData = [
            'name' => 'New Category Name',
        ];

        $this
            ->put("/categories/{$category->id}", $requestData, ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson($requestData)
            ->seeInDatabase('categories', ['name' => 'New Category Name',])
            ->notSeeInDatabase('categories', ['name' => $category->name]);

        $this->assertArrayHasKey('data', $this->response->getData(true));
    }

    /** @test **/
    public function update_method_validates_required_fields()
    {
        $category = factory(\App\Category::class)->create();

        $this
            ->put("/categories/{$category->id}", [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['name'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    /*+ @test **/
    public function updating_an_invalid_category_should_return_a_404()
    {
        $this
            ->put('categories/999', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND)
            ->seeJson([
                'error' => [
                    'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ]);
    }

    /** @test **/
    public function update_should_not_match_an_invalid_route()
    {
        $this->put('/categories/invalid-route', [], ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/Category not found/',
                $this->response->getContent(),
                'CategoriesController@update route matching when it should not.'
            );
    }

    /** @test **/
    public function update_invalidates_name_when_name_is_just_too_long()
    {
        $category = factory(\App\Category::class)->create();

        $newName = str_repeat('a', 256);

        $postData = [
            'name' => $newName,
            'description' => 'A valid description',
        ];

        $this
            ->put("/categories/{$category->id}", $postData, ['Accept' =>  'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals(
            ["The name may not be greater than 255 characters."],
            $data['name']
        );

        $this->notSeeInDatabase('categories', ['name' => $newName]);
    }

    /** @test **/
    public function update_is_valid_when_name_is_just_long_enough()
    {
        $category = factory(\App\Category::class)->create();

        $postData = [
            'name' => str_repeat('a', 255),
            'description' => 'A valid description',
        ];

        $this
            ->put("/categories/{$category->id}", $postData, ['Accept' =>  'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $this->seeInDatabase('categories', $postData);
    }
}
