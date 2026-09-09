<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class AdminTenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('domains')->latest();

        if ($search = $request->input('search')) {
            $query->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%");
        }

        $tenants = $query->paginate(15)->through(function ($tenant) {
            $trialEnds = $tenant->trial_ends_at ? \Carbon\Carbon::parse($tenant->trial_ends_at) : null;
            $daysLeft = $trialEnds ? (int) ceil(now()->diffInFloat($trialEnds, false)) : 0;

            return [
                'id' => $tenant->id,
                'name' => $tenant->name ?? $tenant->id,
                'owner_name' => $tenant->owner_name ?? '-',
                'email' => $tenant->email ?? '-',
                'phone' => $tenant->phone ?? '-',
                'domains' => $tenant->domains->pluck('domain')->toArray(),
                'status' => $tenant->status ?? 'trial',
                'trial_ends_at' => $trialEnds ? $trialEnds->format('Y-m-d') : '-',
                'days_left' => max(0, $daysLeft),
                'is_expired' => $trialEnds && $daysLeft <= 0 && ($tenant->status ?? 'trial') === 'trial',
                'created_at' => $tenant->created_at ? $tenant->created_at->format('Y-m-d H:i') : '-',
            ];
        });

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => ['required', 'string', 'max:255', 'unique:tenants,id', 'regex:/^[a-z0-9_-]+$/'],
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'name' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ], [
            'id.required' => 'معرّف المكتب مطلوب.',
            'id.unique' => 'هذا المعرّف مستخدم بالفعل.',
            'id.regex' => 'المعرّف يجب أن يحتوي فقط على أحرف إنجليزية صغيرة وأرقام وشرطات.',
            'domain.required' => 'النطاق مطلوب.',
            'domain.unique' => 'هذا النطاق مستخدم بالفعل.',
        ]);

        $tenant = Tenant::create([
            'id' => $request->id,
            'name' => $request->name ?? $request->id,
            'owner_name' => $request->owner_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'trial_ends_at' => now()->addDays(15)->toDateTimeString(),
            'status' => 'trial',
        ]);
        $tenant->domains()->create(['domain' => $request->domain]);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'تم إنشاء المكتب بنجاح مع فترة تجريبية 15 يوماً.');
    }

    public function show(string $id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);

        $trialEnds = $tenant->trial_ends_at ? \Carbon\Carbon::parse($tenant->trial_ends_at) : null;
        $daysLeft = $trialEnds ? (int) ceil(now()->diffInFloat($trialEnds, false)) : 0;

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name ?? $tenant->id,
                'owner_name' => $tenant->owner_name ?? '-',
                'email' => $tenant->email ?? '-',
                'phone' => $tenant->phone ?? '-',
                'status' => $tenant->status ?? 'trial',
                'trial_ends_at' => $trialEnds ? $trialEnds->format('Y-m-d H:i') : '-',
                'days_left' => max(0, $daysLeft),
                'domains' => $tenant->domains->map(fn ($d) => [
                    'id' => $d->id,
                    'domain' => $d->domain,
                    'created_at' => $d->created_at ? $d->created_at->format('Y-m-d H:i') : '-',
                ])->toArray(),
                'created_at' => $tenant->created_at ? $tenant->created_at->format('Y-m-d H:i') : '-',
                'data' => $tenant->data ?? [],
            ],
        ]);
    }

    public function edit(string $id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);

        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name ?? $tenant->id,
                'owner_name' => $tenant->owner_name ?? '',
                'email' => $tenant->email ?? '',
                'phone' => $tenant->phone ?? '',
                'domains' => $tenant->domains->pluck('domain')->toArray(),
                'created_at' => $tenant->created_at ? $tenant->created_at->format('Y-m-d H:i') : '-',
            ],
        ]);
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'تم حذف المكتب بنجاح.');
    }

    public function addDomain(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
        ], [
            'domain.required' => 'النطاق مطلوب.',
            'domain.unique' => 'هذا النطاق مستخدم بالفعل.',
        ]);

        $tenant->domains()->create(['domain' => $request->domain]);

        return back()->with('success', 'تم إضافة النطاق بنجاح.');
    }

    public function removeDomain(string $id, int $domainId)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->domains()->where('id', $domainId)->delete();

        return back()->with('success', 'تم حذف النطاق بنجاح.');
    }

    public function extendTrial(Request $request, string $id)
    {
        $request->validate(['days' => 'required|integer|min:1|max:365']);
        $tenant = Tenant::findOrFail($id);

        $currentExpiry = $tenant->trial_ends_at ? \Carbon\Carbon::parse($tenant->trial_ends_at) : now();
        $newExpiry = ($currentExpiry->isPast() ? now() : $currentExpiry)->addDays((int)$request->days);

        $tenant->update([
            'trial_ends_at' => $newExpiry->toDateTimeString(),
            'status' => 'trial',
        ]);

        return back()->with('success', 'تم تمديد الفترة التجريبية للمكتب بنجاح.');
    }

    public function activateSubscription(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update([
            'status' => 'active',
        ]);

        return back()->with('success', 'تم تفعيل اشتراك المكتب بنجاح.');
    }

}
