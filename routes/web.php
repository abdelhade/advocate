<?php

use App\Http\Controllers\Admin\AdminAdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Central\RegisterController;
use App\Http\Middleware\AdminAuthenticated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return Inertia::render('CentralWelcome');
        });

        // Registration for 15-Day Free Trial
        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('central.register');
        Route::post('/register', [RegisterController::class, 'register'])->name('central.register.submit');

        // Admin Auth (guest)
        Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

        // Admin Protected
        Route::middleware(AdminAuthenticated::class)->prefix('admin')->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

            // Tenants
            Route::get('/tenants', [AdminTenantController::class, 'index'])->name('admin.tenants.index');
            Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('admin.tenants.create');
            Route::post('/tenants', [AdminTenantController::class, 'store'])->name('admin.tenants.store');
            Route::get('/tenants/{id}', [AdminTenantController::class, 'show'])->name('admin.tenants.show');
            Route::get('/tenants/{id}/edit', [AdminTenantController::class, 'edit'])->name('admin.tenants.edit');
            Route::delete('/tenants/{id}', [AdminTenantController::class, 'destroy'])->name('admin.tenants.destroy');
            Route::post('/tenants/{id}/domains', [AdminTenantController::class, 'addDomain'])->name('admin.tenants.domains.add');
            Route::delete('/tenants/{id}/domains/{domainId}', [AdminTenantController::class, 'removeDomain'])->name('admin.tenants.domains.remove');

            // Tenants trial & subscription status actions
            Route::post('/tenants/{id}/extend-trial', [AdminTenantController::class, 'extendTrial'])->name('admin.tenants.extend_trial');
            Route::post('/tenants/{id}/activate', [AdminTenantController::class, 'activateSubscription'])->name('admin.tenants.activate');

            // Admins
            Route::get('/admins', [AdminAdminController::class, 'index'])->name('admin.admins.index');
            Route::get('/admins/create', [AdminAdminController::class, 'create'])->name('admin.admins.create');
            Route::post('/admins', [AdminAdminController::class, 'store'])->name('admin.admins.store');
            Route::get('/admins/{id}/edit', [AdminAdminController::class, 'edit'])->name('admin.admins.edit');
            Route::put('/admins/{id}', [AdminAdminController::class, 'update'])->name('admin.admins.update');
            Route::delete('/admins/{id}', [AdminAdminController::class, 'destroy'])->name('admin.admins.destroy');
        });
    });
}

