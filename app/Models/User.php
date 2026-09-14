<?php

namespace App\Models;

use App\Notifications\OfficeRegistrationConfirmation;
use App\Services\TenantContext;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['is_owner', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function activeTenant(): ?Tenant
    {
        return app(TenantContext::class)->get();
    }

    public function currentTenant(): ?Tenant
    {
        return $this->activeTenant() ?? $this->tenants()->first();
    }

    public function isOwnerOf(Tenant|string $tenant): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $this->tenants()
            ->where('tenants.id', $tenantId)
            ->wherePivot('is_owner', true)
            ->exists();
    }

    public function hasTenantPermission(string $permission, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? $this->currentTenant();
        if (! $tenant) {
            return false;
        }

        return app(\App\Services\TenantPermissionService::class)
            ->userHas($this, $tenant, $permission);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new OfficeRegistrationConfirmation($this->tenants()->first()));
    }
}
