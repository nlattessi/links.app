<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteUserTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->app->instance('middleware.disable', true);
    }

    public function test_delete_user()
    {
        $user = factory(\App\User::class)->create();

        $this
            ->seeInDatabase('users', [
                'id' => $user->id,
                'email' => $user->email,
                'password' => $user->password,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

        $this
            ->delete("/users/{$user->id}", [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NO_CONTENT)
            ->isEmpty();

        $this->notSeeInDatabase('users', ['id' => $user->id]);
    }

    public function test_should_fail_if_id_not_exist()
    {
        $this
            ->delete('users/999', [], ['Accept' => 'application/json'])
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
        $this->delete('/users/invalid-route', [], ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/User not found/',
                $this->response->getContent(),
                'UsersController@update route matching when it should not.'
            );
    }
}
