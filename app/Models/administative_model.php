<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administative extends Model
{
    use HasFactory;

    protected $table = 'administative';

    protected $fillable = [
        'apogee',
        'filliere',
        'annee_scolaire',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'apogee', 'apoL_a01_code');
    }
}