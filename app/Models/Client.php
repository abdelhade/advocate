<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'national_id_or_cr',
        'email',
        'phone',
        'address',
        'notes',
    ];

    /**
     * Legal cases for this client.
     */
    public function cases(): HasMany
    {
        return $this->hasMany(LegalCase::class, 'client_id');
    }

    /**
     * Documents for this client.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'client_id');
    }

    /**
     * Invoices for this client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'client_id');
    }
}
