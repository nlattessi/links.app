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
                'url' => "https://links.app",
                'description' => "A links storage service",
            ]);

        $this
            ->seeStatusCode(200)
            ->seeJson([
                'title' => 'Links app',
                'url' => "https://links.app",
                'description' => "A links storage service",
            ])
            ->seeInDatabase('links', ['url' => "https://links.app"]);
    }
}
