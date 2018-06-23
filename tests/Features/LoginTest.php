<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Centaur\Tests\TestCase;
use Illuminate\Foundation\Auth\User;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Cookies\IlluminateCookie;
use Cartalyst\Sentinel\Persistences\EloquentPersistence;

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
    public function a_user_can_login_via_http_and_be_remembered()
    {
        // Arrange
        $user = User::where('email', 'admin@admin.com')->first();

        // Act
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'true',
        ]);

        // Assert
        $persistence = EloquentPersistence::where('user_id', $user->id)->first();
        $this->assertInstanceOf(EloquentUser::class, Sentinel::check('admin@admin.com'));
        $this->assertInstanceOf(IlluminateCookie::class, $this->app['sentinel.cookie']);
        $this->assertEquals($persistence->code, $this->app['sentinel.cookie']->get());
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function a_user_cannot_login_with_the_wrong_password()
    {
        // Arrange
        // There is already a user account in the stubbed sqlite file

        // Act
        $response = $this->from(route('auth.login.form'))->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'wrong_password'
        ]);

        // Assert
        $response->assertRedirect(route('auth.login.form'));
        $response->assertSessionHas('error', 'Access denied due to invalid credentials.');
        $response->assertSessionHas('_old_input', [
            'email' => 'admin@admin.com',
            'password' => 'wrong_password'
        ]);
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
            'Accept' => 'application/json',
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
            'Accept' => 'application/json',
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
