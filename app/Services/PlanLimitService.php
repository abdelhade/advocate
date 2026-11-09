<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LegalCase;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;

class PlanLimitService
{
    public function subscription(Tenant $tenant)
    {
        return $tenant->currentSubscription();
    }

    public function isTrialing(Tenant $tenant): bool
    {
        return false;
    }

    /**
     * The application is completely free with all features and unlimited limits.
     */
    public function effectivePlan(Tenant $tenant): SubscriptionPlan
    {
        $plan = $this->subscription($tenant)?->plan
            ?? SubscriptionPlan::where('slug', 'free')->first()
            ?? SubscriptionPlan::first();

        if ($plan) {
            return $plan;
        }

        $dummyPlan = new SubscriptionPlan([
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
        ]);

        return $dummyPlan;
    }

    public function hasFeature(Tenant $tenant, string $feature): bool
    {
        return true;
    }

    public function assertFeature(Tenant $tenant, string $feature): void
    {
        // Fully free application - all features unlocked
    }

    public function assertCanAddUser(Tenant $tenant): void
    {
        // Fully free application - unlimited users
    }

    public function assertCanAddClient(Tenant $tenant): void
    {
        // Fully free application - unlimited clients
    }

    public function assertCanAddCase(Tenant $tenant): void
    {
        // Fully free application - unlimited cases
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
        $usage = $this->usage($tenant);

        return [
            'slug' => 'free',
            'name' => 'مجاني بالكامل (مدى الحياة)',
            'is_trialing' => false,
            'limits' => [
                'max_users' => 999999,
                'max_clients' => 999999,
                'max_cases' => 999999,
            ],
            'usage' => $usage,
            'features' => [
                'tasks' => true,
                'billing' => true,
                'documents' => true,
                'portal' => true,
            ],
            'can_add' => [
                'user' => true,
                'client' => true,
                'case' => true,
            ],
        ];
    }
}
