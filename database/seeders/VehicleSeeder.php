<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CUS-001',
                'name' => 'Andi Pratama',
                'phone' => '081234567811',
                'address' => 'Jl. Merdeka No. 10',
            ],
            [
                'code' => 'CUS-002',
                'name' => 'Siti Rahmawati',
                'phone' => '081234567812',
                'address' => 'Jl. Diponegoro No. 15',
            ],
            [
                'code' => 'CUS-003',
                'name' => 'Dedi Kurniawan',
                'phone' => '081234567813',
                'address' => 'Jl. Pahlawan No. 8',
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::firstOrCreate(
                ['code' => $customerData['code']],
                $customerData
            );

            foreach ($this->vehiclesForCustomer($customerData['code']) as $vehicleData) {
                Vehicle::firstOrCreate(
                    ['plate_number' => $vehicleData['plate_number']],
                    ['customer_id' => $customer->id, ...$vehicleData]
                );
            }
        }
    }

    /**
     * @return array<int, array{plate_number: string, brand: string, model: string, year: string, color: string}>
     */
    private function vehiclesForCustomer(string $customerCode): array
    {
        return match ($customerCode) {
            'CUS-001' => [
                ['plate_number' => 'S 1234 AB', 'brand' => 'Honda', 'model' => 'Vario 125', 'year' => '2022', 'color' => 'Hitam'],
            ],
            'CUS-002' => [
                ['plate_number' => 'S 2345 CD', 'brand' => 'Yamaha', 'model' => 'NMAX 155', 'year' => '2023', 'color' => 'Biru'],
            ],
            'CUS-003' => [
                ['plate_number' => 'S 3456 EF', 'brand' => 'Honda', 'model' => 'Beat Street', 'year' => '2021', 'color' => 'Merah'],
                ['plate_number' => 'S 4567 GH', 'brand' => 'Suzuki', 'model' => 'Nex II', 'year' => '2020', 'color' => 'Putih'],
            ],
        };
    }
}
