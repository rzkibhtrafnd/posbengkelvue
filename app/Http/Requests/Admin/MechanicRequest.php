<?php

namespace App\Http\Requests\Admin;

use App\Enums\MechanicStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class MechanicRequest extends FormRequest
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
        $mechanicId = optional($this->route('mechanic'))->id ?? $this->route('mechanic');

        return [
            'code'    => ['nullable', 'string', 'max:50', Rule::unique('mechanics', 'code')->ignore($mechanicId)],
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status'  => ['required', Rule::enum(MechanicStatus::class)],
        ];
    }
}