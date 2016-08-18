<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class FactoryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_factory_links_created_are_in_database()
    {
        $links = factory(\App\Link::class, 5)->create();

        foreach ($links as $link) {
            $this
                ->seeInDatabase('links', [
                    'id' => $link->id,
                    'uuid' => $link->uuid,
                    'title' => $link->title,
                    'url' => $link->url,
                    'description' => $link->description,
                    'created_at' => $link->created_at,
                    'updated_at' => $link->updated_at,
                ]);
        }
    }

    /** @test **/
    public function test_factory_categories_created_are_in_database()
    {
        $categories = factory(\App\Category::class, 5)->create();

        foreach ($categories as $category) {
            $this
                ->seeInDatabase('categories', [
                    'id' => $category->id,
                    'uuid' => $category->uuid,
                    'name' => $category->name,
                    'description' => $category->description,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ]);
        }
    }

    public function test_factory_users_created_are_in_database()
    {
        $users = factory(\App\User::class, 5)->create();

        foreach ($users as $user) {
            $this
                ->seeInDatabase('users', [
                    'id' => $user->id,
                    'email' => $user->email,
                    'password' => $user->password,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
        }
    }
}
