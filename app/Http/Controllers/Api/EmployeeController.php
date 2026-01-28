<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Lijst van werknemers (per company)
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $employees = Employee::where('company_id', $company->id)
            ->with([
                'site',
                'inspections',
                'certificates',
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Employee $employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'site' => $employee->site,
                    'status' => $this->calculateEmployeeStatus($employee),
                ];
            });

        return response()->json($employees);
    }

    /**
     * Detail van 1 werknemer
     */
    public function show(Request $request, string $id)
    {
        $company = $request->user()->company;

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $id)
            ->with([
                'site',
                'inspections.inspectionType',
                'certificates.certificateType',
            ])
            ->firstOrFail();

        return response()->json([
            'employee' => $employee,
            'status' => $this->calculateEmployeeStatus($employee),
        ]);
    }

    private function calculateEmployeeStatus(Employee $employee): string
    {
        $now = now();

        // Verlopen?
        if (
            $employee->inspections->contains(fn ($i) => $i->expiry_date < $now) ||
            $employee->certificates->contains(fn ($c) => $c->expiry_date < $now)
        ) {
            return 'verlopen';
        }

        // Binnenkort? (30 dagen)
        if (
            $employee->inspections->contains(fn ($i) => $i->expiry_date < $now->copy()->addDays(30)) ||
            $employee->certificates->contains(fn ($c) => $c->expiry_date < $now->copy()->addDays(30))
        ) {
            return 'binnenkort';
        }

        return 'ok';
    }
}
