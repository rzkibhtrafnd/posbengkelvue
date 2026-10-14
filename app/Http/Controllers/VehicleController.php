<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\VehicleRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Models\Vehicle;
use App\Services\VehicleService;
use App\Support\PaginationOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService,
        protected CustomerService $customerService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeRead($request);

        $filters = $request->only(['search', 'brand', 'per_page']);
        $vehicles = $this->vehicleService->getFilteredVehicles(
            $request->only(['search', 'brand']),
            PaginationOptions::perPage($request),
        );

        $brands = $this->vehicleService->getAvailableBrands();

        return Inertia::render('Vehicles/Index', compact('vehicles', 'brands', 'filters'));
    }

    public function create(): Response
    {
        $this->authorizeAdmin();

        $customers = Customer::orderBy('name')->get();
        return Inertia::render('Vehicles/Create', compact('customers'));
    }

    public function store(CustomerRequest $customerRequest, VehicleRequest $vehicleRequest): RedirectResponse
    {
        $this->authorizeAdmin();

        $customerData = $customerRequest->validated();
        $vehicleData = $vehicleRequest->validated();

        DB::transaction(function () use (&$vehicleData, $customerData): void {
            if (empty($vehicleData['customer_id'])) {
                $vehicleData['customer_id'] = $this->customerService->createCustomer($customerData)->id;
            }

            $this->vehicleService->createVehicle($vehicleData);
        });

        return redirect()->route('admin.vehicles.index')->with('success', 'Data kendaraan & pemilik berhasil ditambahkan.');
    }

    public function show(Request $request, Vehicle $vehicle): Response
    {
        $this->authorizeRead($request);
        $vehicle->load('customer');

        $serviceHistory = $vehicle->transactions()
            ->with('items')
            ->latest('transaction_date')
            ->paginate(PaginationOptions::perPage($request))
            ->withQueryString();

        return Inertia::render('Vehicles/Show', compact('vehicle', 'serviceHistory'));
    }

    public function edit(Vehicle $vehicle): Response
    {
        $this->authorizeAdmin();

        $vehicle->load('customer');
        $customers = Customer::orderBy('name')->get();
        return Inertia::render('Vehicles/Edit', compact('vehicle', 'customers'));
    }

    public function update(CustomerRequest $customerRequest, VehicleRequest $vehicleRequest, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeAdmin();

        $customerData = $customerRequest->validated();
        $vehicleData = $vehicleRequest->validated();

        DB::transaction(function () use ($vehicle, &$vehicleData, $customerData): void {
            if (empty($vehicleData['customer_id'])) {
                $vehicleData['customer_id'] = $this->customerService->createCustomer($customerData)->id;
            }

            $this->vehicleService->updateVehicle($vehicle, $vehicleData);
        });

        return redirect()->route('admin.vehicles.index')->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeAdmin();

        $this->vehicleService->deleteVehicle($vehicle);
        return redirect()->route('admin.vehicles.index')->with('success', 'Data kendaraan berhasil dihapus.');
    }

    private function authorizeRead(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, [UserRole::ADMIN, UserRole::KASIR], true), 403, 'Akses ditolak.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(request()->user()?->role === UserRole::ADMIN, 403, 'Akses ditolak.');
    }
}
