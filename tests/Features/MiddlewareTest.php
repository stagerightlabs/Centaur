<?php

namespace Centaur\Tests\Features;

use Centaur\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function it_redirects_a_non_authenticated_user()
    {
        $response = $this->get(route('users.show', 1));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_blocks_a_non_authenticated_api_request()
    {
        // Arrange
        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
        ];

        // Act
        $response = $this->get(route('users.show', 1), $headers);

        // Assert
        $response->assertJsonFragment(['error' => 'Unauthorized']);
    }

    /** @test */
    public function it_redirects_a_user_without_permissions_to_the_dashboard()
    {
        $this->withoutExceptionHandling();

        // Arrange
        $this->signIn('user@user.com');

        // Act
        $response = $this->get(route('users.create'));

        // Assert
        $response->assertSessionHas('error', 'You do not have permission to do that.');
    }

    /** @test */
    public function it_prevents_api_access_to_a_user_without_permissions()
    {
        // Arrange
        // This user account already exists in the stubbed sqlite file
        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
        ];
        $this->signIn('user@user.com');

        // Act
        $response = $this->json('GET', route('users.create'), [], $headers);

        // Assert
        $response->assertJsonFragment(['error' => 'Unauthorized']);
    }

    /** @test */
    public function it_redirects_a_user_who_does_not_have_an_appropriate_role()
    {
        // Arrange
        $this->signIn('user@user.com');

        // Act
        $response = $this->get(route('roles.create'));

        // Assert
        $response->assertSessionHas('error', 'You do not have permission to do that.');
    }

    /** @test */
    public function it_prevents_api_access_to_a_user_with_an_improper_role()
    {
        // Arrange
        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
        ];
        $this->signIn('user@user.com');

        // Act
        $response = $this->json('GET', route('roles.create'), [], $headers);

        // Assert
        $response->assertJsonFragment(['error' => 'Unauthorized']);
    }

    /** @test */
    public function it_redirects_authenticated_users_away_from_guest_only_pages()
    {
        // Arrange
        $this->signIn('user@user.com');

        // Act
        $response = $this->get(route('auth.login.form'));

        // Assert
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function it_prevents_api_access_for_authenticated_users_to_guest_endpoints()
    {
        // Arrange
        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
        ];
        $this->signIn('user@user.com');

        // Act
        $response = $this->json('GET', route('auth.login.form'), [], $headers);

        // Assert
        $response->assertJsonFragment(['error' => 'Unauthorized']);
    }
}
