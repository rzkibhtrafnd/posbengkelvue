<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    public function edit(): Response
    {
        $setting = Setting::first();
        $logoPath = $setting?->logo_path;
        $logoUrl = $logoPath && Storage::disk('public')->exists($logoPath)
            ? '/storage/'.ltrim($logoPath, '/')
            : null;

        return Inertia::render('Admin/Settings/Edit', [
            'setting' => $setting ? array_merge($setting->toArray(), [
                'logo_url' => $logoUrl,
            ]) : null,
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        $this->settingService->update($request->validated());

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan bengkel berhasil disimpan.');
    }
}
