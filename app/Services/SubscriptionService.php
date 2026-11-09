<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Support\Str;

class SubscriptionService
{
    public const TRIAL_DAYS = 36500; // Lifetime access

    public function ensureTrialSubscription(Tenant $tenant): Subscription
    {
        $existing = $tenant->currentSubscription();
        if ($existing) {
            return $existing;
        }

        $plan = SubscriptionPlan::where('slug', 'free')->first()
            ?? SubscriptionPlan::first();

        if (! $plan) {
            $plan = SubscriptionPlan::create([
                'name' => 'المجانية الشاملة',
                'slug' => 'free',
                'tagline' => 'مجاني بالكامل لكافة مكاتب المحاماة',
                'max_users' => 999999,
                'max_clients' => 999999,
                'max_cases' => 999999,
                'max_storage_mb' => 999999,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $subscription = Subscription::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_period' => 'lifetime',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => null,
        ]);

        $settings = $tenant->settings ?? [];
        unset($settings['expires_at'], $settings['trial_days']);
        $settings['is_free'] = true;
        $settings['plan_slug'] = $plan->slug;
        $settings['billing_period'] = 'lifetime';
        $tenant->update(['status' => 'active', 'settings' => $settings]);

        return $subscription;
    }

    public function assignPlan(
        Tenant $tenant,
        SubscriptionPlan $plan,
        string $billingPeriod = 'lifetime',
        ?int $days = null
    ): Subscription {
        $tenant->subscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->update(['status' => 'cancelled']);

        $subscription = Subscription::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_period' => $billingPeriod,
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => null,
        ]);

        $settings = $tenant->settings ?? [];
        unset($settings['expires_at']);
        $settings['plan_slug'] = $plan->slug;
        $settings['billing_period'] = $billingPeriod;
        $settings['is_free'] = true;

        $tenant->update([
            'status' => 'active',
            'settings' => $settings,
        ]);

        return $subscription->load('plan');
    }

    public function createInvoiceForSubscription(
        Tenant $tenant,
        Subscription $subscription,
        SubscriptionPlan $plan,
        string $billingPeriod = 'lifetime',
        string $status = 'paid',
        ?string $paymentMethod = 'free'
    ): \App\Models\SubscriptionInvoice {
        $count = \App\Models\SubscriptionInvoice::count() + 1;
        $invoiceNumber = 'SINV-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        return \App\Models\SubscriptionInvoice::create([
            'invoice_number' => $invoiceNumber,
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'billing_period' => $billingPeriod,
            'amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 0.00,
            'status' => 'paid',
            'issued_at' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'paid_at' => now(),
            'payment_method' => 'free',
            'notes' => "اشتراك مجاني بالكامل مدى الحياة لخطة «{$plan->name}»",
        ]);
    }

    public function summarize(?Subscription $subscription, Tenant $tenant): array
    {
        $plan = $subscription?->plan
            ?? SubscriptionPlan::where('slug', 'free')->first()
            ?? SubscriptionPlan::first();

        return [
            'plan_id' => $plan?->id,
            'plan_name' => 'مجاني بالكامل (مدى الحياة)',
            'plan_slug' => 'free',
            'plan_tagline' => 'كافة مميزات المنصة مجانية بالكامل لجميع مكاتب المحاماة',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'billing_period' => 'مدى الحياة',
            'subscription_status' => 'active',
            'starts_at' => $tenant->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'ends_at' => null,
            'is_trial' => false,
        ];
    }
}
