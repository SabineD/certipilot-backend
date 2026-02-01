<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateExpiringSoonMail extends Mailable implements ShouldQueue
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

        return $this->subject('Certificaat verloopt binnenkort')
            ->markdown('emails.certificate-expiring-soon', [
                'employee' => $this->employee,
                'certificate' => $this->certificate,
                'url' => $url,
            ]);
    }
}
