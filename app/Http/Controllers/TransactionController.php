<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Mechanic;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Services\TransactionService;
use App\Support\PaginationOptions;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeRead($request);

        $filters = $request->only(['search', 'date', 'per_page']);
        $cashierId = $request->user()?->role === UserRole::KASIR
            ? $request->user()->id
            : null;
        $transactions = $this->transactionService->getFilteredTransactions(
            $request->only(['search', 'date']),
            PaginationOptions::perPage($request),
            $cashierId,
        );

        return Inertia::render('Transactions/Index', compact('transactions', 'filters'));
    }

    public function create(Request $request): Response
    {
        $this->authorizeRead($request);

        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $vehicles = Vehicle::with('customer')->orderBy('plate_number')->get();
        $mechanics = Mechanic::orderBy('name')->get();

        return Inertia::render('Transactions/Create', compact('products', 'categories', 'vehicles', 'mechanics'));
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $this->authorizeRead($request);

        try {
            $transaction = $this->transactionService->createTransaction($request->validated());

            $isAdmin = $request->user()?->role === UserRole::ADMIN;
            $showRoute = $isAdmin
                ? route('admin.transactions.show', $transaction->id)
                : route('kasir.transactions.show', $transaction->id);

            return redirect()->to($showRoute)->with('success', 'Transaksi berhasil diproses.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Transaction $transaction): Response
    {
        $this->authorizeTransactionRead($request, $transaction);

        $transaction = $this->transactionService->getTransactionDetails($transaction);

        return Inertia::render('Transactions/Show', compact('transaction'));
    }

    public function edit(Transaction $transaction): Response
    {
        $this->authorizeAdmin();

        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $vehicles = Vehicle::with('customer')->orderBy('plate_number')->get();
        $mechanics = Mechanic::orderBy('name')->get();

        $transaction->load('items.product');
        $initialCart = $transaction->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product_name,
                'selling_price' => (float) $item->selling_price,
                'qty' => $item->qty,
                'type' => $item->product_type->value,
                'maxStock' => (int) ($item->product?->stock ?? 0) + $item->qty,
            ];
        })->values();

        return Inertia::render('Transactions/Create', compact(
            'products',
            'categories',
            'vehicles',
            'mechanics',
            'transaction',
            'initialCart'
        ));
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeAdmin();

        try {
            $this->transactionService->updateTransaction($transaction, $request->validated());

            return redirect()
                ->route('admin.transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeAdmin();

        $this->transactionService->deleteTransaction($transaction);

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function invoice(Request $request, Transaction $transaction): Response
    {
        $this->authorizeTransactionRead($request, $transaction);

        $transaction = $this->transactionService->getTransactionDetails($transaction);

        return Inertia::render('Transactions/Invoice', compact('transaction'));
    }

    private function authorizeRead(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, [UserRole::ADMIN, UserRole::KASIR], true), 403, 'Akses ditolak.');
    }

    private function authorizeTransactionRead(Request $request, Transaction $transaction): void
    {
        $this->authorizeRead($request);

        if ($request->user()?->role === UserRole::KASIR) {
            abort_unless((int) $transaction->user_id === (int) $request->user()->id, 404);
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(request()->user()?->role === UserRole::ADMIN, 403, 'Akses ditolak.');
    }
}
