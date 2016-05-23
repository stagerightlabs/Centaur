<?php

namespace Centaur\Tests\Integrated;

use Centaur\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function it_redirects_a_non_authenticated_user()
    {
        $this->visit(route('users.show', 1))
            ->see('Login');
    }

    /** @test */
    public function it_blocks_a_non_authenticated_api_request()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Test the endpoint
        $this->json('GET', route('users.show', 1), [], $headers)
             ->seeJson([
                 'error' => 'Unauthorized',
             ]);
    }

    /** @test */
    public function it_redirects_a_user_without_permissions_to_the_dashboard()
    {
        $this->signIn('user@user.com')
             ->visit(route('dashboard'))
             ->visit(route('users.create'))
             ->see('You do not have permission to do that.');
    }

    /** @test */
    public function it_prevents_api_access_to_a_user_without_permissions()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Test the endpoint
        $this->signIn('user@user.com');
        $this->json('GET', route('users.create'), [], $headers)
             ->seeJson([
                 'error' => 'Unauthorized',
             ]);
    }

    /** @test */
    public function it_redirects_a_user_who_does_not_have_an_appropriate_role()
    {
        $this->signIn('user@user.com')
             ->visit(route('dashboard'))
             ->visit(route('roles.create'))
             ->see('You do not have permission to do that.');
    }

    /** @test */
    public function it_prevents_api_access_to_a_user_with_an_improper_role()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Test the endpoint
        $this->signIn('user@user.com');
        $this->json('GET', route('roles.create'), [], $headers)
             ->seeJson([
                 'error' => 'Unauthorized',
             ]);
    }

    /** @test */
    public function it_redirects_authenticated_users_away_from_guest_only_pages()
    {
        $this->signIn('user@user.com')
             ->visit(route('dashboard'))
             ->visit(route('auth.login.form'))
             ->see('Hello, user@user.com!');
    }

    /** @test */
    public function it_prevents_api_access_for_authenticated_users_for_guest_endpoints()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Test the endpoint
        $this->signIn('user@user.com');
        $this->json('GET', route('auth.login.form'), [], $headers)
             ->seeJson([
                 'error' => 'Unauthorized',
             ]);
    }
}
