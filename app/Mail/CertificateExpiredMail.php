<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateExpiredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employee $employee,
        public Certificate $certificate
    ) {
    }

    public function build(): self
    {
        $baseUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $baseUrl . '/employees/' . $this->employee->id;

        return $this->subject('Certificaat verlopen – actie vereist')
            ->markdown('emails.certificate-expired', [
                'employee' => $this->employee,
                'certificate' => $this->certificate,
                'url' => $url,
            ]);
    }
}
