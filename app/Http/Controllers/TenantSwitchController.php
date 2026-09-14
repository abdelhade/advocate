<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Support\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantSwitchController extends Controller
{
    /**
     * Switch to another office by redirecting to its subdomain.
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = Auth::user();

        $isMember = $user->tenants()
            ->where('tenants.id', $tenant->id)
            ->where('tenants.status', 'active')
            ->exists();

        if (! $isMember && ! $user->is_super_admin) {
            abort(403, 'غير مصرح لك بالوصول لهذا المكتب.');
        }

        return redirect()->away(TenantUrl::for($tenant, '/dashboard', $request));
    }
}
