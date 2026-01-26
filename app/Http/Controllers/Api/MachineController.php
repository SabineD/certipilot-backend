<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        ->with([
            'site',
            'inspections',
            'certificates',
        ])
        ->orderBy('name')
        ->get()
        ->map(function (Machine $machine) {
            return [
                'id' => $machine->id,
                'name' => $machine->name,
                'type' => $machine->type,
                'site' => $machine->site,
                'status' => $this->calculateMachineStatus($machine),
            ];
        });

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
            'site',
            'inspections.inspectionType',
            'certificates.certificateType',
        ])
        ->firstOrFail();

    return response()->json([
        'machine' => $machine,
        'status' => $this->calculateMachineStatus($machine),
    ]);
    }

    private function calculateMachineStatus(Machine $machine): string
    {
    $now = now();

    // Verlopen?
    if (
        $machine->inspections->contains(fn ($i) => $i->expiry_date < $now) ||
        $machine->certificates->contains(fn ($c) => $c->expiry_date < $now)
    ) {
        return 'verlopen';
    }

    // Binnenkort? (30 dagen)
    if (
        $machine->inspections->contains(fn ($i) => $i->expiry_date < $now->copy()->addDays(30)) ||
        $machine->certificates->contains(fn ($c) => $c->expiry_date < $now->copy()->addDays(30))
    ) {
        return 'binnenkort';
    }

    return 'ok';
    }
}