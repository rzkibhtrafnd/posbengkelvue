<?php

namespace Tests\Unit;

use App\Support\PaginationOptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaginationOptionsTest extends TestCase
{
    public function test_it_uses_ten_rows_when_no_page_size_is_provided(): void
    {
        $this->assertSame(10, PaginationOptions::perPage(Request::create('/')));
    }

    public function test_it_accepts_supported_page_sizes(): void
    {
        $request = Request::create('/?per_page=50');

        $this->assertSame(50, PaginationOptions::perPage($request));
    }

    public function test_it_rejects_unsupported_page_sizes(): void
    {
        $this->expectException(ValidationException::class);

        PaginationOptions::perPage(Request::create('/?per_page=500'));
    }
}
