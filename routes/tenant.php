<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\AuditLogController;
use App\Http\Controllers\Tenant\CaseController;
use App\Http\Controllers\Tenant\ClientController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\SessionController;
use App\Http\Controllers\Tenant\TaskController;
use App\Http\Controllers\Tenant\TenantBillingController;
use App\Http\Controllers\Tenant\TenantUserController;
use App\Http\Controllers\TenantSwitchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant subdomain routes ({slug}.jalsateg.com / {slug}.localhost)
| Shared single database — tenant resolved from host via IdentifyTenant.
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('/switch-tenant/{tenant}', TenantSwitchController::class)->name('tenant.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('tenant-users', TenantUserController::class)->names([
        'index' => 'tenant.users.index',
        'store' => 'tenant.users.store',
        'update' => 'tenant.users.update',
        'destroy' => 'tenant.users.destroy',
    ])->only(['index', 'store', 'update', 'destroy']);

    Route::resource('clients', ClientController::class);

    Route::resource('cases', CaseController::class);
    Route::post('cases/{case}/sessions', [SessionController::class, 'store'])->name('cases.sessions.store');
    Route::delete('cases/{case}/sessions/{session}', [SessionController::class, 'destroy'])->name('cases.sessions.destroy');

    Route::resource('tasks', TaskController::class)->except(['create', 'edit', 'show']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::resource('payments', PaymentController::class)->only(['index', 'store', 'destroy']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('billing', [TenantBillingController::class, 'index'])->name('tenant.billing.index');
    Route::get('billing/invoices/{invoice}', [TenantBillingController::class, 'showInvoice'])->name('tenant.billing.show');

    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
