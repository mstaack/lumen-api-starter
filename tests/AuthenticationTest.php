<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $token = $this->createTestUser($verified = false)->verification_token;

        $this->seeInDatabase('users', ['verification_token' => $token]);

        $this->get("auth/verify/$token")->assertResponseOk();

        $this->notSeeInDatabase('users', ['verification_token' => $token]);
    }

    public function test_password_forgotten_process()
    {
        $user = $this->createTestUser($verified = true);
        $newPassword = Str::random(8);

        //request
        $this->post('auth/password/forgot', ['email' => $user->email])->assertResponseOk();

        //get token "from" mail
        $token = DB::table('password_resets')->where('email', $user->email)->first()->token;

        //change
        $this->post("auth/password/recover/$token", ['password' => $newPassword])->assertResponseOk();

        $this->assertNotFalse(Auth::attempt(['email' => $user->email, 'password' => $newPassword]));
    }

    public function test_validation_register()
    {
        $this->post('/auth/register');

        $this->assertValidationFailedResponse();
    }
}
