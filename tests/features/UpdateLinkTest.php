<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateLinkTest extends TestCase
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

    public function test_update_link()
    {
        $link = factory(\App\Link::class)->create();

        $this->notSeeInDatabase('links', [
            'title' => 'Links app',
            'url' => 'https://links.app',
            'description' => 'A links storage service',
        ]);

        $this
            ->put("/links/{$link->id}", [
                'title' => 'Links app',
                'url' => 'https://links.app',
                'description' => 'A links storage service',
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson([
                'id' => $link->id,
                'title' => 'Links app',
                'url' => 'https://links.app',
                'description' => 'A links storage service',
            ])
            ->seeInDatabase('links', ['url' => 'https://links.app']);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertArrayHasKey('created_at', $data);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['created_at']);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['updated_at']);

        $this->notSeeInDatabase('links', ['url' => $link->url]);
    }

    public function test_should_fail_if_id_not_exist()
    {
        $this
            ->put('links/999', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND)
            ->seeJson([
                'error' => [
                    'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ]);
    }

    public function test_should_not_match_an_invalid_route()
    {
        $this->put('/links/invalid-route', [], ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@update route matching when it should not.'
            );
    }

    public function test_it_validates_required_fields_when_updating_a_link()
    {
        $link = factory(\App\Link::class)->create();

        $this->put("/links/{$link->id}", [], ['Accept' => 'application/json']);
        
        $this->assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->response->getStatusCode()
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('url', $body);

        $this->assertEquals(['The title field is required.'], $body['title']);
        $this->assertEquals(['The url field is required.'], $body['url']);
    }

    public function test_update_fails_pass_validation_when_title_is_too_long()
    {
        $link = factory(\App\Link::class)->create();
        $link->title = str_repeat('a', 256);

        $this
            ->put("/links/{$link->id}", [
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'title' => ['The title may not be greater than 255 characters.'],
            ])
            ->notSeeInDatabase('links', ['title' => $link->title]);
    }

    public function test_update_passes_validation_when_title_is_exactly_max()
    {
        $link = factory(\App\Link::class)->create();
        $link->title = str_repeat('a', 255);

        $this
            ->put("/links/{$link->id}", [
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeInDatabase('links', ['title' => $link->title]);
    }
}
