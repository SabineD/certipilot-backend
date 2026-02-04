<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Subscription;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionService
{
    /**
     * Start a trial subscription for the company.
     */
    public function startTrial(Company $company): Subscription
    {
        return DB::transaction(function () use ($company): Subscription {
            $subscription = $company->subscription()->first();

            if (! $subscription) {
                $subscription = new Subscription();
                $subscription->company_id = $company->id;
            }

            $subscription->plan = (string) config('subscriptions.trial.plan', Subscription::PLAN_PROFESSIONAL);
            $subscription->status = Subscription::STATUS_TRIAL;
            $subscription->trial_ends_at = now()->addDays((int) config('subscriptions.trial.days', 14));
            $subscription->ends_at = null;
            $subscription->save();

            return $subscription->fresh();
        });
    }

    /**
     * Activate a paid plan for the company.
     */
    public function activatePlan(Company $company, string $plan): Subscription
    {
        if (! $this->planExists($plan)) {
            throw new InvalidArgumentException('Ongeldig abonnement.');
        }

        return DB::transaction(function () use ($company, $plan): Subscription {
            $subscription = $company->subscription()->first();

            if (! $subscription) {
                $subscription = new Subscription();
                $subscription->company_id = $company->id;
                $subscription->plan = $plan;
            }

            if ($this->planRank($plan) < $this->planRank($subscription->plan)) {
                $this->guardDowngradeLimits($company, $plan);
            }

            $subscription->plan = $plan;
            $subscription->status = Subscription::STATUS_ACTIVE;
            $subscription->trial_ends_at = null;
            $subscription->ends_at = null;
            $subscription->save();

            return $subscription->fresh();
        });
    }

    /**
     * Cancel the company's subscription.
     */
    public function cancelSubscription(Company $company): Subscription
    {
        return DB::transaction(function () use ($company): Subscription {
            $subscription = $company->subscription()->first();

            if (! $subscription) {
                throw new DomainException('Geen actief abonnement gevonden.');
            }

            $subscription->status = Subscription::STATUS_CANCELLED;
            $subscription->ends_at = now();
            $subscription->save();

            return $subscription->fresh();
        });
    }

    /**
     * Check if a feature is allowed for company plan.
     */
    public function isFeatureAllowed(Company $company, string $feature): bool
    {
        $subscription = $company->subscription;
        if (! $subscription) {
            return false;
        }

        if (! in_array($subscription->status, [Subscription::STATUS_TRIAL, Subscription::STATUS_ACTIVE], true)) {
            return false;
        }

        if ($subscription->status === Subscription::STATUS_TRIAL && ! $company->onTrial()) {
            return false;
        }

        $requiredPlan = config("subscriptions.features.{$feature}");
        if (! is_string($requiredPlan) || ! $this->planExists($requiredPlan)) {
            return false;
        }

        return $this->planRank($subscription->plan) >= $this->planRank($requiredPlan);
    }

    /**
     * Check whether count is within a plan resource limit.
     */
    public function checkLimit(Company $company, string $resource, int $count): bool
    {
        $subscription = $company->subscription;
        if (! $subscription) {
            return false;
        }

        $limit = config("subscriptions.plans.{$subscription->plan}.limits.{$resource}");
        if ($limit === 'unlimited') {
            return true;
        }

        if (! is_int($limit)) {
            return false;
        }

        return $count <= $limit;
    }

    private function planExists(string $plan): bool
    {
        return is_array(config("subscriptions.plans.{$plan}"));
    }

    private function planRank(string $plan): int
    {
        $rank = config("subscriptions.plans.{$plan}.rank");

        if (! is_int($rank)) {
            throw new InvalidArgumentException('Onbekend abonnement.');
        }

        return $rank;
    }

    private function guardDowngradeLimits(Company $company, string $targetPlan): void
    {
        $resourceCounts = [
            'werven' => $company->sites()->where('is_active', true)->count(),
            'werknemers' => $company->employees()->where('is_active', true)->count(),
            'machines' => $company->machines()->where('is_active', true)->count(),
        ];

        foreach ($resourceCounts as $resource => $count) {
            $limit = config("subscriptions.plans.{$targetPlan}.limits.{$resource}");

            if ($limit === 'unlimited') {
                continue;
            }

            if (! is_int($limit)) {
                continue;
            }

            if ($count > $limit) {
                throw new DomainException("Downgrade niet mogelijk: limiet voor {$resource} overschreden ({$count}/{$limit}).");
            }
        }
    }
}

