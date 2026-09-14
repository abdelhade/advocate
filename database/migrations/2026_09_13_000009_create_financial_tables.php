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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('client_id');
            $table->foreignId('case_id')->nullable()->constrained('legal_cases')->nullOnDelete();
            $table->string('invoice_number');
            $table->enum('status', ['draft', 'posted', 'paid', 'partially_paid', 'voided'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uk_invoices_id_tenant');
            $table->unique(['tenant_id', 'invoice_number'], 'uk_invoices_tenant_num');

            $table->foreign(['client_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('clients')
                ->onDelete('restrict');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('total_price', 12, 2)->default(0.00);

            $table->foreign(['invoice_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('invoices')
                ->onDelete('cascade');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->string('payment_number');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'card'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign(['invoice_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('invoices')
                ->onDelete('restrict');

            $table->foreign(['client_id', 'tenant_id'])
                ->references(['id', 'tenant_id'])
                ->on('clients')
                ->onDelete('restrict');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('legal_cases')->nullOnDelete();
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->foreignId('paid_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
