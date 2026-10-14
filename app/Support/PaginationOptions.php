<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaginationOptions
{
    public const PER_PAGE = [10, 25, 50, 100];

    public static function perPage(Request $request): int
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', Rule::in(self::PER_PAGE)],
        ]);

        return (int) ($validated['per_page'] ?? self::PER_PAGE[0]);
    }
}
