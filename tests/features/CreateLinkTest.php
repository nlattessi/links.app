<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateLinkTest extends TestCase
{
    use DatabaseMigrations;

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
            // ->seeJson([
            //     'title' => 'Links app',
            //     'url' => "https://links.app",
            //     'description' => "A links storage service",
            // ])
            ->seeHeaderWithRegExp('Location', '#/links/[\d]+$#');
            // ->seeInDatabase('links', ['url' => "https://links.app"]);
        
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertEquals('Links app', $data['title']);
        $this->assertEquals('https://links.app', $data['url']);
        $this->assertEquals('A links storage service', $data['description']);
        $this->assertTrue($data['id'] > 0, 'Expected a positive integer, but did not see one');

        $this->seeInDatabase('links', ['url' => "https://links.app"]);
    }
}
