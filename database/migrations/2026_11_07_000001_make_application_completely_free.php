<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_plans')) {
            DB::table('subscription_plans')->update([
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_users' => 999999,
                'max_clients' => 999999,
                'max_cases' => 999999,
                'max_storage_mb' => 999999,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'is_active' => true,
            ]);
        }

        if (Schema::hasTable('subscriptions')) {
            DB::table('subscriptions')->update([
                'status' => 'active',
                'ends_at' => null,
                'trial_ends_at' => null,
                'billing_period' => 'lifetime',
            ]);
        }

        if (Schema::hasTable('tenants')) {
            DB::table('tenants')->where('status', '!=', 'suspended')->update([
                'status' => 'active',
            ]);
        }
    }

    public function down(): void
    {
        // No reversal needed for free transition
    }
};

