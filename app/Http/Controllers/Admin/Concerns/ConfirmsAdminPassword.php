<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait ConfirmsAdminPassword
{
    protected function confirmAdminPassword(Request $request): void
    {
        $request->validate([
            'admin_password' => ['required', 'string'],
        ], [
            'admin_password.required' => 'كلمة مرور المدير مطلوبة لتأكيد هذا الإجراء.',
        ]);

        $admin = Auth::guard('admin')->user();

        if (! $admin || ! Hash::check($request->input('admin_password'), $admin->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'كلمة مرور المدير غير صحيحة.',
            ]);
        }
    }
}
