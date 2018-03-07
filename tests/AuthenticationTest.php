<?php

use App\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    private $testName = 'John Doe';
    private $testEmail = 'john@company.com';
    private $testPassword = 'reallysecure';

    public function test_registration()
    {
        $this->post('/auth/register', [
            'name' => $this->testName,
            'email' => $this->testEmail,
            'password' => $this->testPassword
        ]);

        $this->assertResponseOk();
    }

    public function test_login()
    {
        $this->createTestUser();

        $this->authenticate();

        $this->assertResponseOk();
    }

    public function test_protected_route_with_valid_token()
    {
        $this->createTestUser();

        $token = $this->authenticate();

        $this->get('articles', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseOk();
    }

    public function test_protected_route_with_invalid_token()
    {
        $this->get('articles', [
            'Authorization' => 'Bearer invalidtoken'
        ]);

        $this->assertResponseStatus(401);
    }

    private function createTestUser($email = null, $password = null, $name = null)
    {
        $user = new User;
        $user->name = $name ?: $this->testName;
        $user->email = $email ?: $this->testEmail;
        $user->password = Hash::make($password ?: $this->testPassword);
        $user->save();

        return $user->save() ? $user : false;
    }

    private function authenticate()
    {
        $this->post('/auth/login', [
            'email' => $this->testEmail,
            'password' => $this->testPassword
        ]);

        return array_get(json_decode($this->response->content(), true), 'token');
    }
}
