<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetLink()
    {
        $link = factory(\App\Link::class)->create();

        $this
            ->get("/links/{$link->id}")
            ->seeStatusCode(200);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertEquals($link->id, $data['id']);
        $this->assertEquals($link->title, $data['title']);
        $this->assertEquals($link->url, $data['url']);
        $this->assertEquals($link->description, $data['description']);
        $this->assertEquals($link->created_at->toIso8601String(), $data['created_at']);
        $this->assertEquals($link->updated_at->toIso8601String(), $data['updated_at']);
    }

    public function testShouldFailIfLinkIdNotExist()
    {
        $this
            ->get('links/999', ['Accept' => 'application/json'])
            ->seeStatusCode(404)
            ->seeJson([
                'error' => [
                    'message' => 'Not Found',
                    'status' => 404,
                ],
            ]);
    }

    public function testShouldNotMatchAnInvalidRoute()
    {
        $this->get('/links/invalid-route');

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@show route matching when it should not.'
            );
    }
}
