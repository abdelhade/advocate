<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TenantUserController extends Controller
{
    public function __construct(private TenantPermissionService $permissions)
    {
    }

    public function index()
    {
        $tenant = auth()->user()->currentTenant();
        abort_unless($tenant, 403);
        abort_unless(
            auth()->user()->isOwnerOf($tenant) || auth()->user()->hasTenantPermission('users.view', $tenant),
            403
        );

        $users = $tenant->users()
            ->get()
            ->map(function ($user) use ($tenant) {
                $userPermissions = $this->permissions->permissionsForUser($user, $tenant);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '-',
                    'is_owner' => (bool) $user->pivot->is_owner,
                    'status' => $user->pivot->status ?? 'active',
                    'joined_at' => $user->pivot->joined_at ? date('Y-m-d', strtotime($user->pivot->joined_at)) : '-',
                    'permissions' => $userPermissions,
                    'permissions_count' => count($userPermissions),
                ];
            });

        return Inertia::render('Tenant/Users/Index', [
            'users' => $users,
            'permissionMatrix' => $this->permissions->matrix(),
            'permissionPresets' => $this->permissions->inertiaPayload()['presets'],
            'canManageUsers' => auth()->user()->isOwnerOf($tenant)
                || auth()->user()->hasTenantPermission('users.create', $tenant)
                || auth()->user()->hasTenantPermission('users.update', $tenant),
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->currentTenant();
        abort_unless($tenant, 403);
        abort_unless(
            auth()->user()->isOwnerOf($tenant) || auth()->user()->hasTenantPermission('users.create', $tenant),
            403
        );

        $allowed = $this->permissions->allPermissionNames();

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($allowed)],
        ], [
            'name.required' => 'اسم المستخدم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
        ]);

        $existingInTenant = $tenant->users()
            ->where('users.email', $request->email)
            ->exists();

        if ($existingInTenant) {
            return back()->withErrors(['email' => 'هذا البريد مضاف بالفعل إلى فريق هذا المكتب.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password ?: '00000000'),
                'status' => 'active',
                'is_super_admin' => false,
            ]);
        }

        $tenant->users()->attach($user->id, [
            'is_owner' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->permissions->sync($user, $tenant, $request->input('permissions', []));

        return back()->with('success', 'تم إضافة المستخدم إلى فريق المكتب بنجاح.');
    }

    public function update(Request $request, $id)
    {
        $tenant = auth()->user()->currentTenant();
        abort_unless($tenant, 403);
        abort_unless(
            auth()->user()->isOwnerOf($tenant) || auth()->user()->hasTenantPermission('users.update', $tenant),
            403
        );

        $user = $tenant->users()->where('users.id', $id)->firstOrFail();

        if ($user->pivot->is_owner) {
            return back()->withErrors(['general' => 'صلاحيات مالك المكتب كاملة ولا يمكن تعديلها من هنا.']);
        }

        $allowed = $this->permissions->allPermissionNames();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($allowed)],
        ], [
            'name.required' => 'اسم المستخدم مطلوب.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف.',
        ]);

        $user->forceFill([
            'name' => $request->name,
            'phone' => $request->phone,
        ])->save();

        if ($request->filled('password')) {
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();
        }

        $this->permissions->sync($user, $tenant, $request->input('permissions', []));

        return back()->with('success', 'تم تحديث بيانات وصلاحيات المستخدم بنجاح.');
    }

    public function destroy($id)
    {
        $tenant = auth()->user()->currentTenant();
        abort_unless($tenant, 403);
        abort_unless(
            auth()->user()->isOwnerOf($tenant) || auth()->user()->hasTenantPermission('users.delete', $tenant),
            403
        );

        $user = $tenant->users()->where('user_id', $id)->first();

        if (! $user) {
            return back()->withErrors(['general' => 'المستخدم غير موجود في هذا المكتب.']);
        }

        if ($user->pivot->is_owner) {
            return back()->withErrors(['general' => 'لا يمكن إزالة مالك المكتب الرئيسي.']);
        }

        $this->permissions->sync($user, $tenant, []);
        $tenant->users()->detach($id);

        return back()->with('success', 'تم إزالة المستخدم من فريق المكتب بنجاح.');
    }
}
