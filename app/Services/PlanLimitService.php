<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LegalCase;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

class PlanLimitService
{
    public function subscription(Tenant $tenant)
    {
        return $tenant->currentSubscription();
    }

    public function isTrialing(Tenant $tenant): bool
    {
        return $this->subscription($tenant)?->status === 'trialing';
    }

    /**
     * During trial, unlock enterprise limits/features.
     */
    public function effectivePlan(Tenant $tenant): SubscriptionPlan
    {
        if ($this->isTrialing($tenant)) {
            $enterprise = SubscriptionPlan::where('slug', 'enterprise')->where('is_active', true)->first();
            if ($enterprise) {
                return $enterprise;
            }
        }

        $plan = $this->subscription($tenant)?->plan;

        if ($plan) {
            return $plan;
        }

        return SubscriptionPlan::where('slug', 'free')->where('is_active', true)->firstOrFail();
    }

    public function hasFeature(Tenant $tenant, string $feature): bool
    {
        $plan = $this->effectivePlan($tenant);
        $column = 'feature_'.$feature;

        return (bool) ($plan->{$column} ?? false);
    }

    public function assertFeature(Tenant $tenant, string $feature): void
    {
        if ($this->hasFeature($tenant, $feature)) {
            return;
        }

        $labels = [
            'tasks' => 'إدارة المهام',
            'billing' => 'الفوترة والأتعاب والمصروفات',
            'documents' => 'إدارة المستندات والتوثيق',
            'portal' => 'بوابة الموكلين',
        ];

        $label = $labels[$feature] ?? $feature;

        throw ValidationException::withMessages([
            'plan' => "ميزة «{$label}» غير متاحة في خطتك الحالية. يرجى الترقية للوصول إليها.",
        ]);
    }

    public function assertCanAddUser(Tenant $tenant): void
    {
        $plan = $this->effectivePlan($tenant);
        $current = $tenant->users()->count();

        if ($current >= (int) $plan->max_users) {
            throw ValidationException::withMessages([
                'plan' => "وصلت للحد الأقصى للمستخدمين ({$plan->max_users}) في خطتك. يرجى الترقية لإضافة المزيد.",
            ]);
        }
    }

    public function assertCanAddClient(Tenant $tenant): void
    {
        $plan = $this->effectivePlan($tenant);
        $current = Client::where('tenant_id', $tenant->id)->count();

        if ($current >= (int) $plan->max_clients) {
            throw ValidationException::withMessages([
                'plan' => "وصلت للحد الأقصى للموكلين ({$plan->max_clients}) في خطتك. يرجى الترقية لإضافة المزيد.",
            ]);
        }
    }

    public function assertCanAddCase(Tenant $tenant): void
    {
        $plan = $this->effectivePlan($tenant);
        $current = LegalCase::where('tenant_id', $tenant->id)->count();

        if ($current >= (int) $plan->max_cases) {
            throw ValidationException::withMessages([
                'plan' => "وصلت للحد الأقصى للقضايا ({$plan->max_cases}) في خطتك. يرجى الترقية لإضافة المزيد.",
            ]);
        }
    }

    public function usage(Tenant $tenant): array
    {
        return [
            'users' => $tenant->users()->count(),
            'clients' => Client::where('tenant_id', $tenant->id)->count(),
            'cases' => LegalCase::where('tenant_id', $tenant->id)->count(),
        ];
    }

    public function inertiaPayload(Tenant $tenant): array
    {
        $plan = $this->effectivePlan($tenant);
        $assigned = $this->subscription($tenant)?->plan;
        $usage = $this->usage($tenant);

        return [
            'slug' => $assigned?->slug ?? $plan->slug,
            'name' => $assigned?->name ?? $plan->name,
            'is_trialing' => $this->isTrialing($tenant),
            'limits' => [
                'max_users' => (int) $plan->max_users,
                'max_clients' => (int) $plan->max_clients,
                'max_cases' => (int) $plan->max_cases,
            ],
            'usage' => $usage,
            'features' => [
                'tasks' => (bool) $plan->feature_tasks,
                'billing' => (bool) $plan->feature_billing,
                'documents' => (bool) $plan->feature_documents,
                'portal' => (bool) $plan->feature_portal,
            ],
            'can_add' => [
                'user' => $usage['users'] < (int) $plan->max_users,
                'client' => $usage['clients'] < (int) $plan->max_clients,
                'case' => $usage['cases'] < (int) $plan->max_cases,
            ],
        ];
    }
}
