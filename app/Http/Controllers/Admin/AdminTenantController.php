<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ConfirmsAdminPassword;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminTenantController extends Controller
{
    use ConfirmsAdminPassword;

    public function __construct(private SubscriptionService $subscriptions)
    {
    }

    public function index(Request $request)
    {
        $query = Tenant::query()
            ->with(['users', 'subscriptions' => fn ($q) => $q->with('plan')->latest('starts_at')])
            ->withCount('users')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($planSlug = $request->input('plan')) {
            $query->whereHas('subscriptions', function ($q) use ($planSlug) {
                $q->whereIn('status', ['active', 'trialing', 'past_due'])
                    ->whereHas('plan', fn ($p) => $p->where('slug', $planSlug));
            });
        }

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get([
            'id', 'name', 'slug', 'tagline', 'price_monthly', 'price_yearly',
        ]);

        $tenants = $query->paginate(20)->through(function ($tenant) {
            $owner = $tenant->users->firstWhere('pivot.is_owner', true);
            $subscription = $tenant->subscriptions->first();
            $summary = $this->subscriptions->summarize($subscription, $tenant);

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'owner_name' => $owner?->name ?? '-',
                'email' => $tenant->email,
                'phone' => $tenant->phone ?? '-',
                'status' => $tenant->status,
                'users_count' => $tenant->users_count,
                'start_date' => $summary['starts_at'] ?? ($tenant->created_at?->format('Y-m-d') ?? '-'),
                'end_date' => $summary['ends_at'] ?? '-',
                'plan_name' => $summary['plan_name'],
                'plan_slug' => $summary['plan_slug'],
                'plan_id' => $summary['plan_id'],
                'price_monthly' => $summary['price_monthly'],
                'price_yearly' => $summary['price_yearly'],
                'billing_period' => $summary['billing_period'],
                'subscription_status' => $summary['subscription_status'],
                'is_trial' => $summary['is_trial'],
                'created_at' => $tenant->created_at?->format('Y-m-d') ?? '-',
            ];
        });

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'plans' => $plans,
            'filters' => $request->only('search', 'plan'),
        ]);
    }

    public function show(string $id)
    {
        $tenant = Tenant::with(['users', 'subscriptions.plan'])->findOrFail($id);
        $subscription = $tenant->subscriptions
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->sortByDesc('starts_at')
            ->first()
            ?? $tenant->subscriptions->sortByDesc('starts_at')->first();

        $summary = $this->subscriptions->summarize($subscription, $tenant);

        $expiresAtCarbon = $summary['ends_at']
            ? \Carbon\Carbon::parse($summary['ends_at'])->endOfDay()
            : now();
        $daysLeft = max(0, (int) ceil(now()->diffInSeconds($expiresAtCarbon, false) / 86400));

        $owner = $tenant->users->firstWhere('pivot.is_owner', true);
        $subdomainUrl = \App\Support\TenantUrl::for($tenant, '/', request());

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get([
            'id', 'name', 'slug', 'tagline', 'price_monthly', 'price_yearly',
        ]);

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'email' => $tenant->email,
                'phone' => $tenant->phone ?? '-',
                'status' => $tenant->status,
                'owner_name' => $owner?->name ?? 'غير محدد',
                'owner_email' => $owner?->email ?? '-',
                'owner_phone' => $owner?->phone ?? '-',
                'start_date' => $summary['starts_at'],
                'end_date' => $summary['ends_at'],
                'days_left' => $daysLeft,
                'subdomain_url' => $subdomainUrl,
                'subscription' => $summary,
                'stats' => [
                    'clients_count' => $tenant->clients()->count(),
                    'cases_count' => $tenant->cases()->count(),
                    'documents_count' => $tenant->documents()->count(),
                    'invoices_count' => $tenant->invoices()->count(),
                    'users_count' => $tenant->users->count(),
                ],
                'users' => $tenant->users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?? '-',
                    'is_owner' => (bool) $u->pivot->is_owner,
                    'joined_at' => $u->pivot->joined_at ? \Carbon\Carbon::parse($u->pivot->joined_at)->format('Y-m-d') : '-',
                ]),
                'subscription_invoices' => $tenant->subscriptionInvoices()
                    ->latest('issued_at')
                    ->get()
                    ->map(fn ($inv) => [
                        'id' => $inv->id,
                        'invoice_number' => $inv->invoice_number,
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
                    ]),
                'created_at' => $tenant->created_at?->format('Y-m-d'),
            ],
            'plans' => $plans,
        ]);
    }

    public function updatePlan(Request $request, string $id)
    {
        $this->confirmAdminPassword($request);

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_period' => 'required|in:monthly,yearly',
        ], [
            'plan_id.required' => 'اختر خطة الاشتراك.',
            'billing_period.in' => 'فترة الفوترة غير صحيحة.',
        ]);

        $tenant = Tenant::findOrFail($id);
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $this->subscriptions->assignPlan(
            $tenant,
            $plan,
            $validated['billing_period']
        );

        return back()->with('success', "تم تحديث اشتراك المكتب إلى خطة «{$plan->name}».");
    }

    public function destroy(Request $request, string $id)
    {
        $this->confirmAdminPassword($request);

        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'تم حذف المكتب وإلغاء الاشتراك بنجاح.');
    }

    public function activateSubscription(Request $request, string $id)
    {
        $this->confirmAdminPassword($request);

        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);

        return back()->with('success', 'تم تفعيل حساب المكتب بنجاح.');
    }

    public function extendSubscription(Request $request, string $id)
    {
        $this->confirmAdminPassword($request);

        $tenant = Tenant::findOrFail($id);
        $days = (int) $request->input('days', 30);

        $subscription = $tenant->currentSubscription();
        if ($subscription) {
            $currentEnd = $subscription->ends_at && $subscription->ends_at->isFuture()
                ? $subscription->ends_at->copy()
                : now();
            $newEnd = $currentEnd->addDays($days);
            $subscription->update([
                'ends_at' => $newEnd,
                'status' => $subscription->plan?->isFree() ? 'trialing' : 'active',
            ]);
        } else {
            $newEnd = now()->addDays($days);
        }

        $settings = $tenant->settings ?? [];
        $settings['expires_at'] = ($newEnd ?? now()->addDays($days))->toDateTimeString();

        $tenant->update([
            'status' => 'active',
            'settings' => $settings,
        ]);

        $endLabel = ($newEnd ?? now()->addDays($days))->format('Y-m-d');

        return back()->with('success', "تم تمديد اشتراك المكتب بنجاح لمدة {$days} يوماً حتى {$endLabel}.");
    }

    public function toggleStatus(Request $request, string $id)
    {
        $this->confirmAdminPassword($request);

        $tenant = Tenant::findOrFail($id);
        $newStatus = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->update(['status' => $newStatus]);

        if ($newStatus === 'suspended') {
            $tenant->subscriptions()
                ->whereIn('status', ['active', 'trialing', 'past_due'])
                ->update(['status' => 'cancelled']);
        }

        $statusMsg = $newStatus === 'active' ? 'تم تفعيل المكتب بنجاح' : 'تم إيقاف/إلغاء اشتراك المكتب بنجاح';

        return back()->with('success', $statusMsg);
    }
}
