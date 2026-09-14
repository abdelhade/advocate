<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSwitchController extends Controller
{
    /**
     * Switch the active tenant context for the authenticated user.
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = Auth::user();

        // Verify membership
        $isMember = $user->tenants()
            ->where('tenants.id', $tenant->id)
            ->where('tenants.status', 'active')
            ->exists();

        if (!$isMember && !$user->is_super_admin) {
            abort(403, 'غير مصرح لك بالوصول لهذا المكتب.');
        }

        session(['active_tenant_id' => $tenant->id]);

        return back()->with('success', "تم التبديل إلى مكتب: {$tenant->name}");
    }
}
