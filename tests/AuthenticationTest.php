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

        $this->post('/auth/login', [
            'email' => $this->testEmail,
            'password' => $this->testPassword
        ]);

        $this->assertResponseOk();
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

    public function test_protected_route_with_authenticated_user()
    {
        $user = $this->createTestUser();

        $this->be($user);

        $this->get('articles')->assertResponseOk();
    }

    public function test_protected_route_without_authenticated_user()
    {
        $this->get('articles');

        $this->assertResponseStatus(401);
    }

    public function test_validation_login()
    {
        $this->post('/auth/login');

        $this->assertResponseStatus(422);
    }

    public function test_validation_register()
    {
        $this->post('/auth/register');

        $this->assertResponseStatus(422);
    }
}
