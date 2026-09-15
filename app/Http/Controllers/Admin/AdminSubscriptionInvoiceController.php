<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ConfirmsAdminPassword;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSubscriptionInvoiceController extends Controller
{
    use ConfirmsAdminPassword;

    public function index(Request $request)
    {
        $query = SubscriptionInvoice::with(['tenant', 'plan', 'subscription'])
            ->latest('issued_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('plan_name', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($tQ) use ($search) {
                        $this->applySmartTenantSearch($tQ, $search);
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $stats = [
            'total_amount' => (float) SubscriptionInvoice::sum('total_amount'),
            'paid_amount' => (float) SubscriptionInvoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => (float) SubscriptionInvoice::whereIn('status', ['pending', 'overdue'])->sum('total_amount'),
            'count' => SubscriptionInvoice::count(),
        ];

        $invoices = $query->paginate(20)->through(function ($inv) {
            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'tenant_id' => $inv->tenant_id,
                'tenant_name' => $inv->tenant?->name ?? 'غير محدد',
                'plan_name' => $inv->plan_name,
                'billing_period' => $inv->billing_period,
                'amount' => (float) $inv->amount,
                'tax_amount' => (float) $inv->tax_amount,
                'total_amount' => (float) $inv->total_amount,
                'status' => $inv->status,
                'status_label' => $inv->displayStatus(),
                'payment_method' => $inv->displayPaymentMethod(),
                'issued_at' => $inv->issued_at?->format('Y-m-d') ?? '-',
                'due_date' => $inv->due_date?->format('Y-m-d') ?? '-',
                'paid_at' => $inv->paid_at?->format('Y-m-d H:i') ?? '-',
                'notes' => $inv->notes,
            ];
        });

        // Load non-dummy tenants first for the filter dropdown
        $tenants = Tenant::orderByRaw("CASE WHEN email NOT LIKE '%@example.com' THEN 0 ELSE 1 END")
            ->latest()
            ->limit(50)
            ->get(['id', 'name', 'email']);

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'price_monthly', 'price_yearly']);

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'tenants' => $tenants,
            'plans' => $plans,
            'filters' => $request->only('search', 'status', 'tenant_id'),
        ]);
    }

    public function searchTenants(Request $request)
    {
        $search = trim($request->input('q', ''));
        $query = Tenant::query();

        $this->applySmartTenantSearch($query, $search);

        $tenants = $query->limit(40)->get(['id', 'name', 'slug', 'email', 'phone']);

        return response()->json($tenants);
    }

    private function applySmartTenantSearch($query, string $search)
    {
        $search = trim($search);
        if (mb_strlen($search) < 1) {
            $query->orderByRaw("CASE WHEN email NOT LIKE '%@example.com' THEN 0 ELSE 1 END")->latest();
            return;
        }

        $stopWords = ['مكتب', 'مكاتب', 'شركة', 'مؤسسة', 'المحاماة', 'للمحاماة', 'المحاماه', 'للمحاماه', 'استشارات', 'قانونية'];
        $allWords = array_filter(explode(' ', $search), fn ($w) => mb_strlen($w) >= 2);
        $specificWords = array_filter($allWords, fn ($w) => !in_array(mb_strtolower($w), $stopWords));

        if (!empty($specificWords)) {
            $query->where(function ($q) use ($specificWords) {
                foreach ($specificWords as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%{$word}%")
                            ->orWhere('slug', 'like', "%{$word}%")
                            ->orWhere('email', 'like', "%{$word}%");

                        $alt1 = str_replace(['ة', 'أ', 'إ', 'آ', 'ى'], ['ه', 'ا', 'ا', 'ا', 'ي'], $word);
                        $alt2 = str_replace(['ه', 'أ', 'إ', 'آ', 'ى'], ['ة', 'ا', 'ا', 'ا', 'ي'], $word);
                        if ($alt1 !== $word) $sub->orWhere('name', 'like', "%{$alt1}%");
                        if ($alt2 !== $word) $sub->orWhere('name', 'like', "%{$alt2}%");
                    });
                }
            });
        } else {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                $alt1 = str_replace(['ة', 'أ', 'إ', 'آ', 'ى'], ['ه', 'ا', 'ا', 'ا', 'ي'], $search);
                $alt2 = str_replace(['ه', 'أ', 'إ', 'آ', 'ى'], ['ة', 'ا', 'ا', 'ا', 'ي'], $search);
                if ($alt1 !== $search) $q->orWhere('name', 'like', "%{$alt1}%");
                if ($alt2 !== $search) $q->orWhere('name', 'like', "%{$alt2}%");
            });
        }

        $query->orderByRaw("CASE WHEN email NOT LIKE '%@example.com' THEN 0 ELSE 1 END")->latest();
    }

    public function store(Request $request)
    {
        $this->confirmAdminPassword($request);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'plan_name' => 'required|string|max:255',
            'billing_period' => 'required|in:monthly,yearly,custom',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:paid,pending,cancelled,overdue',
            'payment_method' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $subscription = $tenant->currentSubscription();

        $amount = (float) $validated['amount'];
        $taxAmount = (float) ($validated['tax_amount'] ?? 0);
        $totalAmount = $amount + $taxAmount;

        $count = SubscriptionInvoice::count() + 1;
        $invoiceNumber = 'SINV-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        SubscriptionInvoice::create([
            'invoice_number' => $invoiceNumber,
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'plan_id' => $validated['plan_id'] ?? null,
            'plan_name' => $validated['plan_name'],
            'billing_period' => $validated['billing_period'],
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => $validated['status'],
            'issued_at' => now()->toDateString(),
            'due_date' => $validated['due_date'] ?? now()->addDays(7)->toDateString(),
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', "تم إنشاء الفاتورة رقم «{$invoiceNumber}» للمكتب بنجاح.");
    }

    public function show(string $id)
    {
        $invoice = SubscriptionInvoice::with(['tenant.users', 'plan', 'subscription'])->findOrFail($id);
        $owner = $invoice->tenant?->users->firstWhere('pivot.is_owner', true);

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'tenant_name' => $invoice->tenant?->name ?? 'غير محدد',
                'tenant_email' => $invoice->tenant?->email ?? '-',
                'tenant_phone' => $invoice->tenant?->phone ?? '-',
                'owner_name' => $owner?->name ?? 'غير محدد',
                'owner_email' => $owner?->email ?? '-',
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

    public function updateStatus(Request $request, string $id)
    {
        $this->confirmAdminPassword($request);

        $validated = $request->validate([
            'status' => 'required|in:paid,pending,cancelled,overdue',
            'payment_method' => 'nullable|string',
        ]);

        $invoice = SubscriptionInvoice::findOrFail($id);
        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'paid' && !$invoice->paid_at) {
            $updateData['paid_at'] = now();
        }
        if ($request->filled('payment_method')) {
            $updateData['payment_method'] = $validated['payment_method'];
        }

        $invoice->update($updateData);

        return back()->with('success', "تم تحديث حالة الفاتورة رقم «{$invoice->invoice_number}» إلى {$invoice->displayStatus()}.");
    }
}
