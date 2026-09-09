<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number');
            $table->string('title');
            $table->string('case_type'); // جنائي، مدني، تجاري، أسرة، عمالي
            $table->string('court')->nullable();
            $table->string('status')->default('active'); // active, postponed, judged, closed
            $table->string('opponent_name')->nullable();
            $table->string('opponent_lawyer')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('filed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
