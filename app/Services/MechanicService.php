<?php

namespace App\Services;

use App\Models\Mechanic;
use Illuminate\Pagination\LengthAwarePaginator;

class MechanicService
{
    public function getAllMechanics(int $perPage = 10): LengthAwarePaginator
    {
        return Mechanic::latest()->paginate($perPage);
    }

    public function generateCode(): string
    {
        $prefix = 'MTR-';
        $latestMechanic = Mechanic::where('code', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestMechanic) {
            return $prefix . '0001';
        }

        $number = (int) substr($latestMechanic->code, strlen($prefix));
        return $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }

    public function createMechanic(array $data): Mechanic
    {
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode();
        }

        return Mechanic::create($data);
    }

    public function updateMechanic(Mechanic $mechanic, array $data): bool
    {
        return $mechanic->update($data);
    }

    public function deleteMechanic(Mechanic $mechanic): bool
    {
        return $mechanic->delete();
    }
}