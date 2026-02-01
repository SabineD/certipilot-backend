<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Models\Certificate;
use App\Models\Employee;
use App\Services\CertificateComplianceService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Lijst van attesten voor een werknemer
     */
    public function index(Request $request, string $employeeId)
    {
        $company = $request->user()->company;

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $employeeId)
            ->firstOrFail();

        $certificates = Certificate::where('company_id', $company->id)
            ->where('employee_id', $employee->id)
            ->orderBy('valid_until', 'desc')
            ->get()
            ->map(fn (Certificate $certificate) => $this->formatCertificate($certificate));

        return response()->json($certificates);
    }

    /**
     * Detail van 1 attest
     */
    public function show(Request $request, string $id)
    {
        $company = $request->user()->company;

        $certificate = Certificate::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($this->formatCertificate($certificate));
    }

    /**
     * Attest aanmaken
     */
    public function store(StoreCertificateRequest $request, string $employeeId, CertificateComplianceService $complianceService)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $employeeId)
            ->firstOrFail();

        $previousStatus = $employee->certificateStatus();

        $certificate = new Certificate();
        $certificate->company_id = $company->id;
        $certificate->employee_id = $employee->id;
        $certificate->certificate_type = $data['certificate_type'];
        $certificate->issued_at = $data['issued_at'];
        $certificate->valid_until = $data['valid_until'];
        $certificate->save();

        $employee->refresh();
        $complianceService->handleEmployee($employee, $previousStatus);

        return response()->json($this->formatCertificate($certificate), 201);
    }

    /**
     * Attest bijwerken
     */
    public function update(UpdateCertificateRequest $request, string $id, CertificateComplianceService $complianceService)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $certificate = Certificate::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $certificate->employee_id)
            ->first();
        $previousStatus = $employee?->certificateStatus();

        $certificate->certificate_type = $data['certificate_type'];
        $certificate->issued_at = $data['issued_at'];
        $certificate->valid_until = $data['valid_until'];
        $certificate->save();

        if ($employee) {
            $employee->refresh();
            $complianceService->handleEmployee($employee, $previousStatus);
        }

        return response()->json($this->formatCertificate($certificate));
    }

    /**
     * Attest verwijderen
     */
    public function destroy(Request $request, string $id, CertificateComplianceService $complianceService)
    {
        $company = $request->user()->company;

        $certificate = Certificate::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $employee = Employee::where('company_id', $company->id)
            ->where('id', $certificate->employee_id)
            ->first();
        $previousStatus = $employee?->certificateStatus();

        $certificate->delete();

        if ($employee) {
            $employee->refresh();
            $complianceService->handleEmployee($employee, $previousStatus);
        }

        return response()->noContent();
    }

    private function formatCertificate(Certificate $certificate): array
    {
        return [
            'id' => $certificate->id,
            'certificate_type' => $certificate->certificate_type,
            'issued_at' => $certificate->issued_at?->toDateString(),
            'valid_until' => $certificate->valid_until?->toDateString(),
        ];
    }
}
