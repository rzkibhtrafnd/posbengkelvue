<?php

namespace Database\Seeders;

use App\Enums\MechanicStatus;
use App\Models\Mechanic;
use Illuminate\Database\Seeder;

class MechanicSeeder extends Seeder
{
    public function run(): void
    {
        $mechanics = [
            [
                'code' => 'MTR-001',
                'name' => 'Budi Santoso',
                'phone' => '081234567801',
                'address' => 'Jl. Melati No. 10',
                'status' => MechanicStatus::ACTIVE,
            ],
            [
                'code' => 'MTR-002',
                'name' => 'Agus Setiawan',
                'phone' => '081234567802',
                'address' => 'Jl. Mawar No. 12',
                'status' => MechanicStatus::ACTIVE,
            ],
            [
                'code' => 'MTR-003',
                'name' => 'Rudi Hartono',
                'phone' => '081234567803',
                'address' => 'Jl. Kenanga No. 5',
                'status' => MechanicStatus::ACTIVE,
            ],
        ];

        foreach ($mechanics as $mechanic) {
            Mechanic::firstOrCreate(
                ['code' => $mechanic['code']],
                $mechanic
            );
        }
    }
}
