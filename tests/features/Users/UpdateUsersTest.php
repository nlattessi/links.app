<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateUserTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now('UTC'));
        $this->app->instance('middleware.disable', true);
    }

    public function tearDown()
    {
        parent::tearDown();

        Carbon::setTestNow();
    }

    public function test_update_user()
    {
        $user = factory(\App\User::class)->create();

        $this->notSeeInDatabase('users', [
            'email' => 'user2@email.com',
        ]);

        $this
            ->put("/users/{$user->id}", [
                'email' => 'user2@email.com',
                'password' => 'password2',
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJson([
                'id' => $user->id,
                'email' => 'user2@email.com',
            ])
            ->seeInDatabase('users', [
                'email' => 'user2@email.com',
            ]);

        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertArrayHasKey('created_at', $data);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['created_at']);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['updated_at']);

        $this->notSeeInDatabase('users', ['email' => $user->email]);
    }

    public function test_should_fail_if_id_not_exist()
    {
        $this
            ->put('users/999', [
                'email' => 'user@email.com',
                'password' => 'password',
            ], ['Accept' => 'application/json'])
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
        $this->put('/users/invalid-route', [], ['Accept' => 'application/json']);

        $this
            ->assertNotRegExp(
                '/User not found/',
                $this->response->getContent(),
                'UsersController@update route matching when it should not.'
            );
    }

    public function test_it_validates_required_fields_when_updating_a_user()
    {
        $user = factory(\App\User::class)->create();

        $this->put("/users/{$user->id}", [], ['Accept' => 'application/json']);
        
        $this->assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->response->getStatusCode()
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('email', $body);
        $this->assertArrayHasKey('password', $body);

        $this->assertEquals(['The email field is required.'], $body['email']);
        $this->assertEquals(['The password field is required.'], $body['password']);
    }

    public function test_create_fails_pass_validation_when_email_is_not_valid()
    {
        $user = factory(\App\User::class)->create();
        $user->email = str_repeat('a', 10);

        $this
            ->put("/users/{$user->id}", [
                'email' => $user->email,
                'password' => $user->password,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'email' => ['The email must be valid.'],
            ])
            ->notSeeInDatabase('users', ['email' => $user->email]);
    }

    public function test_create_fails_pass_validation_when_email_is_not_unique()
    {
        $oldUser = factory(\App\User::class)->create();
        
        $user = factory(\App\User::class)->create();
        $user->email = $oldUser->email;

        $this
            ->put("/users/{$user->id}", [
                'email' => $user->email,
                'password' => $user->password,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'email' => ['This email is already registered.'],
            ]);
    }

    public function test_create_fails_pass_validation_when_password_is_too_long()
    {
        $user = factory(\App\User::class)->create();
        $user->email = 'email2@email.com';
        $user->password = str_repeat('a', 256);

        $this
            ->put("/users/{$user->id}", [
                'email' => $user->email,
                'password' => $user->password,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->seeJson([
                'password' => ['The password may not be greater than 255 characters.'],
            ])
            ->notSeeInDatabase('users', ['email' => $user->email]);
    }

    public function test_create_passes_validation_when_password_is_exactly_max()
    {
        $user = factory(\App\User::class)->create();
        $user->email = 'email2@email.com';
        $user->password = str_repeat('a', 255);

        $this
            ->put("/users/{$user->id}", [
                'email' => $user->email,
                'password' => $user->password,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_OK)
            ->seeInDatabase('users', ['email' => $user->email]);
    }
}
