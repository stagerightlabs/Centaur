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
    public function it_redirects_a_user_without_permissions_to_the_dashboard()
    {
        $this->signIn('user@user.com')
             ->visit(route('dashboard'))
             ->visit(route('users.create'))
             ->see('You do not have permission to do that.');
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
    public function it_redirects_authenticated_users_away_from_guest_only_pages()
    {
        $this->signIn('user@user.com')
             ->visit(route('dashboard'))
             ->visit(route('auth.login.form'))
             ->see('Hello, user@user.com!');
    }
}
