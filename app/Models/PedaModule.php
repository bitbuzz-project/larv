<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedaModule extends Model
{
    use HasFactory;

    protected $table = 'peda_modules';

    protected $fillable = [
        'apogee',
        'module_code',
        'module_name',
        'module_name_ar',
        'credits',
        'coefficient',
        'semester',
        'annee_scolaire',
        'status',
        'professor',
        'schedule',
    ];

    protected $casts = [
        'credits' => 'integer',
        'coefficient' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'apogee', 'apoL_a01_code');
    }

    public function administrative()
    {
        return $this->belongsTo(Administrative::class, 'apogee', 'apogee');
    }

    // Scopes
    public function scopeForStudent($query, $apogee)
    {
        return $query->where('apogee', $apogee);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeCurrentYear($query, $year = null)
    {
        $year = $year ?: '2024-2025';
        return $query->where('annee_scolaire', $year);
    }

    // Accessors
    public function getFullModuleNameAttribute()
    {
        return $this->module_name_ar ?: $this->module_name;
    }
}
