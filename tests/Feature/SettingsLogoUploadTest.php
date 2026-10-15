<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_persist_the_workshop_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'name' => 'Bengkel Test',
            'phone' => '08123456789',
            'email' => 'bengkel@example.test',
            'address' => 'Alamat bengkel test',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'banks' => [],
            'qris_payload' => '',
            'tax_percentage' => '0.00',
        ]);

        $response->assertRedirect(route('admin.settings.edit'));

        $setting = Setting::query()->firstOrFail();
        $this->assertNotEmpty($setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
    }
}
