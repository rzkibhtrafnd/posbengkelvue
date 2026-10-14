<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
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
        $vehicleId = optional($this->route('vehicle'))->id ?? $this->route('vehicle');

        return [
            'customer_id'      => ['nullable', 'exists:customers,id'],
            'plate_number'     => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($vehicleId)],
            'brand'            => ['required', 'string', 'max:100'],
            'model'            => ['required', 'string', 'max:100'],
            'year'             => ['nullable', 'string', 'max:4'],
            'color'            => ['nullable', 'string', 'max:50'],
        ];
    }
}
