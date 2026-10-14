<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('name');

        $products = [
            [
                'category' => 'Oli dan Pelumas',
                'code' => 'OLI-001',
                'name' => 'Oli Mesin 10W-40 1L',
                'type' => ProductType::BARANG,
                'cost_price' => 65000,
                'selling_price' => 80000,
                'stock' => 20,
                'min_stock' => 5,
                'unit' => 'Botol',
                'description' => 'Oli mesin motor 10W-40 kemasan 1 liter.',
            ],
            [
                'category' => 'Oli dan Pelumas',
                'code' => 'OLI-002',
                'name' => 'Oli Gardan 120ml',
                'type' => ProductType::BARANG,
                'cost_price' => 12000,
                'selling_price' => 18000,
                'stock' => 25,
                'min_stock' => 5,
                'unit' => 'Botol',
                'description' => 'Oli gardan motor matic kemasan 120 ml.',
            ],
            [
                'category' => 'Suku Cadang',
                'code' => 'SP-001',
                'name' => 'Busi Standar',
                'type' => ProductType::BARANG,
                'cost_price' => 10000,
                'selling_price' => 15000,
                'stock' => 30,
                'min_stock' => 8,
                'unit' => 'Pcs',
                'description' => 'Busi standar untuk motor harian.',
            ],
            [
                'category' => 'Suku Cadang',
                'code' => 'SP-002',
                'name' => 'Kampas Rem Depan',
                'type' => ProductType::BARANG,
                'cost_price' => 22000,
                'selling_price' => 35000,
                'stock' => 15,
                'min_stock' => 4,
                'unit' => 'Set',
                'description' => 'Kampas rem depan motor.',
            ],
            [
                'category' => 'Suku Cadang',
                'code' => 'SP-003',
                'name' => 'Filter Udara',
                'type' => ProductType::BARANG,
                'cost_price' => 18000,
                'selling_price' => 28000,
                'stock' => 12,
                'min_stock' => 3,
                'unit' => 'Pcs',
                'description' => 'Filter udara pengganti untuk motor.',
            ],
            [
                'category' => 'Aksesori',
                'code' => 'AKS-001',
                'name' => 'Lampu LED Motor',
                'type' => ProductType::BARANG,
                'cost_price' => 35000,
                'selling_price' => 50000,
                'stock' => 10,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'description' => 'Lampu LED motor.',
            ],
            [
                'category' => 'Jasa Servis',
                'code' => 'JSA-001',
                'name' => 'Servis Ringan',
                'type' => ProductType::JASA,
                'cost_price' => 0,
                'selling_price' => 50000,
                'stock' => 0,
                'min_stock' => 0,
                'unit' => 'Layanan',
                'description' => 'Pemeriksaan dan perawatan rutin kendaraan.',
            ],
            [
                'category' => 'Jasa Servis',
                'code' => 'JSA-002',
                'name' => 'Ganti Oli',
                'type' => ProductType::JASA,
                'cost_price' => 0,
                'selling_price' => 10000,
                'stock' => 0,
                'min_stock' => 0,
                'unit' => 'Layanan',
                'description' => 'Jasa penggantian oli mesin.',
            ],
            [
                'category' => 'Jasa Servis',
                'code' => 'JSA-003',
                'name' => 'Servis Rem',
                'type' => ProductType::JASA,
                'cost_price' => 0,
                'selling_price' => 35000,
                'stock' => 0,
                'min_stock' => 0,
                'unit' => 'Layanan',
                'description' => 'Pemeriksaan dan penyetelan sistem pengereman.',
            ],
        ];

        foreach ($products as $product) {
            $category = $categories->get($product['category']);

            if (! $category) {
                throw new \RuntimeException("Kategori {$product['category']} belum tersedia. Jalankan CategorySeeder terlebih dahulu.");
            }

            unset($product['category']);
            Product::firstOrCreate(
                ['code' => $product['code']],
                ['category_id' => $category->id, ...$product]
            );
        }
    }
}
