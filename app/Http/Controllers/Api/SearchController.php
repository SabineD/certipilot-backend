<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Machine;
use App\Models\Site;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Globale autocomplete zoekopdracht
     */
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'message' => 'Query must be at least 2 characters.',
            ], 400);
        }

        $company = $request->user()->company;
        $like = '%' . $query . '%';

        $machines = Machine::where('company_id', $company->id)
            ->where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhere('serial_number', 'like', $like);
            })
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name']);

        $employees = Employee::where('company_id', $company->id)
            ->where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("concat(first_name, ' ', last_name) like ?", [$like]);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'name']);

        $sites = Site::where('company_id', $company->id)
            ->where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('address', 'like', $like);
            })
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name']);

        $results = collect()
            ->merge($machines->map(function (Machine $machine) {
                return [
                    'type' => 'machine',
                    'id' => $machine->id,
                    'label' => $machine->name,
                    'subLabel' => 'Machine',
                    'url' => '/machines/' . $machine->id,
                ];
            }))
            ->merge($employees->map(function (Employee $employee) {
                $label = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                if ($label === '') {
                    $label = $employee->name ?? '';
                }

                return [
                    'type' => 'employee',
                    'id' => $employee->id,
                    'label' => $label,
                    'subLabel' => 'Werknemer',
                    'url' => '/employees/' . $employee->id,
                ];
            }))
            ->merge($sites->map(function (Site $site) {
                return [
                    'type' => 'site',
                    'id' => $site->id,
                    'label' => $site->name,
                    'subLabel' => 'Werf',
                    'url' => '/werven/' . $site->id,
                ];
            }))
            ->values()
            ->all();

        return response()->json($results);
    }
}
