<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    private static int $suppressionDepth = 0;

    private const EXCLUDED_ATTRIBUTES = [
        'password',
        'remember_token',
        'token',
        'banks',
        'qris_payload',
    ];

    public function created(Model $model): void
    {
        if (self::$suppressionDepth > 0) {
            return;
        }

        $this->record($model, 'created', $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        if (self::$suppressionDepth > 0) {
            return;
        }

        $changes = [];

        foreach ($model->getChanges() as $key => $value) {
            if ($key === 'updated_at') {
                continue;
            }

            $changes[$key] = [
                'old' => $this->safeValue($key, $model->getPrevious()[$key] ?? null),
                'new' => $this->safeValue($key, $value),
            ];
        }

        if ($changes !== []) {
            $this->record($model, 'updated', $changes);
        }
    }

    public function deleted(Model $model): void
    {
        if (self::$suppressionDepth > 0) {
            return;
        }

        $this->record($model, 'deleted', $model->getAttributes());
    }

    public static function withoutLogging(Closure $callback): mixed
    {
        self::$suppressionDepth++;

        try {
            return $callback();
        } finally {
            self::$suppressionDepth--;
        }
    }

    private function record(Model $model, string $action, array $attributes): void
    {
        $actor = Auth::user();
        $changes = [];

        foreach ($attributes as $key => $value) {
            $changes[$key] = $this->safeValue($key, $value);
        }

        ActivityLog::query()->create([
            'user_id' => $actor?->getAuthIdentifier(),
            'actor_name' => $actor?->name,
            'actor_role' => $actor?->role?->value ?? $actor?->role,
            'action' => $action,
            'subject_type' => class_basename($model),
            'subject_id' => (string) $model->getKey(),
            'subject_name' => $this->subjectName($model),
            'changes' => $changes,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
        ]);
    }

    private function safeValue(string $key, mixed $value): mixed
    {
        return in_array($key, self::EXCLUDED_ATTRIBUTES, true)
            ? '[redacted]'
            : $value;
    }

    private function subjectName(Model $model): string
    {
        foreach (['name', 'invoice_number', 'code', 'plate_number', 'email', 'product_name'] as $attribute) {
            if (filled($model->getAttribute($attribute))) {
                return (string) $model->getAttribute($attribute);
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }
}
