<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteLinkTest extends TestCase
{
    use DatabaseMigrations;

    public function testDeleteLink()
    {
        $link = factory(\App\Link::class)->create();

        $this
            ->seeInDatabase('links', [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'description' => $link->description,
                'created_at' => $link->created_at,
                'updated_at' => $link->updated_at,
            ]);

        $this
            ->delete("/links/{$link->id}")
            ->seeStatusCode(200)
            ->isEmpty();

        $this->notSeeInDatabase('links', ['id' => $link->id]);
    }

    public function testGet404IfNotExistsLink()
    {
        $this
            ->delete('links/999')
            ->seeStatusCode(404);
    }
}
