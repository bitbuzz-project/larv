<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'students_base';
    protected $primaryKey = 'apoL_a01_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'apoL_a01_code',
        'apoL_a02_nom',
        'apoL_a03_prenom',
        'apoL_a04_naissance',
        'cod_etu',
        'cod_etp',
        'cod_anu',
        'cod_dip',
        'cod_sex_etu',
        'lib_vil_nai_etu',
        'cin_ind',
        'lib_etp',
        'lic_etp',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Override the password field (we use birth date for authentication)
    public function getAuthPassword()
    {
        return $this->apoL_a04_naissance;
    }

    // Check if student is admin
    public function isAdmin(): bool
    {
        return $this->apoL_a01_code === '16005333';
    }

    // Get full name
    public function getFullNameAttribute(): string
    {
        return $this->apoL_a03_prenom . ' ' . $this->apoL_a02_nom;
    }

    // Get initials
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->apoL_a03_prenom, 0, 1) . substr($this->apoL_a02_nom, 0, 1));
    }

    // Relationships
    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'apoL_a01_code', 'apoL_a01_code');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'apoL_a01_code', 'apoL_a01_code');
    }

    public function administratives()
{
    return $this->hasMany(Administrative::class, 'apogee', 'apoL_a01_code');
}

public function pedaModules()
{
    return $this->hasMany(PedaModule::class, 'apogee', 'apoL_a01_code');
}

public function currentAdministrative()
{
    return $this->hasOne(Administrative::class, 'apogee', 'apoL_a01_code')
                ->where('annee_scolaire', '2024-2025');
}

public function currentModules()
{
    return $this->hasMany(PedaModule::class, 'apogee', 'apoL_a01_code')
                ->where('annee_scolaire', '2024-2025')
                ->where('status', 'active');
}

    public function filieres()
    {
        return $this->hasMany(Administative::class, 'apogee', 'apoL_a01_code');
    }
}
