<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration()
    {
        $this->post('/auth/register', [
            'name' => $this->testName,
            'email' => $this->testEmail,
            'password' => $this->testPassword
        ]);

        $this->assertResponseOk();
    }

    public function test_login_fails_without_activation()
    {
        $this->createTestUser();

        $this->post('/auth/login', [
            'email' => $this->testEmail,
            'password' => $this->testPassword
        ]);

        $this->assertUnauthorized();
    }

    public function test_login_with_activation()
    {
        $this->createTestUser($verified = true);

        $this->post('/auth/login', [
            'email' => $this->testEmail,
            'password' => $this->testPassword
        ]);

        $this->assertResponseOk();
    }

    public function test_protected_route_with_authenticated_user()
    {
        $user = $this->createTestUser($verified = true);

        $this->be($user);

        $this->get('auth/user')->assertResponseOk();
    }

    public function test_protected_route_without_authenticated_user()
    {
        $this->get('auth/user');

        $this->assertUnauthorized();
    }

    public function test_activation_process()
    {
        $user = $this->createTestUser($verified = false);

        $token = $user->verification_token;


        $this->seeInDatabase('users', ['verification_token' => $token]);

        $this->get("auth/verify/$token")->assertResponseOk();

        $this->notSeeInDatabase('users', ['verification_token' => $token]);
    }

    public function test_validation_login()
    {
        $this->post('/auth/login');

        $this->assertValidationFailedResponse();
    }

    public function test_validation_register()
    {
        $this->post('/auth/register');

        $this->assertValidationFailedResponse();
    }
}
