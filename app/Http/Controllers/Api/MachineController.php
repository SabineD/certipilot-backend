<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMachineRequest;
use App\Http\Requests\UpdateMachineRequest;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Lijst van machines (per company)
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $machines = Machine::where('company_id', $company->id)
            ->where('is_active', true)
            ->with([
                'site:id,name',
                'latestInspection' => fn ($query) => $query->select(
                    'inspections.id',
                    'inspections.machine_id',
                    'inspections.inspected_at',
                    'inspections.valid_until'
                ),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Machine $machine) => $this->formatMachine($machine));

        return response()->json($machines);
    }

    /**
     * Detail van 1 machine
     */
    public function show(Request $request, string $id)
    {
        $company = $request->user()->company;

        $machine = Machine::where('company_id', $company->id)
            ->where('id', $id)
            ->with([
                'site:id,name',
                'latestInspection' => fn ($query) => $query->select(
                    'inspections.id',
                    'inspections.machine_id',
                    'inspections.inspected_at',
                    'inspections.valid_until'
                ),
            ])
            ->firstOrFail();

        return response()->json($this->formatMachine($machine));
    }

    /**
     * Machine aanmaken
     */
    public function store(StoreMachineRequest $request)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $machine = new Machine();
        $machine->company_id = $company->id;
        $machine->site_id = $data['site_id'] ?? null;
        $machine->name = $data['name'];
        $machine->type = $data['type'];
        $machine->serial_number = $data['serial_number'] ?? null;
        $machine->is_active = true;
        $machine->save();

        $machine->load('site:id,name');

        return response()->json($this->formatMachine($machine), 201);
    }

    /**
     * Machine bijwerken
     */
    public function update(UpdateMachineRequest $request, string $id)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $machine = Machine::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $machine->site_id = $data['site_id'] ?? null;
        $machine->name = $data['name'];
        $machine->type = $data['type'];
        $machine->serial_number = $data['serial_number'] ?? null;
        $machine->save();

        $machine->load('site:id,name');

        return response()->json($this->formatMachine($machine));
    }

    /**
     * Machine uitschakelen (soft delete)
     */
    public function destroy(Request $request, string $id)
    {
        $company = $request->user()->company;

        $machine = Machine::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $machine->is_active = false;
        $machine->save();

        return response()->noContent();
    }

    private function formatMachine(Machine $machine): array
    {
        return [
            'id' => $machine->id,
            'name' => $machine->name,
            'type' => $machine->type,
            'serial_number' => $machine->serial_number,
            'is_active' => (bool) $machine->is_active,
            'last_inspection' => $machine->lastInspectionDate(),
            'valid_until' => $machine->validUntilDate(),
            'site' => $machine->site
                ? [
                    'id' => $machine->site->id,
                    'name' => $machine->site->name,
                ]
                : null,
            'status' => $machine->inspectionStatus(),
        ];
    }
}
