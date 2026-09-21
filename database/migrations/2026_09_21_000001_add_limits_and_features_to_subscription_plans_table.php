<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('max_clients')->default(10)->after('max_users');
            $table->unsignedInteger('max_cases')->default(100)->after('max_clients');
            $table->boolean('feature_tasks')->default(false)->after('features');
            $table->boolean('feature_billing')->default(false)->after('feature_tasks');
            $table->boolean('feature_documents')->default(false)->after('feature_billing');
            $table->boolean('feature_portal')->default(false)->after('feature_documents');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_clients',
                'max_cases',
                'feature_tasks',
                'feature_billing',
                'feature_documents',
                'feature_portal',
            ]);
        });
    }
};
