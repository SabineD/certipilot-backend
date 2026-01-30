<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SiteComplianceService;
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
            ->withCount(['employees', 'machines'])
            ->orderBy('name')
            ->get()
            ->map(function (Site $site) {
                return [
                    'id' => $site->id,
                    'name' => $site->name,
                    'address' => $site->address,
                    'employees_count' => $site->employees_count,
                    'machines_count' => $site->machines_count,
                ];
            });

        return response()->json($sites);
    }

    /**
     * Detail van 1 werf
     */
    public function show(Request $request, string $id, SiteComplianceService $complianceService)
    {
        $company = $request->user()->company;

        $site = Site::where('company_id', $company->id)
            ->where('id', $id)
            ->with([
                'employees' => fn ($query) => $query->orderBy('name')->with(['inspections', 'certificates']),
                'machines' => fn ($query) => $query->orderBy('name')->with(['inspections', 'certificates']),
            ])
            ->firstOrFail();

        return response()->json(
            $complianceService->buildSiteDetail($site)
        );
    }
}