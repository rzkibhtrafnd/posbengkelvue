<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
            'banks' => ['nullable', 'array', 'max:10'],
            'banks.*.bank_name' => ['required', 'string', 'max:255'],
            'banks.*.bank_account_number' => ['required', 'string', 'max:100'],
            'banks.*.bank_account_holder' => ['required', 'string', 'max:255'],
            'qris_payload' => ['nullable', 'string', 'max:5000'],
            'tax_percentage' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}
