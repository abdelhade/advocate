<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SubscriptionService
{
    public const TRIAL_DAYS = 15;

    public function ensureTrialSubscription(Tenant $tenant): Subscription
    {
        $existing = $tenant->currentSubscription();
        if ($existing) {
            return $existing;
        }

        $plan = SubscriptionPlan::where('slug', 'free')->first()
            ?? SubscriptionPlan::orderBy('sort_order')->first();

        return Subscription::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'billing_period' => 'yearly',
            'starts_at' => now(),
            'ends_at' => now()->addDays(self::TRIAL_DAYS),
            'trial_ends_at' => now()->addDays(self::TRIAL_DAYS),
        ]);
    }

    public function assignPlan(
        Tenant $tenant,
        SubscriptionPlan $plan,
        string $billingPeriod = 'yearly',
        ?int $days = null
    ): Subscription {
        $tenant->subscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->update(['status' => 'cancelled']);

        $isFree = $plan->isFree();
        $duration = $days ?? ($billingPeriod === 'monthly' ? 30 : 365);
        if ($isFree && $days === null) {
            $duration = self::TRIAL_DAYS;
        }

        $startsAt = now();
        $endsAt = $startsAt->copy()->addDays($duration);

        $subscription = Subscription::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => $isFree ? 'trialing' : 'active',
            'billing_period' => $billingPeriod,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $isFree ? $endsAt : null,
        ]);

        $settings = $tenant->settings ?? [];
        $settings['expires_at'] = $endsAt->toDateTimeString();
        $settings['plan_slug'] = $plan->slug;
        $settings['billing_period'] = $billingPeriod;

        $tenant->update([
            'status' => 'active',
            'settings' => $settings,
        ]);

        return $subscription->load('plan');
    }

    public function summarize(?Subscription $subscription, Tenant $tenant): array
    {
        $settings = $tenant->settings ?? [];

        if ($subscription?->plan) {
            $plan = $subscription->plan;
            $endsAt = $subscription->ends_at
                ?? ($subscription->trial_ends_at
                    ?? (! empty($settings['expires_at']) ? Carbon::parse($settings['expires_at']) : null));

            return [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_slug' => $plan->slug,
                'plan_tagline' => $plan->tagline,
                'price_monthly' => (float) $plan->price_monthly,
                'price_yearly' => (float) $plan->price_yearly,
                'billing_period' => $subscription->billing_period ?? 'yearly',
                'subscription_status' => $subscription->displayStatus(),
                'starts_at' => $subscription->starts_at?->format('Y-m-d'),
                'ends_at' => $endsAt?->format('Y-m-d'),
                'is_trial' => $subscription->isTrialing(),
            ];
        }

        // Fallback for tenants without a subscription row yet
        $endsAt = ! empty($settings['expires_at'])
            ? Carbon::parse($settings['expires_at'])
            : ($tenant->created_at?->copy()->addDays(self::TRIAL_DAYS) ?? now()->addDays(self::TRIAL_DAYS));

        return [
            'plan_id' => null,
            'plan_name' => 'تجريبي',
            'plan_slug' => 'trial',
            'plan_tagline' => 'فترة تجريبية',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'billing_period' => 'yearly',
            'subscription_status' => 'trialing',
            'starts_at' => $tenant->created_at?->format('Y-m-d'),
            'ends_at' => $endsAt->format('Y-m-d'),
            'is_trial' => true,
        ];
    }
}
