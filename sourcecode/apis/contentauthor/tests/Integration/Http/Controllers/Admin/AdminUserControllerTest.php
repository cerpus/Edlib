<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Admin;

use App\Administrator;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index(): void
    {
        $admin = new Administrator();
        $admin->username = 'emqu';
        $admin->password = 'abc123';
        $admin->name = 'Emily Quackfaster';
        $admin->save();

        $user = new GenericUser([
            'roles' => ['superadmin'],
            'name' => 'Just Testing',
        ]);

        $ret = $this->withSession(['user' => $user])
            ->get(route('admin-users.index'))
            ->assertOk()
            ->assertViewHas('adminUsers');

        $this->assertCount(1, $ret['adminUsers']);
        $this->assertSame($admin->id, $ret['adminUsers'][0]->id);
    }

    public function test_store(): void
    {
        $input = [
            'name' => 'Emily Quackfaster',
            'username' => 'emqu',
            'password' => 'abcdefghijk0123456789',
        ];

        $ret = $this->withSession(['user' => new GenericUser(['roles' => ['superadmin']])])
            ->post(route('admin-users.store', $input))
            ->assertRedirectToRoute('admin-users.index');

        $ret->assertSessionHas('message', 'User Emily Quackfaster created!');
        $this->assertDatabaseHas('administrators', [
            'username' => $input['username'],
            'name' => $input['name'],
        ]);
        $this->assertDatabaseMissing('administrators', [
            'password' => $input['password'],
        ]);
    }

    public function test_destroy_success(): void
    {
        $admin = new Administrator();
        $admin->username = 'emqu';
        $admin->password = 'abc123';
        $admin->name = 'Emily Quackfaster';
        $admin->save();

        $ret = $this->withSession(['user' => new GenericUser(['id' => 42, 'roles' => ['superadmin']])])
            ->delete(route('admin-users.destroy', ['admin_user' => $admin->id]))
            ->assertRedirectToRoute('admin-users.index');

        $ret->assertSessionHas('message', 'Emily Quackfaster deleted!');
        $this->assertDatabaseMissing('administrators', ['id' => $admin->id]);
    }

    public function test_destroy_failToDestroySelf(): void
    {
        $admin = new Administrator();
        $admin->username = 'emqu';
        $admin->password = 'abc123';
        $admin->name = 'Emily Quackfaster';
        $admin->save();

        $user = new GenericUser([
            'id' => $admin->id,
            'roles' => ['superadmin']
        ]);

        $ret = $this->withSession(['user' => $user])
            ->delete(route('admin-users.destroy', ['admin_user' => $admin->id]))
            ->assertRedirectToRoute('admin-users.index');

        $ret->assertSessionHas('message', 'You can not delete yourself!');
        $this->assertDatabaseHas('administrators', ['id' => $admin->id]);
    }

    public function test_destroy_failToDestroyNonExistingUser(): void
    {
        $this->assertDatabaseMissing('administrators', ['id' => 10]);

        $this->withSession(['user' => new GenericUser(['id' => 42, 'roles' => ['superadmin']])])
            ->delete(route('admin-users.destroy', ['admin_user' => 10]))
            ->assertNotFound();
    }
}
