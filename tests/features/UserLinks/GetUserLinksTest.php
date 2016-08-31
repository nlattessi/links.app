<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserLinksTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_links_show_should_return_a_collection_of_links_associated_to_the_user()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this
            ->get("/user/links?token={$token}", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(5, $body['data']);

        foreach ($user->categories as $category) {
            foreach ($category->links as $link) {
                $this->seeJson([
                    'id' => $link->uuid,
                    'title' => $link->title,
                    'url' => $link->url,
                ]);    
            }
        }
    }

    public function test_user_links_show_should_return_a_collection_of_links_associated_to_the_user_with_3_categories()
    {
        $user = $this->userFactory(3);
        $token = Auth::tokenById($user->id);

        $this
            ->get("/user/links?token={$token}", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(15, $body['data']);

        foreach ($user->categories as $category) {
            foreach ($category->links as $link) {
                $this->seeJson([
                    'id' => $link->uuid,
                    'title' => $link->title,
                    'url' => $link->url,
                ]);    
            }
        }
    }
}
