<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserCategoriesTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function test_show_should_return_a_collection_of_categories_associated_to_the_user()
    {
        $user = factory(\App\User::class)->create();

        $categories = factory(\App\Category::class, 2)->create();

        foreach ($categories as $category) {
            $category->user()->associate($user);
            $category->save();
        }

        $this
            ->get("/user/{$user->uuid}/categories", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(2, $body['data']);

        foreach ($categories as $category) {
            $this->seeJson([
                'id' => $category->uuid,
                'name' => $category->name,
            ]);
        }
    }

    public function test_show_optionally_includes_links_with_1_category()
    {
        $user = factory(\App\User::class)->create();
        
        $userLinks = $this->linkFactory(5);
        foreach ($userLinks as $userLink) {
            $userLink->category->user()->associate($user)->save();
        }

        $this
            ->get("/user/{$user->uuid}/categories?include=links", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);

        // has data?
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertCount(1, $data);

        // See category data
        $category = $data[0];
        $this->assertEquals($userLinks->first()->category->uuid, $category['id']);
        $this->assertEquals($userLinks->first()->category->name, $category['name']);
        $this->assertArrayHasKey('links', $category);
        $this->assertArrayHasKey('data', $category['links']);

        // See links data
        foreach ($userLinks as $userLink) {
            $this->seeJson([
                'id' => $userLink->uuid,
                'title' => $userLink->title,
                'url' => $userLink->url,
            ]);
        }
    }

    public function test_show_optionally_includes_links_with_2_category()
    {
        $user = factory(\App\User::class)->create();
        
        $userLinks1 = $this->linkFactory(5);
        foreach ($userLinks1 as $userLink) {
            $userLink->category->user()->associate($user)->save();
        }

        $userLinks2 = $this->linkFactory(5);
        foreach ($userLinks2 as $userLink) {
            $userLink->category->user()->associate($user)->save();
        }

        $this
            ->get("/user/{$user->uuid}/categories?include=links", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);

        // has data?
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertCount(2, $data);

        // See first category data
        $category = $data[0];
        $this->assertEquals($userLinks1->first()->category->uuid, $category['id']);
        $this->assertEquals($userLinks1->first()->category->name, $category['name']);
        $this->assertArrayHasKey('links', $category);
        $this->assertArrayHasKey('data', $category['links']);

        // See second category data
        $category = $data[1];
        $this->assertEquals($userLinks2->first()->category->uuid, $category['id']);
        $this->assertEquals($userLinks2->first()->category->name, $category['name']);
        $this->assertArrayHasKey('links', $category);
        $this->assertArrayHasKey('data', $category['links']);

        // See links data
        foreach ($userLinks1 as $userLink) {
            $this->seeJson([
                'id' => $userLink->uuid,
                'title' => $userLink->title,
                'url' => $userLink->url,
            ]);
        }
        foreach ($userLinks2 as $userLink) {
            $this->seeJson([
                'id' => $userLink->uuid,
                'title' => $userLink->title,
                'url' => $userLink->url,
            ]);
        }
    }
}
