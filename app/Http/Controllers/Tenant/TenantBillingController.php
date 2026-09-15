<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantBillingController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(Request $request): Response
    {
        $tenant = auth()->user()->currentTenant();
        $subscription = $tenant->currentSubscription();
        $summary = $this->subscriptionService->summarize($subscription, $tenant);

        $expiresAtCarbon = $summary['ends_at']
            ? \Carbon\Carbon::parse($summary['ends_at'])->endOfDay()
            : now();
        $daysLeft = max(0, (int) ceil(now()->diffInSeconds($expiresAtCarbon, false) / 86400));

        $invoices = SubscriptionInvoice::where('tenant_id', $tenant->id)
            ->latest('issued_at')
            ->paginate(15)
            ->through(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'plan_name' => $inv->plan_name,
                'billing_period' => $inv->billing_period === 'monthly' ? 'شهري' : ($inv->billing_period === 'yearly' ? 'سنوي' : 'مخصص'),
                'amount' => (float) $inv->amount,
                'tax_amount' => (float) $inv->tax_amount,
                'total_amount' => (float) $inv->total_amount,
                'status' => $inv->status,
                'status_label' => $inv->displayStatus(),
                'payment_method' => $inv->displayPaymentMethod(),
                'issued_at' => $inv->issued_at?->format('Y-m-d') ?? '-',
                'due_date' => $inv->due_date?->format('Y-m-d') ?? '-',
                'paid_at' => $inv->paid_at?->format('Y-m-d H:i') ?? '-',
            ]);

        return Inertia::render('Tenant/Billing/Index', [
            'tenant' => [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'status' => $tenant->status,
                'subscription' => $summary,
                'days_left' => $daysLeft,
            ],
            'invoices' => $invoices,
        ]);
    }

    public function showInvoice(string $id): Response
    {
        $tenant = auth()->user()->currentTenant();
        $invoice = SubscriptionInvoice::where('tenant_id', $tenant->id)
            ->with(['plan', 'subscription'])
            ->findOrFail($id);

        $owner = $tenant->users()->wherePivot('is_owner', true)->first();

        return Inertia::render('Tenant/Billing/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'tenant_name' => $tenant->name,
                'tenant_email' => $tenant->email,
                'tenant_phone' => $tenant->phone ?? '-',
                'owner_name' => $owner?->name ?? 'صاحب المكتب',
                'plan_name' => $invoice->plan_name,
                'billing_period' => $invoice->billing_period === 'monthly' ? 'شهري' : ($invoice->billing_period === 'yearly' ? 'سنوي' : 'مخصص'),
                'amount' => (float) $invoice->amount,
                'tax_amount' => (float) $invoice->tax_amount,
                'total_amount' => (float) $invoice->total_amount,
                'status' => $invoice->status,
                'status_label' => $invoice->displayStatus(),
                'payment_method' => $invoice->displayPaymentMethod(),
                'issued_at' => $invoice->issued_at?->format('Y-m-d') ?? '-',
                'due_date' => $invoice->due_date?->format('Y-m-d') ?? '-',
                'paid_at' => $invoice->paid_at?->format('Y-m-d H:i') ?? '-',
                'notes' => $invoice->notes,
            ],
        ]);
    }
}
