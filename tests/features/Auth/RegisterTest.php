<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_user_can_register()
    {
        $registerData = [
            'email' => 'user@email.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post(
            '/auth/register',
            $registerData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_CREATED);

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('token', $data);

        $this->seeInDatabase('users', ['email' => 'user@email.com']);
    }

    public function test_a_user_cant_register_without_required_fields()
    {
        $this->post(
            '/auth/register',
            [],
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);

        $fields = ['email', 'password'];

        foreach ($fields as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(["The {$field} field is required."], $data[$field]);
        }
    }

    public function test_a_user_cant_register_if_password_confirmation_not_equal_password()
    {
        $registerData = [
            'email' => 'user@email.com',
            'password' => 'password',
            'password_confirmation' => 'password2',
        ];

        $this->post(
            '/auth/register',
            $registerData,
            ['Accept' => 'application/json']
        );

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $this->response->getData(true);
        $this->assertArrayHasKey('password', $data);
        $this->assertEquals(["The password confirmation does not match."], $data['password']);

        $this->notSeeInDatabase('users', ['email' => 'user@email.com']);
    }
}
