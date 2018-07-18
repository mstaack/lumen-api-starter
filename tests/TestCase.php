<?php

use App\User;

/**
 * Class TestCase
 */
abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * @var string
     */
    protected $testName = 'John Doe';

    /**
     * @var string
     */
    protected $testEmail = 'john@company.com';

    /**
     * @var string
     */
    protected $testPassword = 'reallysecure';

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /**
     * Create a test user
     *
     * @param bool $verified
     * @return bool|User
     */
    protected function createTestUser($verified = false)
    {
        $user = User::createFromValues($this->testName, $this->testEmail, $this->testPassword);

        if ($user && $verified) {
            $user->verify();
        }

        return $user ?: false;
    }

    /**
     * Check 422 status code
     */
    public function assertValidationFailedResponse()
    {
        $this->assertResponseStatus(422);
    }

    /**
     * Check 401 status code
     */
    protected function assertUnauthorized()
    {
        $this->assertResponseStatus(401);
    }
}
