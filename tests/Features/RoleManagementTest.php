<?php

namespace Centaur\Tests\Features;

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
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
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
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
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
            '_token' => csrf_token(),
            '_method' => 'delete'
        ]);

        // Verify
        $response->assertStatus(302);
        $response->assertSessionHas('success', "Role 'Subscriber' has been removed.");
        $this->assertDatabaseMissing('roles', ['name' => 'Subscriber']);
    }

    /** @test */
    public function you_can_remove_a_role_via_ajax()
    {
        // Arrange
        $role = Sentinel::findRoleByName('Subscriber');
        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
        ];
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->delete('/roles/' . $role->id, [], $headers);

        // Verify
        $response->assertJsonFragment(["Role 'Subscriber' has been removed."]);
        $this->assertDatabaseMissing('roles', ['name' => 'Subscriber']);
    }

    /** @test */
    public function you_cannot_remove_roles_you_currently_belong_to()
    {
        // Arrange
        $role = Sentinel::findRoleByName('Administrator');
        $headers = [
            'Accept' => 'application/json',
            'X-CSRF-TOKEN' => csrf_token(),
        ];
        $this->signIn('admin@admin.com');

        // Act
        $response = $this->delete('/roles/' . $role->id, [], $headers);

        // Verify
        $response->assertStatus(422);
        $this->assertDatabaseHas('roles', ['name' => 'Administrator']);
    }
}
