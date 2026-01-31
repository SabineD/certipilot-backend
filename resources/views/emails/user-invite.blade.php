@component('mail::message')
Hallo {{ $user->name }},

Je CertiPilot account is aangemaakt. Stel hieronder je wachtwoord in om in te loggen.

@component('mail::button', ['url' => $url])
Wachtwoord instellen
@endcomponent

Als je dit niet verwachtte, kan je deze e-mail negeren.
@endcomponent
