<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
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
            ->where('is_active', true)
            ->with([
                'site:id,name',
                'latestCertificate' => fn ($query) => $query->select(
                    'certificates.id',
                    'certificates.employee_id',
                    'certificates.valid_until'
                ),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn (Employee $employee) => $this->formatEmployee($employee));

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
                'site:id,name',
                'latestCertificate' => fn ($query) => $query->select(
                    'certificates.id',
                    'certificates.employee_id',
                    'certificates.valid_until'
                ),
            ])
            ->firstOrFail();

        return response()->json($this->formatEmployee($employee));
    }

    /**
     * Werknemer aanmaken
     */
    public function store(StoreEmployeeRequest $request)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $employee = new Employee();
        $employee->company_id = $company->id;
        $employee->site_id = $data['site_id'] ?? null;
        $employee->first_name = $data['first_name'];
        $employee->last_name = $data['last_name'];
        $employee->job_title = $data['job_title'];
        $employee->email = $data['email'] ?? null;
        $employee->is_active = true;
        $employee->name = $this->fullName($employee);
        $employee->save();

        $employee->load('site:id,name');

        return response()->json($this->formatEmployee($employee), 201);
    }

    /**
     * Werknemer bijwerken
     */
    public function update(UpdateEmployeeRequest $request, string $id)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $employee->site_id = $data['site_id'] ?? null;
        $employee->first_name = $data['first_name'];
        $employee->last_name = $data['last_name'];
        $employee->job_title = $data['job_title'];
        $employee->email = $data['email'] ?? null;
        $employee->name = $this->fullName($employee);
        $employee->save();

        $employee->load('site:id,name');

        return response()->json($this->formatEmployee($employee));
    }

    /**
     * Werknemer uitschakelen (soft delete)
     */
    public function destroy(Request $request, string $id)
    {
        $company = $request->user()->company;

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $employee->is_active = false;
        $employee->save();

        return response()->noContent();
    }

    private function fullName(Employee $employee): string
    {
        return trim($employee->first_name . ' ' . $employee->last_name);
    }

    private function formatEmployee(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'full_name' => $this->fullName($employee),
            'job_title' => $employee->job_title,
            'email' => $employee->email,
            'is_active' => (bool) $employee->is_active,
            'site' => $employee->site
                ? [
                    'id' => $employee->site->id,
                    'name' => $employee->site->name,
                ]
                : null,
            'status' => $employee->certificateStatus(),
        ];
    }
}
