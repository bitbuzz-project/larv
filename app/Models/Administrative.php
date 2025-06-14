<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrative extends Model
{
    use HasFactory;

    protected $table = 'administative'; // Keep the existing table name

    protected $fillable = [
        'apogee',
        'filliere',
        'annee_scolaire',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'apogee', 'apoL_a01_code');
    }

    // Scopes
    public function scopeForStudent($query, $apogee)
    {
        return $query->where('apogee', $apogee);
    }

    public function scopeCurrentYear($query, $year = null)
    {
        $year = $year ?: '2024-2025';
        return $query->where('annee_scolaire', $year);
    }

    // Accessors
    public function getFormattedFiliereAttribute()
    {
        return ucfirst(strtolower($this->filliere));
    }
}
