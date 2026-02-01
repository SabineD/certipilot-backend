<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token
    ) {
    }

    public function build(): self
    {
        $baseUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $baseUrl . '/reset-password?token=' . urlencode($this->token) . '&email=' . urlencode($this->user->email);

        return $this->subject('Je CertiPilot account is aangemaakt')
            ->markdown('emails.user-invitation', [
                'user' => $this->user,
                'url' => $url,
            ]);
    }
}
