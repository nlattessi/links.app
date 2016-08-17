<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetCategoriesTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    /** @test **/
    public function index_responds_with_200_status_code()
    {
        $this
            ->get('/categories')
            ->seeStatusCode(Response::HTTP_OK);
    }

    /** @test **/
    public function index_should_return_a_collection_of_records()
    {
        $categories = factory(\App\Category::class, 2)->create();

        $this
            ->get('/categories', ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(2, $body['data']);

        foreach ($categories as $category) {
            $this->seeJson([
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
            ]);
        }
    }

    /** @test **/
    public function show_optionally_includes_links()
    {
        $link = $this->linkFactory();
        $category = $link->category;

        $this
            ->get(
                "/categories/{$category->id}?include=links",
                ['Accept' => 'application/json']
            )
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('data', $data['links']);
        $this->assertCount(1, $data['links']['data']);

        // See Category Data
        $this->seeJson([
            'id' => $category->id,
            'name' => $category->name,
        ]);

        // Test included link Data (the first record)
        $actual = $data['links']['data'][0];
        $this->assertEquals($link->id, $actual['id']);
        $this->assertEquals($link->title, $actual['title']);
        $this->assertEquals($link->url, $actual['url']);
        $this->assertEquals($link->description, $actual['description']);
    }
}
