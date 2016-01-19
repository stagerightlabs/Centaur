<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Centaur\Tests\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

class LoginTest extends TestCase
{
    /** @test */
    public function a_user_can_login_via_http()
    {
        $this->visit('/login')
             ->type('admin@admin.com', 'email')
             ->type('password', 'password')
             ->press('Login')
             ->see('Hello, admin@admin.com!');

        // There should be no cookie set.
        $this->assertNull($this->app['sentinel.cookie']->get());
    }

    /** @test */
    public function a_user_can_login_via_http_and_be_rememebered()
    {
        $this->visit('/login')
             ->type('admin@admin.com', 'email')
             ->type('password', 'password')
             ->check('remember')
             ->press('Login')
             ->see('Hello, admin@admin.com!');

        $cookie = $this->app['sentinel.cookie']->get();

        $this->assertInstanceOf(Cookie::class, $cookie);
    }

    /** @test */
    public function a_user_cannot_login_with_the_wrong_password()
    {
        $this->visit('/login')
             ->type('admin@admin.com', 'email')
             ->type('wrong', 'password')
             ->press('Login')
             ->see('Access denied due to invalid credentials.');
    }

    /** @test */
    public function an_inactive_account_cannot_login()
    {
        Sentinel::register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);

        $this->visit('/login')
             ->type('andrei@prozorov.net', 'email')
             ->type('violin', 'password')
             ->press('Login')
             ->see('Your account has not been activated yet.');
    }

    /** @test */
    public function an_invalid_user_cannot_login()
    {
        $this->visit('/login')
             ->type('admin@invalid.com', 'email')
             ->type('password', 'password')
             ->press('Login')
             ->see('Access denied due to invalid credentials.');
    }

    /** @test */
    public function a_user_can_login_via_ajax()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'password'
        ], $headers)->seeJson([
             'message' => 'You have been authenticated.',
        ]);

        // There should be no cookie set.
        $this->assertNull($this->app['sentinel.cookie']->get());
    }

    /** @test */
    public function an_invalid_user_cannot_login_via_ajax()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        $this->post('/login', [
            'email' => 'invalid@invalid.com',
            'password' => 'password',
        ], $headers)->seeJson([
             'message' => 'Access denied due to invalid credentials.',
        ]);
    }
}
