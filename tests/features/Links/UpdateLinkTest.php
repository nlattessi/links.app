<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function test_update_link()
    {
        $link = $this->linkFactory();

        $this->notSeeInDatabase('links', [
            'title' => 'Links app',
            'url' => 'https://links.app',
        ]);

        $this
            ->put("/links/{$link->uuid}", [
                'id' => 5,
                'uuid' => '25769c6c-d34d-4bfe-ba98-e0ee856f3e7a',
                'title' => 'Links app',
                'url' => 'https://links.app',
                'category_id' => $link->category->uuid,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson([
                'id' => $link->uuid,
                'title' => 'Links app',
                'url' => 'https://links.app',
            ])
            ->seeInDatabase('links', ['url' => 'https://links.app']);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $this->notSeeInDatabase('links', ['url' => $link->url]);
    }

    public function test_should_fail_if_uuid_not_exist()
    {
        $category = factory(\App\Category::class)->create();

        $this
            ->put('/links/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a', [
                'title' => 'Links app',
                'url' => 'https://links.app',
                'category_id' => $category->uuid,
            ], ['Accept' => 'application/json']);

        $this
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
        $link = $this->linkFactory();

        $this->put("/links/{$link->uuid}", [], ['Accept' => 'application/json']);
        
        $this->assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->response->getStatusCode()
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('url', $body);
        $this->assertArrayHasKey('category_id', $body);

        $this->assertEquals(['The title field is required.'], $body['title']);
        $this->assertEquals(['The url field is required.'], $body['url']);
        $this->assertEquals(['The category id field is required.'], $body['category_id']);
    }

    public function test_update_fails_pass_validation_when_title_is_too_long()
    {
        $link = $this->linkFactory();
        $link->title = str_repeat('a', 256);

        $this
            ->put("/links/{$link->uuid}", [
                'title' => $link->title,
                'url' => $link->url,
                'category_id' => $link->category->uuid,
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
        $link = $this->linkFactory();
        $link->title = str_repeat('a', 255);

        $this
            ->put("/links/{$link->uuid}", [
                'title' => $link->title,
                'url' => $link->url,
                'category_id' => $link->category->uuid,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeInDatabase('links', ['title' => $link->title]);
    }

    public function test_update_fails_pass_validation_when_category_id_is_not_uuid()
    {
        $link = $this->linkFactory();

        $this
            ->put("/links/{$link->uuid}", [
                'title' => 'Links app',
                'url' => 'https://links.app',
                'category_id' => 'abc',
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'category_id' => ['The category id format is invalid.'],
            ])
            ->notSeeInDatabase('links', ['title' => 'Links app']);
    }
}
