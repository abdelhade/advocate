<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminAdminController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->get()->map(function ($admin) {
            return [
                'id' => $admin->id,
                'name' => $admin->name,
                'username' => $admin->username,
                'created_at' => $admin->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('Admin/Admins/Index', [
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Admins/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:admins,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'الاسم مطلوب.',
            'username.required' => 'اسم المستخدم مطلوب.',
            'username.unique' => 'اسم المستخدم مستخدم بالفعل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => $request->password,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم إنشاء حساب المدير بنجاح.');
    }

    public function edit(int $id)
    {
        $admin = Admin::findOrFail($id);

        return Inertia::render('Admin/Admins/Edit', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'username' => $admin->username,
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'الاسم مطلوب.',
            'username.required' => 'اسم المستخدم مطلوب.',
            'username.unique' => 'اسم المستخدم مستخدم بالفعل.',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $admin->name = $request->name;
        $admin->username = $request->username;

        if ($request->filled('password')) {
            $admin->password = $request->password;
        }

        $admin->save();

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم تحديث بيانات المدير بنجاح.');
    }

    public function destroy(int $id)
    {
        $admin = Admin::findOrFail($id);

        // Prevent self-deletion
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->withErrors(['error' => 'لا يمكنك حذف حسابك الحالي.']);
        }

        // Prevent deleting last admin
        if (Admin::count() <= 1) {
            return back()->withErrors(['error' => 'لا يمكن حذف آخر حساب مدير.']);
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم حذف حساب المدير بنجاح.');
    }
}
