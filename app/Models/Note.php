<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'apoL_a01_code',
        'code_module',
        'nom_module',
        'note',
        'session_type', // printemps, automne
        'result_type', // normale, rattrapage
        'annee_scolaire',
        'is_current_session',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'is_current_session' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'apoL_a01_code', 'apoL_a01_code');
    }

    // Scopes
    public function scopeCurrentSession($query)
    {
        return $query->where('is_current_session', true);
    }

    public function scopeOldSession($query)
    {
        return $query->where('is_current_session', false);
    }

    public function scopeBySession($query, $sessionType)
    {
        return $query->where('session_type', $sessionType);
    }

    public function scopeByResultType($query, $resultType)
    {
        return $query->where('result_type', $resultType);
    }
}
