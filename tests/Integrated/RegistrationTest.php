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
        // Mock Expectations
        Mail::fake();

        // Attempt registration
        $this->visit('/register')
             ->type('andrei@prozorov.net', 'email')
             ->type('violin', 'password')
             ->type('violin', 'password_confirmation')
             ->press('Sign Me Up!')
             ->see("Registration complete.");

        // Assert mail was sent
        Mail::assertSent(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function an_existing_user_cannot_register_via_http()
    {
        // For now, we don't want the validation exception to reach phpunit
        $this->enableExceptionHandler();

        // Attempt registration (the admin user has already been registered)
        $this->visit('/register')
             ->type('admin@admin.com', 'email')
             ->type('password', 'password')
             ->type('password', 'password_confirmation')
             ->press('Sign Me Up!')
             ->see("The email has already been taken.");
    }

    /** @test */
    public function a_user_can_register_via_ajax()
    {
        // Mock Expectations
        Mail::fake();

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Attempt Registration
        $this->post('/register', [
            'email' => 'andrei@prozorov.net',
            'password' => 'violin',
            'password_confirmation' => 'violin'
        ], $headers)->seeJson([
             'message' => 'Registration complete.  Please check your email for activation instructions.',
        ]);

        // Verify
        $this->seeInDatabase('users', ['email' => 'andrei@prozorov.net']);

        Mail::assertSent(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });

    }

    /** @test */
    public function a_user_cannot_register_again_via_ajax()
    {
        // For now, we don't want the validation exception to reach phpunit
        $this->enableExceptionHandler();

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Attempt Registration (the admin user has already been registered)
        $this->post('/register', [
            'email' => 'admin@admin.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ], $headers)->seeJson([
             "email" => ["The email has already been taken."]
        ]);
    }

}
