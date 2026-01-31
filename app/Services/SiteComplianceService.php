<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Machine;
use App\Models\Site;
use App\Models\SiteDocument;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SiteComplianceService
{
    public function buildSiteDetail(Site $site): array
    {
        $employees = $site->employees ?? collect();
        $machines = $site->machines ?? collect();
        $documents = $site->siteDocuments ?? collect();

        $employeeItems = $employees->map(fn (Employee $employee) => $this->formatEmployee($employee));
        $machineItems = $machines->map(fn (Machine $machine) => $this->formatMachine($machine));

        $employeeSummary = $this->countCompliance($employeeItems);
        $machineSummary = $this->countCompliance($machineItems);
        $certificateSummary = $this->countCertificates($employees, $machines);

        return [
            'id' => $site->id,
            'name' => $site->name,
            'status' => $this->aggregateStatus($employeeItems, $machineItems),
            'complianceSummary' => [
                'employees' => $employeeSummary,
                'machines' => $machineSummary,
                'certificates' => $certificateSummary,
            ],
            'employees' => $employeeItems->values()->all(),
            'machines' => $machineItems->values()->all(),
            'documents' => $this->normalizeDocuments($documents)->values()->all(),
            'meta' => [
                'address' => $site->address,
                'siteManager' => null,
                'startDate' => null,
                'endDate' => null,
            ],
        ];
    }

    private function normalizeDocuments(Collection $documents): Collection
    {
        return $documents->map(function (SiteDocument $document) {
            return [
                'id' => $document->id,
                'name' => $document->name,
                'type' => $document->type,
                'status' => $document->status,
                'uploadedAt' => $document->uploaded_at?->toDateString(),
            ];
        });
    }

    private function formatEmployee(Employee $employee): array
    {
        $dates = $this->collectExpiryDates(
            collect($employee->inspections),
            collect($employee->certificates)
        );
        $status = $this->statusFromDates($dates);

        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'role' => $employee->job_title,
            'status' => $status,
            'issue' => $this->issueForStatus($status),
        ];
    }

    private function formatMachine(Machine $machine): array
    {
        $dates = $this->collectExpiryDates(
            collect($machine->inspections),
            collect($machine->certificates)
        );
        $status = $this->statusFromDates($dates);

        return [
            'id' => $machine->id,
            'name' => $machine->name,
            'type' => $machine->type,
            'lastInspection' => $this->latestInspectionDate(collect($machine->inspections)),
            'validUntil' => $this->earliestExpiryDate($dates),
            'status' => $status,
        ];
    }

    private function countCompliance(Collection $items): array
    {
        $total = $items->count();
        $compliant = $items->where('status', 'compliant')->count();

        return [
            'total' => $total,
            'compliant' => $compliant,
            'nonCompliant' => $total - $compliant,
        ];
    }

    private function countCertificates(Collection $employees, Collection $machines): array
    {
        $now = now();
        $dates = collect()
            ->merge($employees->flatMap(fn (Employee $employee) => $this->collectExpiryDates(
                collect($employee->inspections),
                collect($employee->certificates)
            )))
            ->merge($machines->flatMap(fn (Machine $machine) => $this->collectExpiryDates(
                collect($machine->inspections),
                collect($machine->certificates)
            )))
            ->filter();

        $expired = $dates->filter(fn (Carbon $date) => $date->lt($now))->count();
        $expiringSoon = $dates->filter(fn (Carbon $date) => $date->gte($now) && $date->lte($now->copy()->addDays(30)))->count();

        return [
            'expired' => $expired,
            'expiringSoon' => $expiringSoon,
        ];
    }

    private function aggregateStatus(Collection $employeeItems, Collection $machineItems): string
    {
        $all = $employeeItems->merge($machineItems);

        if ($all->contains(fn (array $item) => $item['status'] === 'non_compliant')) {
            return 'non_compliant';
        }

        if ($all->contains(fn (array $item) => $item['status'] === 'attention_required')) {
            return 'attention_required';
        }

        return 'compliant';
    }

    private function statusFromDates(Collection $dates): string
    {
        $now = now();
        $validDates = $dates->filter();

        if ($validDates->contains(fn (Carbon $date) => $date->lt($now))) {
            return 'non_compliant';
        }

        if ($validDates->contains(fn (Carbon $date) => $date->gte($now) && $date->lte($now->copy()->addDays(30)))) {
            return 'attention_required';
        }

        return 'compliant';
    }

    private function collectExpiryDates(Collection $inspections, Collection $certificates): Collection
    {
        return $inspections
            ->pluck('valid_until')
            ->merge($certificates->pluck('expiry_date'))
            ->map(fn ($date) => $this->toCarbon($date))
            ->filter();
    }

    private function latestInspectionDate(Collection $inspections): ?string
    {
        $latest = $inspections
            ->pluck('inspected_at')
            ->map(fn ($date) => $this->toCarbon($date))
            ->filter()
            ->sortDesc()
            ->first();

        return $latest?->toDateString();
    }

    private function earliestExpiryDate(Collection $dates): ?string
    {
        $earliest = $dates->filter()->sort()->first();

        return $earliest?->toDateString();
    }

    private function issueForStatus(string $status): ?string
    {
        return match ($status) {
            'non_compliant' => 'Inspection or certificate expired',
            'attention_required' => 'Inspection or certificate expiring soon',
            default => null,
        };
    }

    private function toCarbon($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }
}
