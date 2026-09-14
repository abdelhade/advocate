<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('Central/Register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'office_name' => 'required|string|max:255',
            'subdomain' => 'required|string|alpha_dash|max:50|unique:tenants,slug',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'subdomain.unique' => 'رابط المكتب هذا مستخدم بالفعل، يرجى اختيار رابط آخر.',
            'subdomain.alpha_dash' => 'رابط المكتب يجب أن يحتوي على أحرف وأرقام بدون مسافات.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطبق.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 8 رموز.',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Find or create central user
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'status' => 'active',
                ]);
            }

            // 2. Create Tenant
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'name' => $request->office_name,
                'slug' => strtolower($request->subdomain),
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 'active',
                'settings' => [
                    'retention_days' => 365,
                    'currency' => 'EGP',
                ],
            ]);

            // 3. Attach User as Owner of this Tenant
            $tenant->users()->attach($user->id, [
                'is_owner' => true,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // 4. Authenticate & Set Active Tenant Context
            Auth::login($user);
            session(['active_tenant_id' => $tenant->id]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء مكتبك بنجاح! جاري توجيهك إلى لوحة التحكم...',
                'redirect_url' => route('dashboard'),
            ]);
        });
    }
}
