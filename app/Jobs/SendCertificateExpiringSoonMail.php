<?php

namespace App\Jobs;

use App\Mail\CertificateExpiringSoonMail;
use App\Models\Certificate;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCertificateExpiringSoonMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $employeeId,
        public string $certificateId
    ) {
    }

    public function handle(): void
    {
        $employee = Employee::find($this->employeeId);
        $certificate = Certificate::find($this->certificateId);

        if (!$employee || !$certificate) {
            return;
        }

        if ($certificate->employee_id !== $employee->id) {
            return;
        }

        if (!$employee->email) {
            return;
        }

        if ($employee->is_active === false) {
            return;
        }

        $jobTitle = $employee->job_title ? trim($employee->job_title) : '';
        if (strtolower($jobTitle) === 'preventieadviseur') {
            return;
        }

        Mail::to($employee->email)->send(new CertificateExpiringSoonMail($employee, $certificate));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendCertificateExpiringSoonMail failed', [
            'employee_id' => $this->employeeId,
            'certificate_id' => $this->certificateId,
            'error' => $exception->getMessage(),
        ]);
    }
}
