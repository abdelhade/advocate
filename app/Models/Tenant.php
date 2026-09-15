<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Users that belong to this tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['is_owner', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Clients belonging to this tenant.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'tenant_id');
    }

    /**
     * Legal cases belonging to this tenant.
     */
    public function cases(): HasMany
    {
        return $this->hasMany(LegalCase::class, 'tenant_id');
    }

    /**
     * Documents belonging to this tenant.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'tenant_id');
    }

    /**
     * Invoices belonging to this tenant.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'tenant_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->latest('starts_at')
            ->first();
    }

    /**
     * Subscription invoices issued for this tenant.
     */
    public function subscriptionInvoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'tenant_id');
    }
}
