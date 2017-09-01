<?php

namespace Centaur\Tests\Integrated;

use Mail;
use Centaur\Tests\TestCase;
use Centaur\Mail\CentaurWelcomeEmail;

class RegistrationTest extends TestCase
{
    /** @test */
    public function a_user_can_register_via_http()
    {
        // Arrange
        Mail::fake();

        // Act
        $response = $this->post('/register', [
            'email' => 'andrei@prozorov.net',
            'password' => 'violin',
            'password_confirmation' => 'violin'
        ]);

        // Assert
        $response->assertSessionHas('success', 'Registration complete.  Please check your email for activation instructions.');
        $this->assertDatabaseHas('users', ['email' => 'andrei@prozorov.net']);
        Mail::assertQueued(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function an_existing_user_cannot_register_via_http()
    {
        // Arrange
        // There is already a 'admin@admin.com' user in the stubbed sqlite file

        // Act
        $response = $this->post('/register', [
            'email' => 'admin@admin.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function a_user_can_register_via_ajax()
    {
        // Arrange
        Mail::fake();
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->post('/register', [
            'email' => 'andrei@prozorov.net',
            'password' => 'violin',
            'password_confirmation' => 'violin'
        ], $headers);

        // Assert
        $response->assertJsonFragment([
             'message' => 'Registration complete.  Please check your email for activation instructions.',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'andrei@prozorov.net']);
        Mail::assertQueued(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function a_user_cannot_register_again_via_ajax()
    {
        // Arrange
        // There is already an 'admin@admin.com' user in the stubbed sqlite file
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->post('/register', [
            'email' => 'admin@admin.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ], $headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonFragment(['email' => ["The email has already been taken."]]);
    }

}
