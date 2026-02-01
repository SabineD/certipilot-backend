<?php

namespace App\Jobs;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Throwable;

class SendUserInvitationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $userId
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user || !$user->email) {
            return;
        }

        if ($user->password !== null) {
            return;
        }

        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new UserInvitationMail($user, $token));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendUserInvitationMail failed', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
