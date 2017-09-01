<?php

namespace Centaur\Tests\Integrated;

use Sentinel;
use Centaur\Tests\TestCase;

class RoleManagementTest extends TestCase
{
    /** @test */
    public function you_can_create_a_role_via_http()
    {
        // Arrange
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->post('/roles', [
            'name' => 'Prozorov',
            'slug' => 'prozorov',
            'permissions' => [
                'users.create' => 1,
                'users.update' => 1,
            ]
        ]);

        // Assert
        $role = Sentinel::findRoleByName('Prozorov');
        $response->assertSessionHas('success', "Role 'Prozorov' has been created.");
        $this->assertDatabaseHas('roles', ['name' => 'Prozorov', 'slug' => 'prozorov']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_create_a_role_via_ajax()
    {
        // Arrange
        $this->signIn('admin@admin.com');
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];

        // Act
        $response = $this->post('/roles', [
            'name' => 'Prozorov',
            'slug' => 'prozorov',
            'permissions' => [
                'users.create' => "1",
                'users.update' => "1",
            ],
        ], $headers);

        // Assert
        $role = Sentinel::findRoleByName('Prozorov');
        $response->assertJsonFragment(['name' => 'Prozorov']);
        $this->assertDatabaseHas('roles', ['name' => 'Prozorov', 'slug' => 'prozorov']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_update_a_role_via_http()
    {
        // Arrange
        $role = Sentinel::findRoleByName('Subscriber');
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->put('/roles/' . $role->id, [
            'name' => 'Member',
            'slug' => 'member',
            'permissions' => [
                'users.create' => 1,
                'users.update' => 1,
            ]
        ]);

        // Assert
        $role = Sentinel::findRoleByName('Member');
        $response->assertSessionHas('success', "Role 'Member' has been updated.");
        $this->assertDatabaseHas('roles', ['name' => 'Member', 'slug' => 'member']);
        $this->assertDatabaseMissing('roles', ['name' => 'Subscriber', 'slug' => 'subscriber']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_update_a_role_via_ajax()
    {
        // Arrange
        $role = Sentinel::findRoleByName('Subscriber');
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->put('/roles/' . $role->id, [
            'name' => 'Member',
            'slug' => 'member',
            'permissions' => [
                'users.create' => "1",
                'users.update' => "1",
            ],
        ], $headers);

        // Act
        $role = Sentinel::findRoleByName('Member');
        $response->assertJsonFragment(['name' => 'Member']);
        $this->assertDatabaseHas('roles', ['name' => 'Member', 'slug' => 'member']);
        $this->assertDatabaseMissing('roles', ['name' => 'Subscriber', 'slug' => 'subscriber']);
        $this->assertTrue($role->hasAccess('users.create'));
        $this->assertTrue($role->hasAccess('users.update'));
        $this->assertFalse($role->hasAccess('users.delete'));
    }

    /** @test */
    public function you_can_remove_a_role_via_http()
    {
        // Arrange
        $role = Sentinel::findRoleByName('Subscriber');
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->delete('/roles/' . $role->id, [
            '_token' => $this->getCsrfToken(),
            '_method' => 'delete'
        ]);

        // Verify
        $response->assertStatus(302);
        $response->assertSessionHas('success', "Role 'Subscriber' has been removed.");
        $this->assertDatabaseMissing('roles', ['name' => 'Subscriber']);
    }

    /** @test */
    public function you_can_remove_a_user_via_ajax()
    {
        // Arrange
        $role = Sentinel::findRoleByName('Subscriber');
        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => $this->getCsrfToken(),
        ];
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->delete('/roles/' . $role->id, [], $headers);

        // Verify
        $response->assertJsonFragment(["Role 'Subscriber' has been removed."]);
        $this->assertDatabaseMissing('roles', ['name' => 'Subscriber']);
    }
}
