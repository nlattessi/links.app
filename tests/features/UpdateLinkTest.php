<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testUpdateLink()
    {
        $link = factory(\App\Link::class)->create();

        $this->notSeeInDatabase('links', ['url' => "https://links.app"]);

        $this
            ->put("/links/{$link->id}", [
                'id' => 10,
                'title' => 'Links app',
                'url' => "https://links.app",
                'description' => "A links storage service",
            ]);

        $this
            ->seeStatusCode(200)
            ->seeInJson([
                'id' => 10,
                'title' => 'Links app',
                'url' => "https://links.app",
                'description' => "A links storage service",
            ])
            ->seeInDatabase('links', ['url' => "https://links.app"]);

        $this->notSeeInDatabase('links', ['url' => $link->url]);
    }

    public function testGet404IfNotExistsLink()
    {
        $this
            ->put('links/999', [])
            ->seeStatusCode(404);
    }
}
