<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now('UTC'));
        $this->app->instance('middleware.disable', true);
    }

    public function tearDown()
    {
        parent::tearDown();

        Carbon::setTestNow();
    }

    public function test_create_link()
    {
        $category = factory(\App\Category::class)->create([
            'name' => 'PHP'
        ]);

        $this
            ->post('/links', [
                'title' => 'Links app',
                'url' => 'https://links.app',
                'description' => 'A links storage service',
                'category_id' => $category->id,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeHeaderWithRegExp('Location', '#/links/[\d]+$#');
        
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertTrue($data['id'] > 0, 'Expected a positive integer, but did not see one');
        $this->assertEquals('Links app', $data['title']);
        $this->assertEquals('https://links.app', $data['url']);
        $this->assertEquals('A links storage service', $data['description']);
        $this->assertEquals('PHP', $data['category']);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['created_at']);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['updated_at']);

        $this->seeInDatabase('links', ['url' => "https://links.app"]);
    }

    public function test_it_validates_required_fields_when_creating_a_new_link()
    {
        $this
            ->post('/links', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('url', $body);
        $this->assertArrayHasKey('category_id', $body);

        $this->assertEquals(['The title field is required.'], $body['title']);
        $this->assertEquals(['The url field is required.'], $body['url']);
        $this->assertEquals(['The category id field is required.'], $body['category_id']);
    }

    public function test_create_fails_pass_validation_when_title_is_too_long()
    {
        $link = $this->linkFactory();
        $link->title = str_repeat('a', 256);

        $this
            ->post('/links', [
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'category_id' => $link->category->id,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'title' => ['The title may not be greater than 255 characters.'],
            ])
            ->notSeeInDatabase('links', ['title' => $link->title]);
    }

    public function test_create_passes_validation_when_title_is_exactly_max()
    {
        $link = $this->linkFactory();
        $link->title = str_repeat('a', 255);

        $this
            ->post('/links', [
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'category_id' => $link->category->id,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeInDatabase('links', ['title' => $link->title]);
    }

    public function test_create_fails_pass_validation_when_category_id_not_exists()
    {
        $this
            ->post('/links', [
                'title' => 'Links app',
                'url' => 'https://links.app',
                'description' => 'A links storage service',
                'category_id' => 999,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'category_id' => ['The selected category id is invalid.'],
            ])
            ->notSeeInDatabase('links', ['title' => 'Links app']);
    }
}
