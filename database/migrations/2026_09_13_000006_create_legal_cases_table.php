<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('client_id');
            $table->string('case_number');
            $table->string('internal_number')->nullable();
            $table->string('title');
            $table->string('court_name')->nullable();
            $table->string('circuit')->nullable();
            $table->string('case_type')->nullable();
            $table->enum('status', ['active', 'suspended', 'won', 'lost', 'closed'])->default('active');
            $table->foreignId('primary_lawyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['id', 'tenant_id'], 'uk_cases_id_tenant');
            $table->unique(['tenant_id', 'case_number'], 'uk_cases_tenant_case_num');
            $table->foreign(['client_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('clients')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('case_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('case_id');
            $table->string('name');
            $table->enum('party_type', ['opponent', 'third_party', 'lawyer_opponent', 'witness'])->default('opponent');
            $table->string('lawyer_name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->foreign(['case_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('legal_cases')
                ->onDelete('cascade');
        });

        Schema::create('case_user', function (Blueprint $table) {
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('case_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('assigned_role')->default('assigned_lawyer');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['case_id', 'user_id']);
            $table->foreign(['case_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('legal_cases')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_user');
        Schema::dropIfExists('case_parties');
        Schema::dropIfExists('legal_cases');
    }
};
