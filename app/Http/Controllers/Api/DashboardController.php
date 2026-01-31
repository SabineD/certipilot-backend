<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Employee;
use App\Models\Alert;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $machines = Machine::where('company_id', $company->id)
            ->with(['inspections', 'certificates'])
            ->get();

        $machineStats = [
            'total' => $machines->count(),
            'ok' => 0,
            'warning' => 0,
            'expired' => 0,
        ];

        $machineStatuses = [];
        foreach ($machines as $machine) {
            $status = $this->calculateMachineStatus($machine);
            $machineStatuses[$machine->id] = $status;
            $machineStats[$status]++;
        }

        $alerts = Alert::where('company_id', $company->id);

        $employees = Employee::where('company_id', $company->id)
            ->with(['certificates', 'inspections'])
            ->get();

        $employeeStats = [
            'total' => $employees->count(),
            'ok' => 0,
            'warning' => 0,
            'expired' => 0,
        ];

        $employeeStatuses = [];
        foreach ($employees as $employee) {
            $status = $this->calculateEmployeeStatus($employee);
            $employeeStatuses[$employee->id] = $status;
            $employeeStats[$status]++;
        }

        $problemMachines = $machines
            ->filter(fn ($machine) => in_array($machineStatuses[$machine->id], ['expired', 'warning']))
            ->map(fn ($machine) => [
                'id' => $machine->id,
                'name' => $machine->name,
                'status' => $machineStatuses[$machine->id],
                'due_date' => $this->calculateMachineDueDate($machine),
            ])
            ->values();

        $problemEmployees = $employees
            ->filter(fn ($employee) => in_array($employeeStatuses[$employee->id], ['expired', 'warning']))
            ->map(fn ($employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'status' => $employeeStatuses[$employee->id],
                'due_date' => $this->calculateEmployeeDueDate($employee),
            ])
            ->values();

        $actionRequired = $problemMachines
            ->map(function (array $machine) {
                return [
                    'type' => 'machine',
                    'id' => $machine['id'],
                    'name' => $machine['name'],
                    'issue' => $machine['status'] === 'expired'
                        ? 'Inspection or certificate expired'
                        : 'Inspection or certificate expiring soon',
                    'status' => $machine['status'],
                    'due_date' => $machine['due_date'],
                ];
            })
            ->toBase()
            ->merge($problemEmployees->map(function (array $employee) {
                return [
                    'type' => 'employee',
                    'id' => $employee['id'],
                    'name' => $employee['name'],
                    'issue' => $employee['status'] === 'expired'
                        ? 'Inspection or certificate expired'
                        : 'Inspection or certificate expiring soon',
                    'status' => $employee['status'],
                    'due_date' => $employee['due_date'],
                ];
            }))
            ->values();

        return response()->json([
            'summary' => [
                'machines' => $machineStats,
                'employees' => $employeeStats,
                'alerts' => [
                    'open' => (clone $alerts)->where('resolved', false)->count(),
                ],
            ],
            'action_required' => $actionRequired,
            'status_breakdown' => [
                'machines' => [
                    'ok' => $machineStats['ok'],
                    'warning' => $machineStats['warning'],
                    'expired' => $machineStats['expired'],
                ],
                'employees' => [
                    'ok' => $employeeStats['ok'],
                    'warning' => $employeeStats['warning'],
                    'expired' => $employeeStats['expired'],
                ],
            ],
        ]);
    }

    private function calculateMachineStatus(Machine $machine): string
    {
        $now = now();

        if (
            $machine->inspections->contains(fn ($i) => $i->valid_until < $now) ||
            $machine->certificates->contains(fn ($c) => $c->valid_until < $now)
        ) {
            return 'expired';
        }

        if (
            $machine->inspections->contains(fn ($i) => $i->valid_until < $now->copy()->addDays(30)) ||
            $machine->certificates->contains(fn ($c) => $c->valid_until < $now->copy()->addDays(30))
        ) {
            return 'warning';
        }

        return 'ok';
    }

    private function calculateEmployeeStatus(Employee $employee): string
    {
        $now = now();

        if (
            $employee->inspections->contains(fn ($i) => $i->valid_until < $now) ||
            $employee->certificates->contains(fn ($c) => $c->valid_until < $now)
        ) {
            return 'expired';
        }

        if (
            $employee->inspections->contains(fn ($i) => $i->valid_until < $now->copy()->addDays(30)) ||
            $employee->certificates->contains(fn ($c) => $c->valid_until < $now->copy()->addDays(30))
        ) {
            return 'warning';
        }

        return 'ok';
    }

    private function calculateMachineDueDate(Machine $machine): ?string
    {
        $dates = collect()
            ->merge($machine->inspections->pluck('valid_until'))
            ->merge($machine->certificates->pluck('valid_until'))
            ->filter()
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse($date))
            ->sort();

        $soonest = $dates->first();

        return $soonest ? $soonest->toDateString() : null;
    }

    private function calculateEmployeeDueDate(Employee $employee): ?string
    {
        $dates = collect()
            ->merge($employee->inspections->pluck('valid_until'))
            ->merge($employee->certificates->pluck('valid_until'))
            ->filter()
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse($date))
            ->sort();

        $soonest = $dates->first();

        return $soonest ? $soonest->toDateString() : null;
    }
}
