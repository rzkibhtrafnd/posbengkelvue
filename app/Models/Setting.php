<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'logo_path',
        'banks',
        'qris_payload',
        'tax_percentage',
    ];

    protected function casts(): array
    {
        return [
            'banks' => 'array',
            'tax_percentage' => 'decimal:2',
        ];
    }
}
