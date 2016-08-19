<?php

use Carbon\Carbon;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateUserTest extends TestCase
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

    public function test_create_user()
    {
        $this
            ->post('/users', [
                'email' => 'user@user.com',
                'password' => 'password',
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeHeaderWithRegExp('Location', '#/users/[\d]+$#');
        
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);

        $data = $body['data'];
        $this->assertTrue($data['id'] > 0, 'Expected a positive integer, but did not see one');
        $this->assertEquals('user@user.com', $data['email']);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['created_at']);
        $this->assertEquals(Carbon::now()->toDateTimeString(), $data['updated_at']);

        $this->seeInDatabase('users', ['email' => "user@user.com"]);
    }

    public function test_it_validates_required_fields_when_creating_a_new_user()
    {
        $this
            ->post('/users', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('email', $body);
        $this->assertArrayHasKey('password', $body);

        $this->assertEquals(['The email field is required.'], $body['email']);
        $this->assertEquals(['The password field is required.'], $body['password']);
    }

    public function test_create_fails_pass_validation_when_email_is_not_valid()
    {
        $user = factory(\App\User::class)->make();
        $user->email = str_repeat('a', 10);

        $this
            ->post('/users', [
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

        $user = factory(\App\User::class)->make([
            'email' => $oldUser->email
        ]);

        $this
            ->post('/users', [
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
        $user = factory(\App\User::class)->make();
        $user->password = str_repeat('a', 256);

        $this
            ->post('/users', [
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
        $user = factory(\App\User::class)->make();
        $user->password = str_repeat('a', 255);

        $this
            ->post('/users', [
                'email' => $user->email,
                'password' => $user->password,
            ], ['Accept' => 'application/json']);

        $this
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeInDatabase('users', ['email' => $user->email]);
    }
}
