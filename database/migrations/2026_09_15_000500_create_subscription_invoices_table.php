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
        if (! Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
                $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
                $table->string('plan_name');
                $table->string('billing_period')->default('yearly'); // monthly, yearly, custom
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->decimal('tax_amount', 10, 2)->default(0.00);
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->enum('status', ['paid', 'pending', 'cancelled', 'overdue'])->default('pending');
                $table->date('issued_at');
                $table->date('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
