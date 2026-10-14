<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->role === UserRole::ADMIN
            || ($this->isMethod('GET') && $user->role === UserRole::KASIR)
        );
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = optional($this->route('product'))->id ?? $this->route('product');

        return [
            'category_id'   => ['required', 'exists:categories,id'],
            'code'          => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($productId)],
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', Rule::enum(ProductType::class)],
            'cost_price'    => ['required_if:type,' . ProductType::BARANG->value, 'nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock'         => ['required_if:type,' . ProductType::BARANG->value, 'nullable', 'integer', 'min:0'],
            'min_stock'     => ['required_if:type,' . ProductType::BARANG->value, 'nullable', 'integer', 'min:0'],
            'unit'          => ['nullable', 'string', 'max:50'],
            'description'   => ['nullable', 'string', 'max:500'],
        ];
    }
}
