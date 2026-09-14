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

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'owner_name' => $owner?->name ?? '-',
                'email' => $tenant->email,
                'phone' => $tenant->phone ?? '-',
                'status' => $tenant->status,
                'users_count' => $tenant->users->count(),
                'created_at' => $tenant->created_at ? $tenant->created_at->format('Y-m-d H:i') : '-',
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

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'status' => $tenant->status,
                'users' => $tenant->users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'is_owner' => (bool) $u->pivot->is_owner,
                    'joined_at' => $u->pivot->joined_at ? \Carbon\Carbon::parse($u->pivot->joined_at)->format('Y-m-d') : '-',
                ]),
                'created_at' => $tenant->created_at ? $tenant->created_at->format('Y-m-d H:i') : '-',
            ],
        ]);
    }

    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'تم نقل المكتب إلى سلة المهملات بنجاح.');
    }

    public function activateSubscription(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);

        return back()->with('success', 'تم تفعيل حساب المكتب بنجاح.');
    }
}
