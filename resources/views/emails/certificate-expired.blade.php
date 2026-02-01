@component('mail::message')
# Certificaat verlopen – actie vereist

Hallo {{ $employee->first_name ?? $employee->name }},

Het certificaat "{{ $certificate->certificate_type }}" is verlopen op {{ $certificate->valid_until?->toDateString() }}.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Certificaat bekijken
@endcomponent

Als de knop niet werkt, gebruik dan deze link: {{ $url }}

Bedankt,<br>
CertiPilot
@endcomponent
