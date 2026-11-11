<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionBankDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_details_are_available_when_creating_a_transaction(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);
        $banks = $this->createSettingWithBank()->banks;

        $this->actingAs($admin)
            ->get(route('admin.transactions.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Create')
                ->where('banks.0.bank_name', $banks[0]['bank_name'])
                ->where('banks.0.bank_account_number', $banks[0]['bank_account_number'])
                ->where('banks.0.bank_account_holder', $banks[0]['bank_account_holder'])
            );
    }

    public function test_transfer_invoice_includes_configured_bank_details(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);
        $banks = $this->createSettingWithBank()->banks;
        $transaction = Transaction::query()->create([
            'invoice_number' => 'TRX-TEST-0001',
            'transaction_date' => now(),
            'user_id' => $admin->id,
            'customer_name' => 'Pelanggan Umum',
            'cashier_name' => $admin->name,
            'payment_method' => PaymentMethod::TRANSFER,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.transactions.invoice', $transaction))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Transactions/Invoice')
                ->where('transaction.payment_method', PaymentMethod::TRANSFER->value)
                ->where('workshop.banks.0.bank_name', $banks[0]['bank_name'])
                ->where('workshop.banks.0.bank_account_number', $banks[0]['bank_account_number'])
                ->where('workshop.banks.0.bank_account_holder', $banks[0]['bank_account_holder'])
            );
    }

    private function createSettingWithBank(): Setting
    {
        return Setting::query()->create([
            'name' => 'Bengkel Contoh',
            'phone' => '08123456789',
            'email' => 'bengkel@example.test',
            'address' => 'Alamat bengkel',
            'banks' => [[
                'bank_name' => 'Bank Contoh',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => 'Pemilik Bengkel',
            ]],
        ]);
    }
}
