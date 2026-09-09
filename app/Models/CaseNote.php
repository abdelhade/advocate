<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'user_id',
        'content',
    ];

    public function legalCase()
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
