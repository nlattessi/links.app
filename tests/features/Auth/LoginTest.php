<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_user_can_login_and_get_a_token()
    {
        $password = app('hash')->make('password');

        $user = factory(\App\User::class)->create([
            'password' => $password,
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $this
            ->post('/auth/login', $loginData, ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_OK);

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('token', $data);
    }

    public function test_a_user_cant_login_with_invalid_credentials()
    {
        $user = factory(\App\User::class)->create();

        $loginData = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $this
            ->post('/auth/login', $loginData, ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_NOT_FOUND);

        $this->seeJson([
            'error' => [
                'message' => 'User not found.',
                'status' => Response::HTTP_NOT_FOUND,
            ],
        ]);
    }

    public function test_a_user_cant_login_without_required_fields()
    {
        $this
            ->post('/auth/login', [], ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['email', 'password'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    public function test_a_user_cant_login_if_email_not_exists()
    {
        $loginData = [
            'email' => 'user@email.com',
            'password' => 'password',
        ];

        $this
            ->post('/auth/login', $loginData, ['Accept' => 'application/json'])
            ->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['email'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The selected {$field} is invalid."], $data[$field]);
        }
    }
}
