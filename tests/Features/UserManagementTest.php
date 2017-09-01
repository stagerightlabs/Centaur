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
        // Arrange
        $moderatorRole = Sentinel::findRoleBySlug('moderator');
        Mail::fake();

        // Act
        $this->signIn('admin@admin.com');

        $response = $this->post('/users', [
            'first_name' => 'Andrei',
            'last_name' => 'Prozorov',
            'email' => 'andrei@prozorov.net',
            'roles' => [$moderatorRole->slug => $moderatorRole->id],
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        // Assert
        $user = Sentinel::findUserByCredentials(['email' => 'andrei@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        $this->assertDatabaseHas('users', ['email' => 'andrei@prozorov.net']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));

        Mail::assertQueued(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function you_can_create_a_user_via_ajax()
    {
        // Arrange
        $moderatorRole = Sentinel::findRoleBySlug('moderator');
        Mail::fake();
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $this->signIn('admin@admin.com');

        $response = $this->post('/users', [
            'first_name' => 'Andrei',
            'last_name' => 'Prozorov',
            'email' => 'andrei@prozorov.net',
            'roles' => [$moderatorRole->slug => $moderatorRole->id],
            'password' => 'password',
            'password_confirmation' => 'password'
        ], $headers);

        // Assert
        $user = Sentinel::findUserByCredentials(['email' => 'andrei@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');

        $response->assertJsonFragment(['email' => 'andrei@prozorov.net']);
        $this->assertDatabaseHas('users', ['email' => 'andrei@prozorov.net']);
        $this->assertTrue($user->inRole($moderatorRole));
        $this->assertFalse($user->inRole($administrators));

        Mail::assertQueued(CentaurWelcomeEmail::class, function($mail) {
            return $mail->hasTo('andrei@prozorov.net');
        });
    }

    /** @test */
    public function you_can_update_a_user_via_http()
    {
        // Arrange
        $admin = Sentinel::findUserByCredentials(['email' => 'admin@admin.com']);
        $moderatorRole = Sentinel::findRoleBySlug('moderator');

        // Act
        $this->signIn('admin@admin.com');

        $response = $this->put('/users/' . $admin->id, [
            'first_name' => 'Olga',
            'last_name' => 'Prozorov',
            'email' => 'olga@prozorov.net',
            'roles' => [$moderatorRole->slug => $moderatorRole->id],
        ]);
             // ->type('Olga', 'first_name')
             // ->type('Prozorov', 'last_name')
             // ->type('olga@prozorov.net', 'email')
             // ->uncheck('roles[administrator]')
             // ->check('roles[moderator]')
             // ->press('Update')

        // Assert
        $user = Sentinel::findUserByCredentials(['email' => 'olga@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['email' => 'olga@prozorov.net']);
        $this->assertDatabaseMissing('users', ['email' => 'admin@admin.com']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));
    }

    /** @test */
    public function you_can_update_a_user_via_ajax()
    {
        // Arrange
        $admin = Sentinel::findUserByCredentials(['email' => 'admin@admin.com']);
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];
        $moderatorRole = Sentinel::findRoleBySlug('moderator');

        // Act
        $this->signIn('admin@admin.com');

        $response = $this->put('/users/' . $admin->id, [
            'first_name' => 'Olga',
            'last_name' => 'Prozorov',
            'email' => 'olga@prozorov.net',
            'roles' => [$moderatorRole->slug => $moderatorRole->id]
        ], $headers);

        // Assert
        $user = Sentinel::findUserByCredentials(['email' => 'olga@prozorov.net']);
        $administrators = Sentinel::findRoleBySlug('administrator');
        $moderators = Sentinel::findRoleBySlug('moderator');

        $response->assertJsonFragment(['email' => 'olga@prozorov.net']);
        $this->assertDatabaseHas('users', ['email' => 'olga@prozorov.net']);
        $this->assertDatabaseMissing('users', ['email' => 'admin@admin.com']);
        $this->assertTrue($user->inRole($moderators));
        $this->assertFalse($user->inRole($administrators));
    }

    /** @test */
    public function you_can_remove_a_user_via_http()
    {
        // Arrange
        $user = Sentinel::findUserByCredentials(['email' => 'user@user.com']);

        // Attempt user removal
        $this->signIn('admin@admin.com');
        $response = $this->delete('/users/' . $user->id, [
            '_token' => $this->getCsrfToken(),
            '_method' => 'delete'
        ]);

        // Assert
        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['email' => 'user@user.com']);
    }

    /** @test */
    public function you_can_remove_a_user_via_ajax()
    {
        // Arrange
        $user = Sentinel::findUserByCredentials(['email' => 'user@user.com']);
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $this->signIn('admin@admin.com');
        $response = $this->delete('/users/' . $user->id, [], $headers);

        // Assert
        $response->assertJsonFragment(['user@user.com has been removed.']);
        $this->assertDatabaseMissing('users', ['email' => 'user@user.com']);
    }
}
