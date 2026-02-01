<?php

namespace App\Services;

use App\Jobs\SendCertificateExpiredMail;
use App\Jobs\SendCertificateExpiringSoonMail;
use App\Models\Certificate;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class CertificateComplianceService
{
    public int $warningDays = 30;

    public function handleEmployee(Employee $employee, ?string $previousStatus = null): ?string
    {
        if ($employee->is_active === false) {
            return null;
        }

        $currentStatus = $this->evaluateStatus($employee);

        if ($currentStatus === null) {
            return null;
        }

        if ($previousStatus !== null && $previousStatus === $currentStatus) {
            return $currentStatus;
        }

        if (!in_array($currentStatus, ['warning', 'expired'], true)) {
            return $currentStatus;
        }

        $certificate = $this->latestCertificate($employee);
        if (!$certificate) {
            return $currentStatus;
        }

        if ($currentStatus === 'expired') {
            SendCertificateExpiredMail::dispatch($employee->id, $certificate->id);
        }

        if ($currentStatus === 'warning') {
            SendCertificateExpiringSoonMail::dispatch($employee->id, $certificate->id);
        }

        return $currentStatus;
    }

    private function evaluateStatus(Employee $employee): ?string
    {
        $jobTitle = $employee->job_title ? trim($employee->job_title) : '';
        if (strtolower($jobTitle) === 'preventieadviseur') {
            return null;
        }

        $certificate = $this->latestCertificate($employee);
        if (!$certificate || !$certificate->valid_until) {
            return 'expired';
        }

        $now = now();
        $validUntil = $certificate->valid_until instanceof Carbon
            ? $certificate->valid_until
            : Carbon::parse($certificate->valid_until);

        if ($validUntil->lt($now)) {
            return 'expired';
        }

        if ($validUntil->lte($now->copy()->addDays($this->warningDays))) {
            return 'warning';
        }

        return 'ok';
    }

    private function latestCertificate(Employee $employee): ?Certificate
    {
        return $employee->certificates()
            ->orderByDesc('valid_until')
            ->first();
    }
}
