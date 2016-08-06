<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUsersTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->app->instance('middleware.disable', true);
    }

    public function test_get_users()
    {
        $users = factory(\App\User::class, 5)->create();

        $this
            ->get('/users', ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        foreach ($users as $user) {
            $this->seeJson([
                'id' => $user->id,
                'email' => $user->email,
                'created_at' => $user->created_at->toDateTimeString(),
                'updated_at' => $user->updated_at->toDateTimeString(),
            ]);
        }
    }
}
