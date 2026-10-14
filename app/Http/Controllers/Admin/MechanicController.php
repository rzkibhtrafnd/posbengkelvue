<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MechanicRequest;
use App\Models\Mechanic;
use App\Services\MechanicService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Support\PaginationOptions;

class MechanicController extends Controller
{
    public function __construct(
        protected MechanicService $mechanicService
    ) {}

    public function index(Request $request): Response
    {
        $mechanics = $this->mechanicService->getAllMechanics(PaginationOptions::perPage($request));
        return Inertia::render('Admin/Mechanics/Index', [
            'mechanics' => $mechanics,
        ]);
    }

    public function create(): Response
    {
        $generatedCode = $this->mechanicService->generateCode();
        return Inertia::render('Admin/Mechanics/Create', [
            'generatedCode' => $generatedCode,
        ]);
    }

    public function store(MechanicRequest $request): RedirectResponse
    {
        $this->mechanicService->createMechanic($request->validated());
        return redirect()->route('admin.mechanics.index')->with('success', 'Data montir berhasil ditambahkan.');
    }

    public function show(Mechanic $mechanic): Response
    {
        return Inertia::render('Admin/Mechanics/Show', [
            'mechanic' => $mechanic,
        ]);
    }

    public function edit(Mechanic $mechanic): Response
    {
        return Inertia::render('Admin/Mechanics/Edit', [
            'mechanic' => $mechanic,
        ]);
    }

    public function update(MechanicRequest $request, Mechanic $mechanic): RedirectResponse
    {
        $this->mechanicService->updateMechanic($mechanic, $request->validated());
        return redirect()->route('admin.mechanics.index')->with('success', 'Data montir berhasil diperbarui.');
    }

    public function destroy(Mechanic $mechanic): RedirectResponse
    {
        $this->mechanicService->deleteMechanic($mechanic);
        return redirect()->route('admin.mechanics.index')->with('success', 'Data montir berhasil dihapus.');
    }
}