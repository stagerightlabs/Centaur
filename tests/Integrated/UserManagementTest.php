<?php

namespace Centaur\Tests\Integrated;

use Mail;
use Sentinel;
use Centaur\Tests\TestCase;
use Centaur\Mail\CentaurWelcomeEmail;

class UserManagementTest extends TestCase
{
    /** @test */
    public function you_can_create_a_user_via_http()
    {
        // Mock Expectations
        Mail::fake();

        // Attempt user creation
        $this->signIn('admin@admin.com')
             ->visit('/users/create')
             ->type('Andrei', 'first_name')
             ->type('Prozorov', 'last_name')
             ->type('andrei@prozorov.net', 'email')
             ->check('roles[moderator]')
             ->type('password', 'password')
             ->type('password', 'password_confirmation')
             ->press('Create')
             ->see('User andrei@prozorov.net has been created.');

        $user = Sentinel::findUserByCredentials(['email' => 'andrei@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        // Verify
        $this->seeInDatabase('users', ['email' => 'andrei@prozorov.net']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));

        Mail::assertSent(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function you_can_create_a_user_via_ajax()
    {
        // Mock Expectations
        Mail::fake();

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Start a qualified user session
        $this->signIn('admin@admin.com');

        // Attempt user creation
        $this->post('/users', [
            'first_name' => 'Andrei',
            'last_name' => 'Prozorov',
            'email' => 'andrei@prozorov.net',
            'roles' => ['moderator' => 2],
            'password' => 'password',
            'password_confirmation' => 'password'
        ], $headers)->seeJson([
             'email' => 'andrei@prozorov.net',
        ]);

        $user = Sentinel::findUserByCredentials(['email' => 'andrei@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        // Verify
        $this->seeInDatabase('users', ['email' => 'andrei@prozorov.net']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));

        Mail::assertSent(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function you_can_update_a_user_via_http()
    {
        // Fetch a user object
        $admin = Sentinel::findUserByCredentials(['email' => 'admin@admin.com']);

        // Attempt user creation
        $this->signIn('admin@admin.com')
             ->visit('/users/' . $admin->id . '/edit')
             ->type('Olga', 'first_name')
             ->type('Prozorov', 'last_name')
             ->type('olga@prozorov.net', 'email')
             ->uncheck('roles[administrator]')
             ->check('roles[moderator]')
             ->press('Update')
             ->see('olga@prozorov.net has been updated.');

        $user = Sentinel::findUserByCredentials(['email' => 'olga@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        // Verify
        $this->seeInDatabase('users', ['email' => 'olga@prozorov.net']);
        $this->missingFromDatabase('users', ['email' => 'admin@admin.com']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));
    }

    /** @test */
    public function you_can_update_a_user_via_ajax()
    {
        // Fetch a user object
        $admin = Sentinel::findUserByCredentials(['email' => 'admin@admin.com']);

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Start a qualified user session
        $this->signIn('admin@admin.com');

        // Attempt user update
        $this->put('/users/' . $admin->id, [
            'first_name' => 'Olga',
            'last_name' => 'Prozorov',
            'email' => 'olga@prozorov.net',
            'roles' => ['moderator' => 2]
        ], $headers)->seeJson([
             'email' => 'olga@prozorov.net',
        ]);

        $user = Sentinel::findUserByCredentials(['email' => 'olga@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        // Verify
        $this->seeInDatabase('users', ['email' => 'olga@prozorov.net']);
        $this->missingFromDatabase('users', ['email' => 'admin@admin.com']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));
    }

    /** @test */
    public function you_can_remove_a_user_via_http()
    {
        // Fetch a user object
        $user = Sentinel::findUserByCredentials(['email' => 'user@user.com']);

        // Attempt user removal
        $this->signIn('admin@admin.com')
             ->delete('/users/' . $user->id, [
                '_token' => $this->getCsrfToken(),
                '_method' => 'delete'
            ])->followRedirects()
            ->see('user@user.com has been removed.');

        // Verify
        $this->missingFromDatabase('users', ['email' => 'user@user.com']);
    }

    /** @test */
    public function you_can_remove_a_user_via_ajax()
    {
        // Fetch a user object
        $user = Sentinel::findUserByCredentials(['email' => 'user@user.com']);

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Start a qualified user session
        $this->signIn('admin@admin.com');

        // Attempt user removal
        $this->delete('/users/' . $user->id, [], $headers)
             ->seeJson(['user@user.com has been removed.']);

        // Verify
        $this->missingFromDatabase('users', ['email' => 'user@user.com']);
    }
}
