<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TenantUser extends Pivot
{
    protected $table = 'tenant_user';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'is_owner',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
        'joined_at' => 'datetime',
    ];
}
