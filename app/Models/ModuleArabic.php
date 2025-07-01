<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Module;

class ModuleArabic extends Model
{
    protected $table = 'module_arabics';

    protected $fillable = [
        'code_module',
        'nom_module_ar',
        'nom_module_fr',
    ];

    // Get Arabic name by module code from modules table
    public static function getArabicName($codeModule)
    {
        $module = Module::where('cod_elp', $codeModule)->first();
        return $module ? $module->lib_elp_arb_fixed : null;
    }

    // Get French name by module code from modules table
    public static function getFrenchName($codeModule)
    {
        $module = Module::where('cod_elp', $codeModule)->first();
        return $module ? $module->lib_elp : null;
    }
}
