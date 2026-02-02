<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Requests\UpdateInspectionRequest;
use App\Models\Inspection;
use App\Models\Machine;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    /**
     * Lijst van inspecties voor een machine
     */
    public function index(Request $request, string $machineId)
    {
        $company = $request->user()->company;

        $machine = Machine::where('company_id', $company->id)
            ->where('id', $machineId)
            ->firstOrFail();

        $inspections = Inspection::where('company_id', $company->id)
            ->where('machine_id', $machine->id)
            ->orderBy('inspected_at', 'desc')
            ->get()
            ->map(fn (Inspection $inspection) => $this->formatInspection($inspection));

        return response()->json($inspections);
    }

    /**
     * Detail van 1 inspectie
     */
    public function show(Request $request, string $id)
    {
        $company = $request->user()->company;

        $inspection = Inspection::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($this->formatInspection($inspection));
    }

    /**
     * Inspectie aanmaken
     */
    public function store(StoreInspectionRequest $request, string $machineId)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $machine = Machine::where('company_id', $company->id)
            ->where('id', $machineId)
            ->firstOrFail();

        $inspection = new Inspection();
        $inspection->company_id = $company->id;
        $inspection->machine_id = $machine->id;
        $inspection->inspection_type = $data['inspection_type'];
        $inspection->inspected_at = $data['inspected_at'];
        $inspection->valid_until = $data['valid_until'];
        $inspection->save();

        return response()->json($this->formatInspection($inspection), 201);
    }

    /**
     * Inspectie bijwerken
     */
    public function update(UpdateInspectionRequest $request, string $id)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $inspection = Inspection::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $inspection->inspection_type = $data['inspection_type'];
        $inspection->inspected_at = $data['inspected_at'];
        $inspection->valid_until = $data['valid_until'];
        $inspection->save();

        return response()->json($this->formatInspection($inspection));
    }

    /**
     * Inspectie verwijderen
     */
    public function destroy(Request $request, string $id)
    {
        $company = $request->user()->company;

        $inspection = Inspection::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $inspection->delete();

        return response()->noContent();
    }

    private function formatInspection(Inspection $inspection): array
    {
        return [
            'id' => $inspection->id,
            'inspection_type' => $inspection->inspection_type,
            'inspected_at' => $inspection->inspected_at?->toDateString(),
            'valid_until' => $inspection->valid_until?->toDateString(),
            'status' => $inspection->status(),
        ];
    }
}
