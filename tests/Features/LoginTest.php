<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Centaur\Tests\TestCase;
use Cartalyst\Sentinel\Users\EloquentUser;
use Symfony\Component\HttpFoundation\Cookie;

class LoginTest extends TestCase
{
    /** @test */
    public function a_user_can_login_via_http()
    {
        // Arrange
        // There is already a user account in the stubbed sqlite file

        // Act
        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'password'
        ]);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertInstanceOf(EloquentUser::class, Sentinel::check('admin@admin.com'));
        $this->assertNull($this->app['sentinel.cookie']->get());
    }

    /** @test */
    public function a_user_can_login_via_http_and_be_rememebered()
    {
        // Arrange
        // There is already a user account in the stubbed sqlite file

        // Act
        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'password',
            'remember' => 'true',
        ]);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertInstanceOf(EloquentUser::class, Sentinel::check('admin@admin.com'));
        $this->assertInstanceOf(Cookie::class, $this->app['sentinel.cookie']->get());
    }

    /** @test */
    public function a_user_cannot_login_with_the_wrong_password()
    {
        // Arrange
        // There is already a user account in the stubbed sqlite file

        // Act
        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'wrong_password'
        ]);

        // Assert
        $response->assertSessionHas('error', 'Access denied due to invalid credentials.');
    }

    /** @test */
    public function an_inactive_account_cannot_login()
    {
        // Arrange
        Sentinel::register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);

        // Act
        $response = $this->post('/login', [
            'email' => 'andrei@prozorov.net',
            'password' => 'violin'
        ]);

        // Assert
        $response->assertSessionHas('error', 'Your account has not been activated yet.');
    }

    /** @test */
    public function an_invalid_user_cannot_login()
    {
        // Act
        $response = $this->post('/login', [
            'email' => 'admin@invalid.com',
            'password' => 'password'
        ]);

        // Assert
        $response->assertSessionHas('error', 'Access denied due to invalid credentials.');
    }

    /** @test */
    public function a_user_can_login_via_ajax()
    {
        // Arrange
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'password'
        ], $headers);

        // Assert
        $response->assertJsonFragment(['message' => 'You have been authenticated.']);
        $this->assertNull($this->app['sentinel.cookie']->get());
    }

    /** @test */
    public function an_invalid_user_cannot_login_via_ajax()
    {
        // Arrange
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response =     $this->post('/login', [
            'email' => 'invalid@invalid.com',
            'password' => 'password',
        ], $headers);

        // Assert
        $response->assertJsonFragment(['message' => 'Access denied due to invalid credentials.']);
    }
}
