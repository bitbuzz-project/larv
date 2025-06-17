<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        // Core module information
        'cod_elp', 'cod_cmp', 'cod_nel', 'cod_pel', 'lib_elp', 'lic_elp', 'lib_cmt_elp',

        // Dates
        'dat_cre_elp', 'dat_mod_elp', 'dat_deb_ope_ipe', 'dat_fin_ope_ipe',

        // Volume and credits
        'nbr_vol_elp', 'cod_vol_elp', 'nbr_pnt_ect_elp',

        // Status and flags
        'eta_elp', 'lib_lie_elp', 'lib_nom_rsp_elp',

        // Admission numbers
        'nbr_adm_elp', 'nbr_adm_fra', 'nbr_adm_etr',

        // Grades and thresholds
        'not_obt_elp_num', 'not_obt_elp_den', 'not_min_rpt_elp_num', 'not_min_rpt_elp_den',
        'not_min_adm_num', 'not_min_adm_den', 'not_max_adm_num', 'not_max_adm_den',
        'not_min_rpt_elp', 'bar_min_rpt_elp', 'not_min_con_elp', 'bar_min_con_elp',

        // Boolean flags
        'tem_elp_cap', 'tem_rei_ipe_acq', 'tem_sus_elp', 'tem_rel_pos_syt', 'tem_sca_elp',
        'tem_elp_prm_niv', 'tem_not_elp', 'tem_pnt_jur_elp', 'tem_mnd_elp', 'tem_res_elp',
        'tem_jur_elp', 'tem_ctl_val_cad_elp', 'tem_anl_rpt_elp', 'tem_con_elp', 'tem_cap_elp',
        'tem_ses_uni', 'tem_adi', 'tem_ado', 'tem_heu_ens_elp', 'tem_mcc_elp', 'tem_rpt_dsc_elp',

        // Additional fields
        'lib_sus_elp', 'bar_sai_elp', 'cod_cfm', 'dur_con_elp', 'cod_scc', 'nbr_eff_prv_elp',

        // Teaching hours
        'nbr_heu_cm_elp', 'nbr_heu_td_elp', 'nbr_heu_tp_elp',

        // Panel codes
        'cod_pan_1', 'cod_pan_2', 'cod_pan_3', 'cod_pan_4',

        // Arabic translations
        'lib_elp_arb', 'lic_elp_arb', 'lib_elp_arb_fixed'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('eta_elp', 'A'); // Assuming 'A' means active
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('cod_elp', $code);
    }

    public function scopeByComponent($query, $component)
    {
        return $query->where('cod_cmp', $component);
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->lib_elp ?: $this->lic_elp ?: $this->cod_elp;
    }

    public function getDisplayNameArabicAttribute()
    {
        return $this->lib_elp_arb ?: $this->lic_elp_arb ?: $this->lib_elp;
    }

    public function getEctsCreditsAttribute()
    {
        return $this->nbr_pnt_ect_elp ? floatval($this->nbr_pnt_ect_elp) : 0;
    }

    public function getIsMandatoryAttribute()
    {
        return $this->tem_mnd_elp === 'O'; // 'O' for Oui (Yes)
    }

    public function getIsActiveAttribute()
    {
        return $this->eta_elp === 'A';
    }

    // Helpers for boolean flags (Oracle uses 'O'/'N' for Yes/No)
    public function getBooleanFlag($field)
    {
        return $this->$field === 'O';
    }

    public function setBooleanFlag($field, $value)
    {
        $this->$field = $value ? 'O' : 'N';
    }
}
