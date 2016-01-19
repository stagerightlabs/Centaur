<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Centaur\AuthManager;
use Centaur\Tests\TestCase;
use Centaur\Replies\FailureReply;
use Centaur\Replies\SuccessReply;
use Centaur\Replies\ExceptionReply;
use Cartalyst\Sentinel\Users\UserInterface;
use Cartalyst\Sentinel\Activations\EloquentActivation;

class AuthManagerTest extends TestCase
{
    /** @var Centaur\AuthManager */
    protected $authManager;

    public function setUp()
    {
        parent::setUp();
        $this->authManager = $this->app->make(AuthManager::class);
    }

    /** @test */
    public function it_handles_authentication()
    {
        // Given
        $credentials = ['email' => 'admin@admin.com', 'password' => 'password'];
        $remember = false;

        // Attempt the Login
        $result = $this->authManager->authenticate($credentials, $remember);

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
        $this->assertNull($this->app['sentinel.cookie']->get());
    }

    /** @test */
    public function it_handles_authentication_with_remember_cookie()
    {
        // Given
        $credentials = ['email' => 'admin@admin.com', 'password' => 'password'];
        $remember = true;

        // Attempt the Login
        $result = $this->authManager->authenticate($credentials, $remember);
        $cookie = $this->app['sentinel.cookie']->get();

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Cookie::class, $cookie);
    }

    /** @test */
    public function it_prevents_invalid_credentials_from_authenticating()
    {
        // Given
        $credentials = ['email' => 'admin@admin.com', 'password' => 'wrong'];
        $remember = false;

        // Attempt the Login
        $result = $this->authManager->authenticate($credentials, $remember);

        // Verify
        $this->assertInstanceOf(FailureReply::class, $result);
        $this->assertEquals("Access denied due to invalid credentials.", $result->message);
    }

    /** @test */
    public function it_prevents_non_activated_users_from_authenticating()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'violin'];
        Sentinel::register($credentials);
        $remember = false;

        // Attempt the Login
        $result = $this->authManager->authenticate($credentials, $remember);

        // Verify
        $this->assertInstanceOf(ExceptionReply::class, $result);
        $this->assertEquals(
            "Your account has not been activated yet.",
            $result->message
        );
    }

    /** @test */
    public function it_handles_a_logout_request()
    {
        // Given
        $credentials = ['email' => 'admin@admin.com', 'password' => 'password'];
        $result = $this->authManager->authenticate($credentials);

        // Attempt the Logout
        $result = $this->authManager->logout();

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
    }

    /** @test */
    public function it_handles_registration()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'password'];

        // Attempt the Registration
        $result = $this->authManager->register($credentials);
        $activation = $result->activation;
        $activated = $this->app['sentinel.activations']->completed($result->user);

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
        $this->assertInstanceOf(UserInterface::class, $result->user);
        $this->assertInstanceOf(EloquentActivation::class, $activation);
        $this->assertFalse($activated);
    }

    /** @test */
    public function it_handles_registration_and_automatic_activation()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'password'];

        // Attempt the Registration
        $result = $this->authManager->register($credentials, true);
        $activated = $this->app['sentinel.activations']->completed($result->user);

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
        $this->assertInstanceOf(UserInterface::class, $result->user);
        $this->assertInstanceOf(EloquentActivation::class, $activated);
    }

    /** @test */
    public function it_does_not_register_a_duplicate_user()
    {
        // Given
        $credentials = ['email' => 'user@user.com', 'password' => 'password'];

        // Attempt the Registration
        $result = $this->authManager->register($credentials);

        // Verify
        $this->assertInstanceOf(ExceptionReply::class, $result);
        $this->assertEquals("Invalid credentials provided", $result->message);
    }

    /** @test */
    public function it_activates_a_user()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'password'];
        $result = $this->authManager->register($credentials);

        // Attempt the Activation
        $result = $this->authManager->activate($result->activation->getCode());

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
    }

    /** @test */
    public function it_handles_an_incorrect_activation_code()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'violin'];
        $result = $this->authManager->register($credentials);

        // Attempt the Activation
        $result = $this->authManager->activate('incorrect_activation_code');

        // Verify
        $this->assertInstanceOf(ExceptionReply::class, $result);
        $this->assertEquals("Invalid or expired activation code.", $result->message);
    }

    /** @test */
    public function it_resets_passwords_with_a_valid_reset_code()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'password'];
        $result = $this->authManager->register($credentials);
        $reminder = $this->app['sentinel.reminders']->create($result->user);
        $newPassword = 'natasha';

        // Attempt the Reset
        $result = $this->authManager->resetPassword($reminder->code, $newPassword);

        // Verify
        $this->assertInstanceOf(SuccessReply::class, $result);
    }

    /** @test */
    public function it_does_not_resets_passwords_with_an_invalid_reset_code()
    {
        // Given
        $credentials = ['email' => 'andrei@prozorov.net', 'password' => 'password'];
        $result = $this->authManager->register($credentials);
        $reminder = $this->app['sentinel.reminders']->create($result->user);
        $newPassword = 'natasha';

        // Attempt the Reset
        $result = $this->authManager->resetPassword('incorrect_reset_code', $newPassword);

        // Verify
        $this->assertInstanceOf(ExceptionReply::class, $result);
        $this->assertEquals("Invalid or expired password reset code; please request a new link.", $result->message);
    }
}
