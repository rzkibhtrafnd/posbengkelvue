<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\DashboardService;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    public function test_dashboard_redirects_admin_to_the_admin_dashboard(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->mock(DashboardService::class)
            ->shouldReceive('getAdminDashboard')
            ->once()
            ->with(null, null)
            ->andReturn($this->dashboardData());

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('dashboard.stats.periodTransactions', 0)
                ->where('dashboard.stats.totalTransactions', 0)
                ->where('dashboard.stats.products', 0)
                ->has('dashboard.dailyTrend', 7)
                ->has('dashboard.monthlyTrend', 12)
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

        $this->mock(DashboardService::class)
            ->shouldReceive('getCashierDashboard')
            ->once()
            ->with($cashier->id)
            ->andReturn($this->dashboardData());

        $this->actingAs($cashier)
            ->get(route('kasir.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Kasir/Dashboard')
                ->where('dashboard.stats.todayTransactions', 0)
                ->has('dashboard.salesTrend', 7)
            );
    }

    public function test_admin_dashboard_passes_a_valid_date_range_to_the_service(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);

        $this->mock(DashboardService::class)
            ->shouldReceive('getAdminDashboard')
            ->once()
            ->with('2026-01-05', '2026-01-11')
            ->andReturn($this->dashboardData());

        $this->actingAs($admin)
            ->get(route('admin.dashboard', [
                'date_from' => '2026-01-05',
                'date_to' => '2026-01-11',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.date_from', '2026-01-05')
                ->where('filters.date_to', '2026-01-11')
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

    private function dashboardData(): array
    {
        return [
            'stats' => [
                'todayTransactions' => 0,
                'periodTransactions' => 0,
                'totalTransactions' => 0,
                'products' => 0,
            ],
            'dailyTrend' => array_fill(0, 7, []),
            'salesTrend' => array_fill(0, 7, []),
            'monthlyTrend' => array_fill(0, 12, []),
        ];
    }
}
