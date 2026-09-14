<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->primary(
                ['permission_id', 'model_id', 'model_type', 'tenant_id'],
                'model_has_permissions_primary'
            );

            $table->index(['model_id', 'model_type', 'tenant_id'], 'model_has_permissions_model_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
    }
};
