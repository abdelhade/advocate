<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class TenantUserController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->currentTenant();

        $users = $tenant->users()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '-',
                    'is_owner' => (bool) $user->pivot->is_owner,
                    'status' => $user->pivot->status ?? 'active',
                    'joined_at' => $user->pivot->joined_at ? date('Y-m-d', strtotime($user->pivot->joined_at)) : '-',
                ];
            });

        return Inertia::render('Tenant/Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
        ], [
            'name.required' => 'اسم المستخدم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
        ]);

        $tenant = auth()->user()->currentTenant();

        // 1. Check if user already exists globally
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($tenant->users()->where('user_id', $user->id)->exists()) {
                return back()->withErrors(['email' => 'هذا المستخدم مضاف بالفعل إلى فريق هذا المكتب.']);
            }
        } else {
            // Create new global user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password ?: '00000000'),
                'status' => 'active',
                'is_super_admin' => false,
            ]);
        }

        // 2. Attach User to Tenant
        $tenant->users()->attach($user->id, [
            'is_owner' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'تم إضافة المستخدم إلى فريق المكتب بنجاح.');
    }

    public function destroy($id)
    {
        $tenant = auth()->user()->currentTenant();

        $user = $tenant->users()->where('user_id', $id)->first();

        if (!$user) {
            return back()->withErrors(['general' => 'المستخدم غير موجود في هذا المكتب.']);
        }

        if ($user->pivot->is_owner) {
            return back()->withErrors(['general' => 'لا يمكن إزالة مالك المكتب الرئيسي.']);
        }

        $tenant->users()->detach($id);

        return back()->with('success', 'تم إزالة المستخدم من فريق المكتب بنجاح.');
    }
}
