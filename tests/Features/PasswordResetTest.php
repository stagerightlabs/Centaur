<?php

namespace Centaur\Tests\Integrated;

use Mail;
use Reminder;
use Sentinel;
use Centaur\Tests\TestCase;
use Centaur\Mail\CentaurPasswordReset;

class PasswordReminderTest extends TestCase
{
    /** @test */
    public function a_user_can_reset_via_http()
    {
        // Arrange
        Mail::fake();
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);
        $this->post('/password/reset', ['email' => 'andrei@prozorov.net']);
        $andrei = Sentinel::findByCredentials(['email' => 'andrei@prozorov.net']);
        $reminder = Reminder::exists($andrei);

        // Act
        $response = $this->post('/password/reset/' . $reminder->code, [
            'password' => 'natasha',
            'password_confirmation' => 'natasha'
        ]);

        // Assert
        $response->assertSessionHas('success', 'Password reset successful.');
        Mail::assertQueued(CentaurPasswordReset::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function an_invalid_reset_code_will_not_work_via_http()
    {
        // Arrange
        Mail::fake();
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);
        $this->post('/password/reset', ['email' => 'andrei@prozorov.net']);

        // Act
        $response = $this->get('/password/reset/invalid_reset_code');

        // Assert
        $response->assertSessionHas('error', 'Invalid or expired password reset code; please request a new link.');
        Mail::assertQueued(CentaurPasswordReset::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function a_user_can_reset_via_ajax()
    {
        // Arrange
        Mail::fake();
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];
        $andrei = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);

        // Act
        $responseA = $this->post('/password/reset', ['email' => 'andrei@prozorov.net'], $headers);
        $reminder = Reminder::exists($andrei);
        $responseB = $this->post('/password/reset/' . $reminder->code, [
            'password' => 'natasha',
            'password_confirmation' => 'natasha'
        ], $headers);

        // Assert
        $responseA->assertJsonFragment(["message" => "Instructions for changing your password will be sent to your email address if it is associated with a valid account."]);
        $responseB->assertJsonFragment(["message" => "Password reset successful."]);
    }

    /** @test */
    public function an_invalid_reset_code_will_not_work_via_ajax()
    {
        // Arrange
        Mail::fake();
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);

        // Act
        $response = $this->post('/password/reset/invalid_reset_code', [
            'password' => 'natasha',
            'password_confirmation' => 'natasha'
        ], $headers);

        // Assert
        $response->assertJsonFragment(["message" => "Invalid or expired password reset code; please request a new link."]);
    }

}
