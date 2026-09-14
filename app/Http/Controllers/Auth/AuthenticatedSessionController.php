<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TenantContext;
use App\Support\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an authenticating request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $context = app(TenantContext::class);
        $user = $request->user();

        if ($context->check()) {
            $tenant = $context->get();
            $isMember = $user->is_super_admin || $user->tenants()
                ->where('tenants.id', $tenant->id)
                ->wherePivot('status', 'active')
                ->exists();

            if (! $isMember) {
                Auth::logout();

                throw ValidationException::withMessages([
                    'email' => 'ليس لديك صلاحية للدخول إلى هذا المكتب.',
                ]);
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        $tenant = $user->tenants()->where('tenants.status', 'active')->first();

        if ($tenant) {
            return redirect()->away(TenantUrl::for($tenant, '/dashboard', $request));
        }

        return redirect()->away(TenantUrl::central('/', $request));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (TenantUrl::slugFromHost($request->getHost())) {
            return redirect()->to(TenantUrl::loginUrl($request));
        }

        return redirect('/');
    }
}
