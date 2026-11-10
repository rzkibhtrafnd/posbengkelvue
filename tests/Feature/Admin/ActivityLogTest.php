<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    public function test_admin_can_view_logs_created_by_all_roles(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);
        $cashier = $this->makeUser(UserRole::KASIR);
        ActivityLog::query()->delete();

        $this->actingAs($cashier);
        $category = Category::query()->create(['name' => 'Servis']);
        $category->update(['name' => 'Servis Berkala']);

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/ActivityLogs/Index')
                ->has('activityLogs.data', 2)
                ->where('activityLogs.data.0.actor_name', $cashier->name)
                ->where('activityLogs.data.0.action', 'updated')
                ->where('activityLogs.data.0.changes.name.old', 'Servis')
                ->where('activityLogs.data.0.changes.name.new', 'Servis Berkala')
            );
    }

    public function test_cashier_cannot_access_activity_logs(): void
    {
        $cashier = $this->makeUser(UserRole::KASIR);

        $this->actingAs($cashier)
            ->get(route('admin.activity-logs.index'))
            ->assertForbidden();
    }

    public function test_sensitive_user_attributes_are_not_saved_to_logs(): void
    {
        $admin = $this->makeUser(UserRole::ADMIN);
        $this->actingAs($admin);

        $user = User::query()->create([
            'name' => 'Kasir Baru',
            'email' => 'kasir-baru@example.com',
            'password' => 'old-password',
            'role' => UserRole::KASIR,
        ]);

        $user->update(['password' => 'new-password']);

        $log = ActivityLog::query()->where('subject_type', 'User')->latest('id')->firstOrFail();

        $this->assertSame(
            ['old' => '[redacted]', 'new' => '[redacted]'],
            $log->changes['password'],
        );
        $this->assertSame('Kasir Baru', $log->subject_name);
    }

    private function makeUser(UserRole $role): User
    {
        return User::factory()->create([
            'name' => ucfirst($role->value),
            'email' => "{$role->value}-".uniqid().'@example.com',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}
