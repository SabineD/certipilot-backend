<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->user()?->company;

        if (! $company) {
            return response()->json([
                'message' => 'Geen bedrijf gekoppeld aan deze gebruiker.',
            ], 403);
        }

        $subscription = $company->subscription;

        if (! $subscription) {
            return response()->json([
                'message' => 'Geen actief abonnement gevonden voor dit bedrijf.',
            ], 402);
        }

        if ($subscription->status === Subscription::STATUS_ACTIVE) {
            return $next($request);
        }

        if ($subscription->status === Subscription::STATUS_TRIAL) {
            if ($company->onTrial()) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Je proefperiode is verlopen. Kies een abonnement om verder te gaan.',
            ], 402);
        }

        if ($subscription->status === Subscription::STATUS_CANCELLED) {
            return response()->json([
                'message' => 'Je abonnement is geannuleerd. Activeer opnieuw om wijzigingen te doen.',
            ], 403);
        }

        return response()->json([
            'message' => 'Je abonnement is momenteel niet actief.',
        ], 402);
    }
}

