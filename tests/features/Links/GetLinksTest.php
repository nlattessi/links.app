<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetLinksTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function test_get_links()
    {
        $links = $this->linkFactory(5);

        $this
            ->get('/links', ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        foreach ($links as $link) {
            $this->seeJson([
                'id' => $link->uuid,
                'title' => $link->title,
                'url' => $link->url,
                'category' => $link->category->name,
            ]);
        }
    }
}
