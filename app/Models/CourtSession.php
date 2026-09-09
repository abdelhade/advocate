<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'session_date',
        'session_time',
        'location',
        'decision',
        'next_session_date',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'next_session_date' => 'date',
        'session_time' => 'datetime:H:i',
    ];

    public function legalCase()
    {
        return $this->belongsTo(LegalCase::class);
    }
}
