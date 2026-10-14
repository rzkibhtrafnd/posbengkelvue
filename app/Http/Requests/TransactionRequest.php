<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'            => 'nullable|exists:vehicles,id',
            'mechanic_id'           => 'nullable|exists:mechanics,id',
            'payment_method'        => ['required', Rule::enum(PaymentMethod::class)],
            'pay_amount'            => 'required|numeric|min:0',
            'discount'              => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.subtotal'      => 'required|numeric|min:0',
            'subtotal'              => 'required|numeric|min:0',
            'grand_total'           => 'required|numeric|min:0',
            'change_amount'         => 'required|numeric|min:0',
        ];
    }
}