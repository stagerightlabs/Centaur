<?php

namespace Centaur\Tests\Integrated;

use Mail;
use Reminder;
use Sentinel;
use Centaur\Tests\TestCase;

class PasswordReminderTest extends TestCase
{
    /** @test */
    public function a_user_can_reset_via_http()
    {
        // Mock Expectations
        Mail::shouldReceive('queue')->once();

        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);

        // Attempt generating reminder email
        $this->visit('/password/reset')
             ->type('andrei@prozorov.net', 'email')
             ->press('Help!');

        $andrei = Sentinel::findByCredentials(['email' => 'andrei@prozorov.net']);
        $reminder = Reminder::exists($andrei);

        // Attempt changing password
        $this->visit('/password/reset/' . $reminder->code)
             ->type('natasha', 'password')
             ->type('natasha', 'password_confirmation')
             ->press('Save')
             ->see('Password reset successful.');
    }

    /** @test */
    public function an_invalid_reset_code_will_not_work_via_http()
    {
        // Mock Expectations
        Mail::shouldReceive('queue')->once();

        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);

        // Attempt generating reminder email
        $this->visit('/password/reset')
             ->type('andrei@prozorov.net', 'email')
             ->press('Help!');

        // Attempt changing password
        $this->visit('/password/reset/invalid_reset_code')
             ->see('Invalid or expired password reset code;');
    }

    /** @test */
    public function a_user_can_reset_via_ajax()
    {
        // Mock Expectations
        Mail::shouldReceive('queue')->once();

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);

        // Attempt generating reminder email
        $this->post('/password/reset', [
            'email' => 'andrei@prozorov.net'
        ], $headers)
             ->seeJson(["message" => "Instructions for changing your password will be sent to your email address if it is associated with a valid account."]);

        $andrei = Sentinel::findByCredentials(['email' => 'andrei@prozorov.net']);
        $reminder = Reminder::exists($andrei);

        // Attempt changing password
        $this->post('/password/reset/' . $reminder->code, [
            'password' => 'natasha',
            'password_confirmation' => 'natasha'
        ], $headers)
             ->seeJson(["message" => "Password reset successful."]);
    }

    /** @test */
    public function an_invalid_reset_code_will_not_work_via_ajax()
    {
        // Mock Expectations
        Mail::shouldReceive('queue')->once();

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Prepare new account
        $user = app()->make('sentinel')->register(['email' => 'andrei@prozorov.net', 'password' => 'violin'], true);

        // Attempt generating reminder email
        $this->post('/password/reset', [
            'email' => 'andrei@prozorov.net'
        ], $headers)
             ->seeJson(["message" => "Instructions for changing your password will be sent to your email address if it is associated with a valid account."]);

        // Attempt changing password
        $this->post('/password/reset/invalid_reset_code', [
            'password' => 'natasha',
            'password_confirmation' => 'natasha'
        ], $headers)
             ->seeJson(["message" => "Invalid or expired password reset code; please request a new link."]);
    }

}
