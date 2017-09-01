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
        // Arrange
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);

        // Act
        $response = $this->get('/activate/' . $activation->getCode());

        // Assert
        $this->assertInstanceOf(EloquentActivation::class, $activation);
        $response->assertSessionHas('success', 'Registration complete.  You may now log in.');
    }

    /** @test */
    public function an_invalid_activation_code_will_not_work_via_http()
    {
        // Arrange
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);

        // Act
        $response = $this->get('/activate/incorrect_activation_code');

        // Assert
        $response->assertSessionHas('error', 'Invalid or expired activation code.');
    }

    /** @test */
    public function a_user_can_activate_via_ajax()
    {
        // Arrange
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->get('/activate/' . $activation->code, $headers);

        // Assert
        $response->assertJsonFragment(['message' => 'Registration complete.  You may now log in.']);
    }

    /** @test */
    public function an_invalid_activation_code_will_not_work_via_ajax()
    {
        // Arrange
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $activation = app()->make('sentinel.activations')->create($user);
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->get('/activate/incorrect_activation_code', $headers);

        // Assert
        $response->assertJsonFragment(['message' => 'Invalid or expired activation code.']);
    }

    /** @test */
    public function it_resends_an_activation_email_via_http()
    {
        // Arrange
        Mail::fake();
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);

        // Act
        $response = $this->post('/resend', [
            'email' => 'andrei@prozorov.net'
        ]);

        // Assert
        $response->assertSessionHas('success', 'New instructions will be sent to that email address if it is associated with a inactive account.');
        Mail::assertQueued(CentaurWelcomeEmail::class, function ($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function it_resends_an_activation_email_via_ajax()
    {
        // Arrange
        Mail::fake();
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin']);
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->post('/resend', ['email' => 'andrei@prozorov.net'], $headers);

        // Assert
        $response->assertJsonFragment(['message' => 'New instructions will be sent to that email address if it is associated with a inactive account.']);
        Mail::assertQueued(CentaurWelcomeEmail::class, function ($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

}
