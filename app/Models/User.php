<?php

namespace App\Models;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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

    /**
     * Tenants this user belongs to.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['is_owner', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the active tenant for the current request context.
     */
    public function activeTenant(): ?Tenant
    {
        return app(TenantContext::class)->get();
    }

    /**
     * Alias for activeTenant with fallback for tests context.
     */
    public function currentTenant(): ?Tenant
    {
        return $this->activeTenant() ?? $this->tenants()->first();
    }

    /**
     * Check if user is owner of a specific tenant.
     */
    public function isOwnerOf(Tenant|string $tenant): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $this->tenants()
            ->where('tenants.id', $tenantId)
            ->wherePivot('is_owner', true)
            ->exists();
    }
}
