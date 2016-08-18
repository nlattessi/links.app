<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateCategoryTest extends TestCase
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
    public function store_can_create_new_category()
    {
        $postData = [
            'name' => 'PHP',
            'description' => 'PHP, Laravel, Lumen and related topics',
        ];

        $this->post('/categories', $postData, ['Accept' =>  'application/json']);
        
        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeHeaderWithRegExp('Location', '#/categories/[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$#');

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson($postData);

        $this->seeInDatabase('categories', $postData);
    }

    /** @test **/
    public function store_method_validates_required_fields()
    {
        $this
            ->post('/categories', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['name'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    /** @test **/
    public function store_invalidates_name_when_name_is_just_too_long()
    {
        $postData = [
            'name' => str_repeat('a', 256),
            'description' => 'A valid description',
        ];

        $this
            ->post('/categories', $postData, ['Accept' =>  'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals(
            ["The name may not be greater than 255 characters."],
            $data['name']
        );
    }

    /** @test **/
    public function store_is_valid_when_name_is_just_long_enough()
    {
        $postData = [
            'name' => str_repeat('a', 255),
            'description' => 'A valid description',
        ];

        $this
            ->post('/categories', $postData, ['Accept' =>  'application/json'])
            ->seeStatusCode(Response::HTTP_CREATED);

        $this->seeInDatabase('categories', $postData);
    }

    /** @test **/
    public function store_returns_a_valid_location_header()
    {
        $postData = [
            'name' => 'PHP',
            'description' => 'PHP, Laravel, Lumen and related topics',
        ];

        $this
            ->post('/categories', $postData, ['Accept' =>  'application/json'])
            ->seeStatusCode(Response::HTTP_CREATED);
        
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        $uuid = $data['data']['uuid'];
        $this->seeHeaderWithRegExp('Location', "#/categories/{$uuid}$#");
    }
}
