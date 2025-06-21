<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleArabic extends Model
{
    protected $table = 'module_arabics';

    protected $fillable = [
        'code_module',
        'nom_module_ar',
        'nom_module_fr',
    ];

    // Get Arabic name by module code
    public static function getArabicName($codeModule)
    {
        $module = self::where('code_module', $codeModule)->first();
        return $module ? $module->nom_module_ar : null;
    }

    // Get French name by module code
    public static function getFrenchName($codeModule)
    {
        $module = self::where('code_module', $codeModule)->first();
        return $module ? $module->nom_module_fr : null;
    }
}
