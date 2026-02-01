@component('mail::message')
# Je CertiPilot account is aangemaakt

Hallo {{ $user->name }},

Je account is aangemaakt. Stel hieronder je wachtwoord in om in te loggen.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Wachtwoord instellen
@endcomponent

Als de knop niet werkt, gebruik dan deze link: {{ $url }}

Bedankt,<br>
CertiPilot
@endcomponent
