<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    public function test_dashboard_redirects_admin_to_the_admin_dashboard(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
            );

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk();
    }

    public function test_dashboard_redirects_cashier_to_the_cashier_dashboard(): void
    {
        $cashier = $this->makeUser(UserRole::KASIR);

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertRedirect(route('kasir.dashboard'));

        $this->actingAs($cashier)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Kasir/Dashboard')
            );
    }

    public function test_users_can_only_open_the_admin_dashboard_and_user_creation_route(): void
    {
        $cashier = $this->makeUser(UserRole::KASIR);

        $this->actingAs($cashier)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->get(route('admin.users.create'))
            ->assertForbidden();

        $this->assertSame('/admin/users/create', route('admin.users.create', absolute: false));
    }

    private function makeUser(UserRole $role): User
    {
        return User::make([
            'name' => ucfirst($role->value),
            'email' => "{$role->value}@example.com",
            'role' => $role,
        ])->forceFill([
            'id' => 1,
            'email_verified_at' => now(),
        ]);
    }
}
