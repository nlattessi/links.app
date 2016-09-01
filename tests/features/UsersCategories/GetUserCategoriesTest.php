<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserCategoriesTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_categories_show_should_return_a_collection_of_categories_associated_to_the_user()
    {
        $user = $this->userFactory();
        $token = Auth::tokenById($user->id);

        $this->get(
            "/user/categories?token={$token}",
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(1, $body['data']);

        foreach ($user->categories as $category) {
            $this->seeJson([
                'id' => $category->uuid,
                'name' => $category->name,
            ]);
        }
    }

    public function test_user_categories_show_optionally_includes_links_with_1_category()
    {
        $user = $this->userFactory(1);
        $token = Auth::tokenById($user->id);

        $categories = $user->categories()->get();

        $this->get(
            "/user/categories?token={$token}&include=links",
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);

        // has data?
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertCount(1, $data);

        // See category data
        $category = $data[0];
        $this->assertEquals($user->categories->first()->uuid, $category['id']);
        $this->assertEquals($user->categories->first()->name, $category['name']);
        $this->assertArrayHasKey('links', $category);
        $this->assertArrayHasKey('data', $category['links']);

        // See links data
        foreach ($user->categories->first()->links as $userLink) {
            $this->seeJson([
                'id' => $userLink->uuid,
                'title' => $userLink->title,
                'url' => $userLink->url,
            ]);
        }
    }

    public function test_user_categories_show_optionally_includes_links_with_2_category()
    {
        $user = $this->userFactory(2);
        $token = Auth::tokenById($user->id);

        $this->get(
            "/user/categories?token={$token}&include=links",
            ['Accept' => 'application/json']
        );
        
        $this->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);

        // has data?
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertCount(2, $data);

        // See first category data
        $category = $data[0];
        $this->assertEquals($user->categories->first()->uuid, $category['id']);
        $this->assertEquals($user->categories->first()->name, $category['name']);
        $this->assertArrayHasKey('links', $category);
        $this->assertArrayHasKey('data', $category['links']);

        // See second category data
        $category = $data[1];
        $this->assertEquals($user->categories->get(1)->uuid, $category['id']);
        $this->assertEquals($user->categories->get(1)->name, $category['name']);
        $this->assertArrayHasKey('links', $category);
        $this->assertArrayHasKey('data', $category['links']);

        // See links data
        foreach ($user->categories->first()->links as $userLink) {
            $this->seeJson([
                'id' => $userLink->uuid,
                'title' => $userLink->title,
                'url' => $userLink->url,
            ]);
        }
        foreach ($user->categories->get(1)->links as $userLink) {
            $this->seeJson([
                'id' => $userLink->uuid,
                'title' => $userLink->title,
                'url' => $userLink->url,
            ]);
        }
    }
}
