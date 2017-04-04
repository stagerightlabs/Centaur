<?php

namespace Centaur\Tests\Integrated;

use Mail;
use Centaur\Tests\TestCase;
use Centaur\Mail\CentaurWelcomeEmail;
use Cartalyst\Sentinel\Activations\EloquentActivation;

class ActivationTest extends TestCase
{
    /** @test */
    public function a_user_can_activate_via_http()
    {
        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);

        // Attempt activation
        $this->assertInstanceOf(EloquentActivation::class, $activation);
        $this->visit('/activate/' . $activation->getCode())
             ->see('Registration complete.');
    }

    /** @test */
    public function an_invalid_activation_code_will_not_work_via_http()
    {
        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);

        // Attempt activation
        $this->visit('/activate/incorrect_activation_code')
             ->see('Invalid or expired activation code.');
    }

    /** @test */
    public function a_user_can_activate_via_ajax()
    {
        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Attempt Activations
        $this->get('/activate/' . $activation->code, $headers)
             ->seeJson(['message' => 'Registration complete.  You may now log in.']);
    }

    /** @test */
    public function an_invalid_activation_code_will_not_work_via_ajax()
    {
        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Attempt Activations
        $this->get('/activate/incorrect_activation_code', $headers)
             ->seeJson(['message' => 'Invalid or expired activation code.']);
    }

    /** @test */
    public function it_resends_an_activation_email_via_http()
    {
        // Mock Expectations
        Mail::fake();

        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);

        // Attempt activation
        $this->visit('/resend')
             ->type('andrei@prozorov.net', 'email')
             ->press('Send')
             ->see('New instructions will be sent to that email address if it is associated with a inactive account.');

        // Verify
        Mail::assertSent(CentaurWelcomeEmail::class, function ($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function it_resends_an_activation_email_via_ajax()
    {
        // Mock Expectations
        Mail::fake();

        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Attempt activation
        $this->post('/resend', ['email' => 'andrei@prozorov.net'], $headers)
             ->seeJson(['message' => 'New instructions will be sent to that email address if it is associated with a inactive account.']);

        // Verify
        Mail::assertSent(CentaurWelcomeEmail::class, function ($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

}
