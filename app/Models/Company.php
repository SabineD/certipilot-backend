<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'vat_number',
        'address',
        'postal_code',
        'city',
        'country',
        'email_notifications_enabled',
    ];

    protected $casts = [
        'email_notifications_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (Company $company): void {
            if (! Schema::hasTable('subscriptions')) {
                return;
            }

            if ($company->subscription()->exists()) {
                return;
            }

            $company->subscription()->create([
                'plan' => Subscription::PLAN_PROFESSIONAL,
                'status' => Subscription::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays((int) config('subscriptions.trial.days', 14)),
            ]);
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->status === Subscription::STATUS_ACTIVE || $this->onTrial();
    }

    public function onTrial(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->status === Subscription::STATUS_TRIAL
            && $subscription->trial_ends_at instanceof Carbon
            && $subscription->trial_ends_at->isFuture();
    }

    public function trialExpired(): bool
    {
        $subscription = $this->subscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->status === Subscription::STATUS_TRIAL
            && $subscription->trial_ends_at instanceof Carbon
            && $subscription->trial_ends_at->lte(now());
    }
}
