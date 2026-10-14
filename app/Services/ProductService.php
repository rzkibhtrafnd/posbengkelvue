<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getFilteredProducts(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Product::with('category')
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createProduct(array $data): Product
    {
        if ($data['type'] === ProductType::JASA->value) {
            $data['stock'] = 0;
            $data['min_stock'] = 0;
            $data['cost_price'] = 0;
        }

        return Product::create($data);
    }

    public function getProductDetails(Product $product): Product
    {
        return $product->load('category');
    }

    public function updateProduct(Product $product, array $data): bool
    {
        if ($data['type'] === ProductType::JASA->value) {
            $data['stock'] = 0;
            $data['min_stock'] = 0;
            $data['cost_price'] = 0;
        }

        return $product->update($data);
    }

    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }
}