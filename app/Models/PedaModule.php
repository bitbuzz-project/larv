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
        'session_type',
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

    // Relationship to get module details from modules table
    public function moduleDetails()
    {
        return $this->belongsTo(Module::class, 'module_code', 'cod_elp');
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

    public function scopeCurrentSession($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySessionType($query, $sessionType)
    {
        return $query->where('session_type', $sessionType);
    }

    // Accessors
    public function getFullModuleNameAttribute()
    {
        return $this->module_name_ar ?: $this->module_name;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'نشط',
            'completed' => 'مكتمل',
            'failed' => 'راسب',
            'withdrawn' => 'منسحب',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    // Get semester from module details (cod_pel)
    public function getActualSemesterAttribute()
    {
        // First try to get from moduleDetails relationship
        if ($this->moduleDetails && $this->moduleDetails->cod_pel) {
            return $this->mapCodPelToSemester($this->moduleDetails->cod_pel);
        }

        // Fallback to stored semester
        return $this->semester ?: 'S1';
    }

    // Map cod_pel to semester
    private function mapCodPelToSemester($codPel)
    {
        // Common patterns for semester mapping
        // You might need to adjust this based on your institution's coding system

        if (str_contains($codPel, 'S1') || str_contains($codPel, '1')) {
            return 'S1';
        } elseif (str_contains($codPel, 'S2') || str_contains($codPel, '2')) {
            return 'S2';
        } elseif (str_contains($codPel, 'S3') || str_contains($codPel, '3')) {
            return 'S3';
        } elseif (str_contains($codPel, 'S4') || str_contains($codPel, '4')) {
            return 'S4';
        } elseif (str_contains($codPel, 'S5') || str_contains($codPel, '5')) {
            return 'S5';
        } elseif (str_contains($codPel, 'S6') || str_contains($codPel, '6')) {
            return 'S6';
        }

        // More sophisticated mapping based on actual cod_pel patterns
        // Extract numbers or patterns from cod_pel
        if (preg_match('/(\d+)/', $codPel, $matches)) {
            $number = intval($matches[1]);
            if ($number >= 1 && $number <= 6) {
                return 'S' . $number;
            }
        }

        // Default fallback
        return 'S1';
    }

    // Static method to determine semester from module code or other criteria
    public static function determineSemesterFromModule($moduleCode, $moduleName = null)
    {
        // Try to get from modules table first
        $module = Module::where('cod_elp', $moduleCode)->first();

        if ($module && $module->cod_pel) {
            return self::mapCodPelToSemesterStatic($module->cod_pel);
        }

        // Fallback to analyzing module code or name
        if ($moduleName) {
            // Look for semester indicators in module name
            if (preg_match('/S(\d+)|semester\s*(\d+)|semestre\s*(\d+)/i', $moduleName, $matches)) {
                $semNumber = $matches[1] ?: $matches[2] ?: $matches[3];
                if ($semNumber >= 1 && $semNumber <= 6) {
                    return 'S' . $semNumber;
                }
            }
        }

        return 'S1'; // Default
    }

    private static function mapCodPelToSemesterStatic($codPel)
    {
        if (str_contains($codPel, 'S1') || str_contains($codPel, '1')) {
            return 'S1';
        } elseif (str_contains($codPel, 'S2') || str_contains($codPel, '2')) {
            return 'S2';
        } elseif (str_contains($codPel, 'S3') || str_contains($codPel, '3')) {
            return 'S3';
        } elseif (str_contains($codPel, 'S4') || str_contains($codPel, '4')) {
            return 'S4';
        } elseif (str_contains($codPel, 'S5') || str_contains($codPel, '5')) {
            return 'S5';
        } elseif (str_contains($codPel, 'S6') || str_contains($codPel, '6')) {
            return 'S6';
        }

        if (preg_match('/(\d+)/', $codPel, $matches)) {
            $number = intval($matches[1]);
            if ($number >= 1 && $number <= 6) {
                return 'S' . $number;
            }
        }

        return 'S1';
    }
}
