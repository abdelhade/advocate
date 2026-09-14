<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseParty extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'case_parties';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'name',
        'party_type',
        'lawyer_name',
        'phone',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }
}
