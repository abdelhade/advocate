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
        Schema::create('court_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('case_id');
            $table->dateTime('session_date');
            $table->enum('status', ['scheduled', 'completed', 'postponed', 'cancelled'])->default('scheduled');
            $table->text('requirements')->nullable();
            $table->text('results')->nullable();
            $table->dateTime('next_session_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['case_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('legal_cases')
                ->onDelete('cascade');

            $table->index(['tenant_id', 'session_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_sessions');
    }
};
