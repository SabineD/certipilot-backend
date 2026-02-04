<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpgradeSubscriptionRequest;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use DomainException;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {
    }

    /**
     * Return the current company subscription.
     */
    public function show(Request $request)
    {
        $company = $request->user()->company;
        $subscription = $company->subscription;

        if (! $subscription) {
            $subscription = $this->subscriptionService->startTrial($company);
        }

        return response()->json($this->formatSubscription($subscription));
    }

    /**
     * Upgrade or activate a subscription plan.
     */
    public function upgrade(UpgradeSubscriptionRequest $request)
    {
        $company = $request->user()->company;
        $plan = $request->validated('plan');

        if ($plan === Subscription::PLAN_ENTERPRISE) {
            return response()->json([
                'message' => 'Enterprise kan niet via de API geactiveerd worden. Neem contact op met sales.',
            ], 403);
        }

        try {
            $subscription = $this->subscriptionService->activatePlan($company, $plan);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($this->formatSubscription($subscription));
    }

    /**
     * Cancel the current subscription.
     */
    public function cancel(Request $request)
    {
        $company = $request->user()->company;

        try {
            $subscription = $this->subscriptionService->cancelSubscription($company);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($this->formatSubscription($subscription));
    }

    private function formatSubscription(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'company_id' => $subscription->company_id,
            'plan' => $subscription->plan,
            'status' => $subscription->status,
            'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
            'ends_at' => $subscription->ends_at?->toISOString(),
        ];
    }
}

