<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->nullable()->default('#3b82f6');
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_types');
    }
};
