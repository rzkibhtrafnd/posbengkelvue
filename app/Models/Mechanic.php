<?php

namespace App\Models;

use App\Enums\MechanicStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => MechanicStatus::class,
        ];
    }
}