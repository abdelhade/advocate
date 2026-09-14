<?php

namespace Tests\Feature\Tenant;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->user->tenants()->attach($this->tenant->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->client = Client::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'شركة الوفاق',
            'email' => 'wefaq@example.com',
            'phone' => '0501234567',
        ]);
    }

    public function test_invoices_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->onTenant($this->tenant)
            ->get($this->tenantUrl($this->tenant, '/invoices'));

        $response->assertOk();
    }

    public function test_invoice_creation_with_items_calculates_totals_correctly(): void
    {
        $response = $this->actingAs($this->user)
            ->onTenant($this->tenant)
            ->post($this->tenantUrl($this->tenant, '/invoices'), [
                'client_id' => $this->client->id,
                'invoice_number' => 'INV-2026-0001',
                'discount_amount' => 100.00,
                'tax_amount' => 50.00,
                'items' => [
                    ['description' => 'أتعاب المرافعة', 'quantity' => 1, 'unit_price' => 1000.00],
                    ['description' => 'دراسة مستندات', 'quantity' => 2, 'unit_price' => 250.00],
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $this->tenant->id,
            'invoice_number' => 'INV-2026-0001',
            'subtotal' => 1500.00,
            'total_amount' => 1450.00,
            'status' => 'posted',
        ]);
    }

    public function test_payment_updates_invoice_paid_amount_and_status(): void
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-TEST-01',
            'status' => 'posted',
            'subtotal' => 1000.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);

        $response = $this->actingAs($this->user)
            ->onTenant($this->tenant)
            ->post($this->tenantUrl($this->tenant, '/payments'), [
                'client_id' => $this->client->id,
                'invoice_id' => $invoice->id,
                'payment_number' => 'REC-0001',
                'amount' => 1000.00,
                'payment_method' => 'cash',
                'payment_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenant->id,
            'payment_number' => 'REC-0001',
            'amount' => 1000.00,
        ]);

        $invoice->refresh();
        $this->assertEquals(1000.00, $invoice->paid_amount);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_user_can_create_expense(): void
    {
        $response = $this->actingAs($this->user)
            ->onTenant($this->tenant)
            ->post($this->tenantUrl($this->tenant, '/expenses'), [
                'category' => 'رسوم قضائية وتراخيص',
                'amount' => 350.00,
                'expense_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'tenant_id' => $this->tenant->id,
            'category' => 'رسوم قضائية وتراخيص',
            'amount' => 350.00,
        ]);
    }
}
