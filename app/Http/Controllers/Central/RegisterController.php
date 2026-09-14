<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\OfficeRegistrationConfirmation;
use App\Services\SubscriptionService;
use App\Support\TenantUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return Inertia::render('Central/Register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => $this->normalizePhone($request->input('phone')),
            'domain' => 'jalsateg.com',
            'subdomain' => strtolower((string) $request->input('subdomain')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'office_name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                'unique:tenants,slug',
                Rule::notIn(TenantUrl::reservedSlugs()),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^01[0125][0-9]{8}$/',
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'subdomain.unique' => 'رابط المكتب هذا مستخدم بالفعل، يرجى اختيار رابط آخر.',
            'subdomain.alpha_dash' => 'رابط المكتب يجب أن يحتوي على أحرف وأرقام بدون مسافات.',
            'subdomain.not_in' => 'رابط المكتب هذا محجوز، يرجى اختيار رابط آخر.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'هذا البريد مسجل مسبقاً. سجّل الدخول أو استخدم بريداً آخر.',
            'phone.required' => 'رقم التليفون مطلوب.',
            'phone.regex' => 'أدخل رقم تليفون مصري صحيح (مثال: 01012345678).',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 8 رموز.',
        ]);

        $tenant = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => 'active',
                'email_verified_at' => null,
            ]);

            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'name' => $request->office_name,
                'slug' => strtolower($request->subdomain),
                'domain' => 'jalsateg.com',
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 'active',
                'settings' => [
                    'retention_days' => 365,
                    'currency' => 'EGP',
                    'trial_days' => 15,
                ],
            ]);

            $tenant->users()->attach($user->id, [
                'is_owner' => true,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            app(SubscriptionService::class)->ensureTrialSubscription($tenant);

            Auth::login($user);
            session(['active_tenant_id' => $tenant->id]);

            return $tenant;
        });

        $user = Auth::user();
        $user->notify(new OfficeRegistrationConfirmation($tenant));

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء مكتبك بنجاح! أرسلنا رسالة تأكيد إلى بريدك الإلكتروني...',
            'redirect_url' => TenantUrl::for($tenant, '/verify-email', $request),
        ]);
    }

    /**
     * Normalize Egyptian phone numbers to 01XXXXXXXXX.
     */
    protected function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '2001')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '201')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '20') && strlen($digits) >= 12) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }
}
