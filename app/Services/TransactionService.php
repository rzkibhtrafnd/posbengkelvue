<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Enums\TransactionStatus;
use App\Models\Mechanic;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Observers\ActivityLogObserver;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    public function getFilteredTransactions(array $filters = [], int $perPage = 10, ?int $userId = null): LengthAwarePaginator
    {
        return Transaction::query()
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTransactionDetails(Transaction $transaction): Transaction
    {
        return $transaction->load('items');
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = 'TRX-'.date('Ymd').'-';
        $lastTransaction = Transaction::where('invoice_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if (! $lastTransaction) {
            return $prefix.'0001';
        }

        $lastNumber = (int) substr($lastTransaction->invoice_number, -4);

        return $prefix.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function createTransaction(array $data): Transaction
    {
        return ActivityLogObserver::withoutLogging(fn () => DB::transaction(function () use ($data) {
            $vehicle = ! empty($data['vehicle_id'])
                ? Vehicle::with('customer')->findOrFail($data['vehicle_id'])
                : null;
            $mechanic = $vehicle && ! empty($data['mechanic_id'])
                ? Mechanic::find($data['mechanic_id'])
                : null;
            $user = Auth::user();
            $paymentMethod = PaymentMethod::from($data['payment_method'] ?? PaymentMethod::CASH->value);
            $payAmount = $paymentMethod === PaymentMethod::CASH
                ? $data['pay_amount']
                : $data['grand_total'];
            $changeAmount = $paymentMethod === PaymentMethod::CASH
                ? $data['change_amount']
                : 0;

            $transaction = Transaction::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'transaction_date' => now(),
                'user_id' => $user->id,
                'customer_id' => $vehicle?->customer_id,
                'vehicle_id' => $vehicle?->id,
                'mechanic_id' => $mechanic?->id,

                'customer_name' => $vehicle?->customer?->name ?? 'Pelanggan Umum',
                'customer_phone' => $vehicle?->customer?->phone,
                'vehicle_plate_number' => $vehicle?->plate_number,
                'vehicle_brand_model' => $vehicle ? trim($vehicle->brand.' '.$vehicle->model) : null,
                'mechanic_name' => $mechanic?->name ?? 'Tanpa Montir',
                'cashier_name' => $user->name,

                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'grand_total' => $data['grand_total'],
                'pay_amount' => $payAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'status' => TransactionStatus::COMPLETED,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($transaction, $data['items']);
            $this->activityLogService->recordTransaction($transaction, 'created');

            return $transaction;
        }));
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return ActivityLogObserver::withoutLogging(fn () => DB::transaction(function () use ($transaction, $data) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $previousSnapshot = $this->activityLogService->getTransactionSnapshot($transaction);

            foreach ($transaction->items as $item) {
                if ($item->product_id && $item->product_type === ProductType::BARANG) {
                    $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                    $product->stock += $item->qty;
                    $product->save();
                }
            }

            $vehicle = ! empty($data['vehicle_id'])
                ? Vehicle::with('customer')->findOrFail($data['vehicle_id'])
                : null;
            $mechanic = $vehicle && ! empty($data['mechanic_id'])
                ? Mechanic::find($data['mechanic_id'])
                : null;
            $paymentMethod = PaymentMethod::from($data['payment_method']);
            $payAmount = $paymentMethod === PaymentMethod::CASH
                ? $data['pay_amount']
                : $data['grand_total'];

            $transaction->update([
                'customer_id' => $vehicle?->customer_id,
                'vehicle_id' => $vehicle?->id,
                'mechanic_id' => $mechanic?->id,
                'customer_name' => $vehicle?->customer?->name ?? 'Pelanggan Umum',
                'customer_phone' => $vehicle?->customer?->phone,
                'vehicle_plate_number' => $vehicle?->plate_number,
                'vehicle_brand_model' => $vehicle ? trim($vehicle->brand.' '.$vehicle->model) : null,
                'mechanic_name' => $mechanic?->name ?? 'Tanpa Montir',
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'] ?? 0,
                'grand_total' => $data['grand_total'],
                'pay_amount' => $payAmount,
                'change_amount' => $paymentMethod === PaymentMethod::CASH ? $data['change_amount'] : 0,
                'payment_method' => $paymentMethod,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($transaction->items as $item) {
                $item->delete();
            }

            $this->syncItems($transaction, $data['items']);
            $this->activityLogService->recordTransaction($transaction, 'updated', $previousSnapshot);

            return $transaction;
        }));
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        ActivityLogObserver::withoutLogging(fn () => DB::transaction(function () use ($transaction) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $previousSnapshot = $this->activityLogService->getTransactionSnapshot($transaction);

            foreach ($transaction->items as $item) {
                if ($item->product_id && $item->product_type === ProductType::BARANG) {
                    $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                    $product->stock += $item->qty;
                    $product->save();
                }
            }

            $transaction->delete();
            $this->activityLogService->recordTransaction($transaction, 'deleted', $previousSnapshot);
        }));
    }

    private function syncItems(Transaction $transaction, array $items): void
    {
        foreach ($items as $itemData) {
            $product = Product::with('category')->lockForUpdate()->findOrFail($itemData['product_id']);
            $typeValue = $product->type->value ?? $product->type;

            if ($typeValue === ProductType::BARANG->value) {
                if ($product->stock < $itemData['qty']) {
                    throw new Exception("Stok produk {$product->name} tidak mencukupi. (Sisa: {$product->stock})");
                }
                $product->stock -= $itemData['qty'];
                $product->save();
            }

            $transaction->items()->create([
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'product_type' => $typeValue,
                'category_name' => $product->category->name ?? 'Umum',
                'cost_price' => $product->cost_price ?? 0,
                'selling_price' => $itemData['selling_price'],
                'qty' => $itemData['qty'],
                'discount' => $itemData['discount'] ?? 0,
                'subtotal' => $itemData['subtotal'],
            ]);
        }
    }
}
