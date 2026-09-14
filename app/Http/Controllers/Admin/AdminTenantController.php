<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminTenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('users')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $tenants = $query->paginate(20)->through(function ($tenant) {
            $owner = $tenant->users->firstWhere('pivot.is_owner', true);
            $settings = $tenant->settings ?? [];

            $startDate = $tenant->created_at ? $tenant->created_at->format('Y-m-d') : '-';
            
            if (!empty($settings['expires_at'])) {
                $endDate = \Carbon\Carbon::parse($settings['expires_at'])->format('Y-m-d');
            } elseif ($tenant->status === 'active') {
                $endDate = $tenant->created_at ? $tenant->created_at->addYear()->format('Y-m-d') : '-';
            } else {
                $endDate = $tenant->created_at ? $tenant->created_at->addDays(15)->format('Y-m-d') : '-';
            }

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'owner_name' => $owner?->name ?? '-',
                'email' => $tenant->email,
                'phone' => $tenant->phone ?? '-',
                'status' => $tenant->status,
                'users_count' => $tenant->users->count(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'created_at' => $startDate,
            ];
        });

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(string $id)
    {
        $tenant = Tenant::with('users')->findOrFail($id);
        $settings = $tenant->settings ?? [];
        $startDate = $tenant->created_at ? $tenant->created_at->format('Y-m-d') : '-';
        
        if (!empty($settings['expires_at'])) {
            $expiresAtCarbon = \Carbon\Carbon::parse($settings['expires_at']);
            $endDate = $expiresAtCarbon->format('Y-m-d');
        } elseif ($tenant->status === 'active') {
            $expiresAtCarbon = $tenant->created_at ? $tenant->created_at->addYear() : now()->addYear();
            $endDate = $expiresAtCarbon->format('Y-m-d');
        } else {
            $expiresAtCarbon = $tenant->created_at ? $tenant->created_at->addDays(15) : now()->addDays(15);
            $endDate = $expiresAtCarbon->format('Y-m-d');
        }

        $daysLeft = (int) ceil(now()->diffInFloat($expiresAtCarbon, false));
        if ($daysLeft < 0) {
            $daysLeft = 0;
        }

        $owner = $tenant->users->firstWhere('pivot.is_owner', true);

        // Subdomain URL calculation
        $host = request()->getHost();
        $scheme = request()->getScheme();
        if (str_contains($host, 'jalsateg.com')) {
            $subdomainUrl = "{$scheme}://{$tenant->slug}.jalsateg.com";
        } else {
            $port = request()->getPort();
            $portStr = ($port && $port != 80 && $port != 443) ? ":{$port}" : "";
            $subdomainUrl = "{$scheme}://{$tenant->slug}.localhost{$portStr}";
        }

        // Stats
        $clientsCount = $tenant->clients()->count();
        $casesCount = $tenant->cases()->count();
        $documentsCount = $tenant->documents()->count();
        $invoicesCount = $tenant->invoices()->count();

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
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_left' => $daysLeft,
                'subdomain_url' => $subdomainUrl,
                'stats' => [
                    'clients_count' => $clientsCount,
                    'cases_count' => $casesCount,
                    'documents_count' => $documentsCount,
                    'invoices_count' => $invoicesCount,
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
                'created_at' => $startDate,
            ],
        ]);
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'تم حذف المكتب وإلغاء الاشتراك بنجاح.');
    }

    public function activateSubscription(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);

        return back()->with('success', 'تم تفعيل حساب المكتب بنجاح.');
    }

    public function extendSubscription(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $days = (int) $request->input('days', 30);

        $settings = $tenant->settings ?? [];
        $currentEnd = !empty($settings['expires_at']) ? \Carbon\Carbon::parse($settings['expires_at']) : now();
        if ($currentEnd->isPast()) {
            $currentEnd = now();
        }

        $newEnd = $currentEnd->addDays($days);
        $settings['expires_at'] = $newEnd->toDateTimeString();

        $tenant->update([
            'status' => 'active',
            'settings' => $settings,
        ]);

        return back()->with('success', "تم تمديد اشتراك المكتب بنجاح لمدة {$days} يوماً حتى {$newEnd->format('Y-m-d')}.");
    }

    public function toggleStatus(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $newStatus = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->update(['status' => $newStatus]);

        $statusMsg = $newStatus === 'active' ? 'تم تفعيل المكتب بنجاح' : 'تم إيقاف/إلغاء اشتراك المكتب بنجاح';
        return back()->with('success', $statusMsg);
    }
}
