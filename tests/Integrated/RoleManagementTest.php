<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Centaur\Tests\TestCase;

class RoleManagementTest extends TestCase
{
    /** @test */
    public function you_can_create_a_role_via_http()
    {
        // Attempt role creations
        $this->signIn('admin@admin.com')
             ->visit('/roles/create')
             ->type('Prozorov', 'name')
             ->type('prozorov', 'slug')
             ->check('permissions[users.create]')
             ->check('permissions[users.update]')
             ->press('Create')
             ->see('Role \'Prozorov\' has been created.');

        $role = Sentinel::findRoleByName('Prozorov');

        // Verify
        $this->seeInDatabase('roles', ['name' => 'Prozorov', 'slug' => 'prozorov']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_create_a_role_via_ajax()
    {
        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Create a qualified user session
        $this->signIn('admin@admin.com');

        // Attempt role creation
        $this->post('/roles', [
            'name' => 'Prozorov',
            'slug' => 'prozorov',
            'permissions' => [
                'users.create' => "1",
                'users.update' => "1",
            ],
        ], $headers)->seeJson([
             'name' => 'Prozorov',
        ]);

        $role = Sentinel::findRoleByName('Prozorov');

        // Verify
        $this->seeInDatabase('roles', ['name' => 'Prozorov', 'slug' => 'prozorov']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_update_a_role_via_http()
    {
        // Fetch a role object
        $role = Sentinel::findRoleByName('Subscriber');

        // Attempt role creations
        $this->signIn('admin@admin.com')
             ->visit('/roles/' . $role->id . '/edit')
             ->type('Member', 'name')
             ->type('member', 'slug')
             ->check('permissions[users.create]')
             ->check('permissions[users.update]')
             ->press('Update')
             ->see('Role \'Member\' has been updated.');

        $role = Sentinel::findRoleByName('Member');

        // Verify
        $this->seeInDatabase('roles', ['name' => 'Member', 'slug' => 'member']);
        $this->missingFromDatabase('roles', ['name' => 'Subscriber', 'slug' => 'subscriber']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_update_a_role_via_ajax()
    {
        // Fetch a role object
        $role = Sentinel::findRoleByName('Subscriber');

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Start a qualified user session
        $this->signIn('admin@admin.com');

        // Attempt role update
        $this->put('/roles/' . $role->id, [
            'name' => 'Member',
            'slug' => 'member',
            'permissions' => [
                'users.create' => "1",
                'users.update' => "1",
            ],
        ], $headers)->seeJson([
             'name' => 'Member',
        ]);

        $role = Sentinel::findRoleByName('Member');

        // Verify
        $this->seeInDatabase('roles', ['name' => 'Member', 'slug' => 'member']);
        $this->missingFromDatabase('roles', ['name' => 'Subscriber', 'slug' => 'subscriber']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_remove_a_role_via_http()
    {
        // Fetch a role object
        $role = Sentinel::findRoleByName('Subscriber');

        // Attempt role removal
        $this->signIn('admin@admin.com')
             ->delete('/roles/' . $role->id, [
                '_token' => $this->getCsrfToken(),
                '_method' => 'delete'
            ])->followRedirects()
        ->see('Role \'Subscriber\' has been removed.');

        // Verify
        $this->missingFromDatabase('roles', ['name' => 'Subscriber']);
    }

    /** @test */
    public function you_can_remove_a_user_via_ajax()
    {
        // Fetch a role object
        $role = Sentinel::findRoleByName('Subscriber');

        // Specify that this is an ajax request
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Create a qualified user session
        $this->signIn('admin@admin.com');

        // Attempt role removal
        $this->delete('/roles/' . $role->id, [], $headers)
             ->seeJson(['Role \'Subscriber\' has been removed.']);

        // Verify
        $this->missingFromDatabase('roles', ['name' => 'Subscriber']);
    }
}
