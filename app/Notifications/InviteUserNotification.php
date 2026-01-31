<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class InviteUserNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Je CertiPilot account is aangemaakt')
            ->line('Je account is aangemaakt. Stel hieronder je wachtwoord in om in te loggen.')
            ->action('Wachtwoord instellen', $url)
            ->line('Als je dit niet verwachtte, kan je deze e-mail negeren.');
    }
}
