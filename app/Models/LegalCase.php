<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'title',
        'case_type',
        'court',
        'status',
        'opponent_name',
        'opponent_lawyer',
        'description',
        'client_id',
        'user_id',
        'filed_at',
    ];

    protected $casts = [
        'filed_at' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courtSessions()
    {
        return $this->hasMany(CourtSession::class);
    }

    public function caseNotes()
    {
        return $this->hasMany(CaseNote::class);
    }
}
