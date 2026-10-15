<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * Update the singleton settings record and manage its public logo.
     *
     * @param array<string, mixed> $data
     */
    public function update(array $data): Setting
    {
        $setting = Setting::first() ?? new Setting();
        $oldPaths = [];
        $newPaths = [];

        foreach ([
            'logo' => 'logo_path',
        ] as $input => $column) {
            $file = $data[$input] ?? null;
            
            $remove = filter_var($data['remove_'.$input] ?? false, FILTER_VALIDATE_BOOLEAN);

            unset($data[$input], $data['remove_'.$input]);

            if ($file instanceof UploadedFile) {
                $path = $file->storePublicly('settings/'.$input, 'public');

                if (! $path) {
                    throw new \RuntimeException('Logo bengkel gagal disimpan ke penyimpanan.');
                }

                if ($setting->{$column}) {
                    $oldPaths[] = $setting->{$column};
                }
                $newPaths[] = $path;
                $data[$column] = $path;
            } elseif ($remove && $setting->{$column}) {
                $oldPaths[] = $setting->{$column};
                $data[$column] = null;
            }
        }

        $setting->fill($data);
        $setting->save();

        foreach ($oldPaths as $path) {
            if ($path && ! in_array($path, $newPaths, true)) {
                Storage::disk('public')->delete($path);
            }
        }

        return $setting->refresh();
    }
}