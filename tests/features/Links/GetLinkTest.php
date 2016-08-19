<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function test_get_link()
    {
        $link = $this->linkFactory();

        $this
            ->get("/links/{$link->uuid}", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertEquals($link->uuid, $data['id']);
        $this->assertEquals($link->title, $data['title']);
        $this->assertEquals($link->url, $data['url']);
        $this->assertEquals($link->description, $data['description']);
        $this->assertEquals($link->category->name, $data['category']);
    }

    public function test_should_fail_if_link_uuid_not_exist()
    {
        $this
            ->get('links/25769c6c-d34d-4bfe-ba98-e0ee856f3e7a', ['Accept' => 'application/json'])
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
        $this->get('/links/invalid-route', ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@show route matching when it should not.'
            );
    }
}
