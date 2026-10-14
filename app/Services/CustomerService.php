<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function generateCode(): string
    {
        $prefix = 'CUST-';
        $latestCustomer = Customer::where('code', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestCustomer) {
            return $prefix . '0001';
        }

        $number = (int) substr($latestCustomer->code, strlen($prefix));
        return $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }

    public function createCustomer(array $data): Customer
    {
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode();
        }

        return Customer::create([
            'code'    => $data['code'],
            'name'    => $data['customer_name'] ?? $data['name'],
            'phone'   => $data['customer_phone'] ?? $data['phone'] ?? null,
            'address' => $data['customer_address'] ?? $data['address'] ?? null,
        ]);
    }
}