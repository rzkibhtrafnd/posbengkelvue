<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleService
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function getFilteredVehicles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Vehicle::with('customer')
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getAvailableBrands(): Collection
    {
        return Vehicle::select('brand')
            ->distinct()
            ->whereNotNull('brand')
            ->orderBy('brand')
            ->get();
    }

    public function createVehicle(array $data): Vehicle
    {
        if (empty($data['customer_id'])) {
            $customer = $this->customerService->createCustomer($data);
            $data['customer_id'] = $customer->id;
        }

        $data['plate_number'] = strtoupper(trim($data['plate_number']));

        return Vehicle::create($data);
    }

    public function updateVehicle(Vehicle $vehicle, array $data): bool
    {
        if (!empty($data['customer_id'])) {
            $vehicle->customer->update([
                'name'    => $data['customer_name'] ?? $vehicle->customer->name,
                'phone'   => $data['customer_phone'] ?? $vehicle->customer->phone,
                'address' => $data['customer_address'] ?? $vehicle->customer->address,
            ]);
        }

        $data['plate_number'] = strtoupper(trim($data['plate_number']));
        return $vehicle->update($data);
    }

    public function deleteVehicle(Vehicle $vehicle): bool
    {
        return $vehicle->delete();
    }
}