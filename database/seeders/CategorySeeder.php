<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Oli dan Pelumas',
                'description' => 'Oli mesin, oli transmisi, dan cairan pelumas kendaraan.',
            ],
            [
                'name' => 'Suku Cadang',
                'description' => 'Sparepart dan komponen pengganti kendaraan.',
            ],
            [
                'name' => 'Aksesori',
                'description' => 'Aksesori dan perlengkapan tambahan kendaraan.',
            ],
            [
                'name' => 'Jasa Servis',
                'description' => 'Layanan perawatan dan perbaikan kendaraan.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
