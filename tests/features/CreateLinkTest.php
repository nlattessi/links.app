<?php

use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateLinkTest extends TestCase
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

    public function testCreateLink()
    {
        $this
            ->post('/links', [
                'title' => 'Links app',
                'url' => 'https://links.app',
                'description' => 'A links storage service',
            ]);

        $this
            ->seeStatusCode(201)
            ->seeHeaderWithRegExp('Location', '#/links/[\d]+$#');
        
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertTrue($data['id'] > 0, 'Expected a positive integer, but did not see one');
        $this->assertEquals('Links app', $data['title']);
        $this->assertEquals('https://links.app', $data['url']);
        $this->assertEquals('A links storage service', $data['description']);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['created_at']);
        $this->assertEquals(Carbon::now()->toIso8601String(), $data['updated_at']);

        $this->seeInDatabase('links', ['url' => "https://links.app"]);
    }
}
