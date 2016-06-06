<?php

use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now('UTC'));
    }

    public function tearDown()
    {
        parent::tearDown();

        Carbon::setTestNow();
    }

    public function testUpdateLink()
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
            ]);

        $this
            ->seeStatusCode(200)
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
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['created_at']);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['updated_at']);

        $this->notSeeInDatabase('links', ['url' => $link->url]);
    }

    public function testShouldFailIfLinkIdNotExist()
    {
        $this
            ->put('links/999', [], ['Accept' => 'application/json'])
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
        $this->put('/links/invalid-route', []);

        $this
            ->assertNotRegExp(
                '/Link not found/',
                $this->response->getContent(),
                'LinksController@update route matching when it should not.'
            );
    }
}
