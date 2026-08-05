<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_users_without_admin_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertForbidden();
    }

    public function test_owner_can_visit_the_dashboard(): void
    {
        StoreSetting::query()->create(['store_name' => 'Toko Senja']);
        $permission = Permission::findOrCreate('access admin');
        $role = Role::findOrCreate('owner');
        $role->givePermissionTo($permission);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan hari ini')
            ->assertSee('Order terbaru');
    }
}
