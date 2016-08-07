<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class GetUserTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->app->instance('middleware.disable', true);
    }

    public function test_get_user()
    {
        $user = factory(\App\User::class)->create();

        $this
            ->get("/users/{$user->id}", ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertEquals($user->created_at->toDateTimeString(), $data['created_at']);
        $this->assertEquals($user->updated_at->toDateTimeString(), $data['updated_at']);
    }

    public function test_should_fail_if_user_id_not_exist()
    {
        $this
            ->get('users/999', ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND)
            ->seeJson([
                'error' => [
                    'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                    'status' => Response::HTTP_NOT_FOUND,
                ],
            ]);
    }

    public function test_should_not_match_an_invalid_route()
    {
        $this->get('/users/invalid-route', ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/User not found/',
                $this->response->getContent(),
                'UsersController@show route matching when it should not.'
            );
    }
}
