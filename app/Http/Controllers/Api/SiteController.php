<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Lijst van werven (per company)
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $sites = Site::where('company_id', $company->id)
            ->where('is_active', true)
            ->withCount(['machines', 'employees'])
            ->orderBy('name')
            ->get()
            ->map(fn (Site $site) => $this->formatSiteSummary($site));

        return response()->json($sites);
    }

    /**
     * Detail van 1 werf
     */
    public function show(Request $request, string $id)
    {
        $company = $request->user()->company;

        $site = Site::where('company_id', $company->id)
            ->where('id', $id)
            ->with([
                'employees' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name')->with([
                    'latestCertificate' => fn ($certificateQuery) => $certificateQuery->select(
                        'certificates.id',
                        'certificates.employee_id',
                        'certificates.valid_until'
                    ),
                ]),
                'machines' => fn ($query) => $query->orderBy('name')->with([
                    'latestInspection' => fn ($inspectionQuery) => $inspectionQuery->select(
                        'inspections.id',
                        'inspections.machine_id',
                        'inspections.inspected_at',
                        'inspections.valid_until'
                    ),
                ]),
            ])
            ->firstOrFail();

        return response()->json($this->formatSiteDetail($site));
    }

    /**
     * Werf aanmaken
     */
    public function store(StoreSiteRequest $request)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $site = new Site();
        $site->company_id = $company->id;
        $site->name = $data['name'];
        $site->address = $data['address'] ?? null;
        $site->start_date = $data['start_date'] ?? null;
        $site->end_date = $data['end_date'] ?? null;
        $site->is_active = true;
        $site->save();

        $site->loadCount(['machines', 'employees']);

        return response()->json($this->formatSiteSummary($site), 201);
    }

    /**
     * Werf bijwerken
     */
    public function update(UpdateSiteRequest $request, string $id)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $site = Site::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $site->name = $data['name'];
        $site->address = $data['address'] ?? null;
        $site->start_date = $data['start_date'] ?? null;
        $site->end_date = $data['end_date'] ?? null;
        $site->save();

        $site->loadCount(['machines', 'employees']);

        return response()->json($this->formatSiteSummary($site));
    }

    /**
     * Werf uitschakelen (soft delete)
     */
    public function destroy(Request $request, string $id)
    {
        $company = $request->user()->company;

        $site = Site::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $site->is_active = false;
        $site->save();

        return response()->noContent();
    }

    private function formatSiteSummary(Site $site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'is_active' => (bool) $site->is_active,
            'machines_count' => $site->machines_count ?? 0,
            'employees_count' => $site->employees_count ?? 0,
            'status' => 'compliant',
            'meta' => [
                'address' => $site->address,
                'start_date' => optional($site->start_date)?->toDateString(),
                'end_date' => optional($site->end_date)?->toDateString(),
            ],
        ];
    }

    private function formatSiteDetail(Site $site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'is_active' => (bool) $site->is_active,
            'status' => 'compliant',
            'machines' => $site->machines->map(function ($machine) {
                return [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'last_inspection' => $machine->lastInspectionDate(),
                    'valid_until' => $machine->validUntilDate(),
                    'status' => $machine->inspectionStatus(),
                ];
            })->values(),
            'employees' => $site->employees->map(function ($employee) {
                $fullName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                if ($fullName === '') {
                    $fullName = $employee->name ?? '';
                }

                return [
                    'id' => $employee->id,
                    'full_name' => $fullName,
                    'job_title' => $employee->job_title,
                    'status' => $employee->certificateStatus(),
                ];
            })->values(),
            'meta' => [
                'address' => $site->address,
                'start_date' => optional($site->start_date)?->toDateString(),
                'end_date' => optional($site->end_date)?->toDateString(),
            ],
        ];
    }
}
