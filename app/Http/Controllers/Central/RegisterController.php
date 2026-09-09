<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'subdomain' => 'required|string|alpha_dash|max:50|unique:tenants,id',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'subdomain.unique' => 'رابط المكتب هذا مستخدم بالفعل، يرجى اختيار رابط آخر.',
            'subdomain.alpha_dash' => 'رابط المكتب يجب أن يحتوي على أحرف وأرقام بدون مسافات.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطبق.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 8 رموز.',
        ]);

        $subdomain = strtolower($request->subdomain);
        $port = request()->getPort();
        $scheme = request()->getScheme();
        
        $host = request()->getHost();
        if ($host === '127.0.0.1' || $host === 'localhost') {
            $baseDomain = 'localhost';
        } else {
            $baseDomain = implode('.', array_slice(explode('.', $host), -2));
        }

        $fullDomain = $subdomain . '.' . $baseDomain;

        // 1. Create Tenant (triggers DB creation & migrations)
        $tenant = Tenant::create([
            'id' => $subdomain,
            'name' => $request->office_name,
            'owner_name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'trial_ends_at' => now()->addDays(15)->toDateTimeString(),
            'status' => 'trial',
        ]);

        // 2. Create Domain mapping
        $tenant->domains()->create([
            'domain' => $fullDomain,
        ]);

        // 3. Create default Lawyer user inside Tenant context
        $tenant->run(function () use ($request) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        });

        // 4. Construct redirect URL to tenant login
        $redirectUrl = $scheme . '://' . $fullDomain . ($port && !in_array($port, [80, 443]) ? ':' . $port : '') . '/login';

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء مكتبك بنجاح! جاري توجيهك إلى لوحة التحكم...',
            'redirect_url' => $redirectUrl,
        ]);
    }
}
