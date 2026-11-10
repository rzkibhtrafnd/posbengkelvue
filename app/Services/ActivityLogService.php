<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function getFilteredActivityLogs(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->filter($filters)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function recordTransaction(Transaction $transaction, string $action, ?array $previousSnapshot = null): ActivityLog
    {
        $snapshot = $this->getTransactionSnapshot($transaction);
        $changes = match ($action) {
            'created' => ['transaction' => $snapshot],
            'updated' => ['transaction' => [
                'old' => $previousSnapshot,
                'new' => $snapshot,
            ]],
            'deleted' => ['transaction' => $previousSnapshot ?? $snapshot],
            default => throw new \InvalidArgumentException("Unsupported transaction activity action: {$action}"),
        };
        $actor = Auth::user();

        return ActivityLog::query()->create([
            'user_id' => $actor?->getAuthIdentifier(),
            'actor_name' => $actor?->name,
            'actor_role' => $actor?->role?->value ?? $actor?->role,
            'action' => $action,
            'subject_type' => class_basename($transaction),
            'subject_id' => (string) $transaction->getKey(),
            'subject_name' => $transaction->invoice_number,
            'changes' => $changes,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
        ]);
    }

    public function getTransactionSnapshot(Transaction $transaction): array
    {
        return [
            'invoice_number' => $transaction->invoice_number,
            'cashier_name' => $transaction->cashier_name,
            'customer_name' => $transaction->customer_name,
            'vehicle_plate_number' => $transaction->vehicle_plate_number,
            'subtotal' => $transaction->subtotal,
            'discount' => $transaction->discount,
            'grand_total' => $transaction->grand_total,
            'payment_method' => $transaction->payment_method?->value ?? $transaction->payment_method,
            'status' => $transaction->status?->value ?? $transaction->status,
            'items' => $transaction->items()->get([
                'product_name',
                'product_type',
                'qty',
                'selling_price',
                'discount',
                'subtotal',
            ])->map(fn ($item) => [
                'product_name' => $item->product_name,
                'product_type' => $item->product_type?->value ?? $item->product_type,
                'qty' => $item->qty,
                'selling_price' => $item->selling_price,
                'discount' => $item->discount,
                'subtotal' => $item->subtotal,
            ])->all(),
        ];
    }
}
