<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            // Core module information
            $table->string('cod_elp', 20)->nullable(); // Module code
            $table->string('cod_cmp', 20)->nullable(); // Component code
            $table->string('cod_nel', 20)->nullable(); // Element nature code
            $table->string('cod_pel', 20)->nullable(); // Element parent code
            $table->string('lib_elp', 255)->nullable(); // Module title (French)
            $table->string('lic_elp', 255)->nullable(); // Module short title
            $table->text('lib_cmt_elp')->nullable(); // Module comment

            // Dates
            $table->string('dat_cre_elp', 20)->nullable(); // Creation date
            $table->string('dat_mod_elp', 20)->nullable(); // Modification date
            $table->string('dat_deb_ope_ipe', 20)->nullable(); // Operation start date
            $table->string('dat_fin_ope_ipe', 20)->nullable(); // Operation end date

            // Volume and credits
            $table->string('nbr_vol_elp', 10)->nullable(); // Volume hours
            $table->string('cod_vol_elp', 10)->nullable(); // Volume code
            $table->string('nbr_pnt_ect_elp', 10)->nullable(); // ECTS credits

            // Status and flags
            $table->string('eta_elp', 10)->nullable(); // Status
            $table->string('lib_lie_elp', 100)->nullable(); // Link library
            $table->string('lib_nom_rsp_elp', 100)->nullable(); // Responsible name

            // Admission numbers
            $table->string('nbr_adm_elp', 10)->nullable(); // Total admissions
            $table->string('nbr_adm_fra', 10)->nullable(); // French admissions
            $table->string('nbr_adm_etr', 10)->nullable(); // Foreign admissions

            // Grades and thresholds
            $table->string('not_obt_elp_num', 10)->nullable(); // Obtained grade numerator
            $table->string('not_obt_elp_den', 10)->nullable(); // Obtained grade denominator
            $table->string('not_min_rpt_elp_num', 10)->nullable(); // Min repeat grade num
            $table->string('not_min_rpt_elp_den', 10)->nullable(); // Min repeat grade den
            $table->string('not_min_adm_num', 10)->nullable(); // Min admission grade num
            $table->string('not_min_adm_den', 10)->nullable(); // Min admission grade den
            $table->string('not_max_adm_num', 10)->nullable(); // Max admission grade num
            $table->string('not_max_adm_den', 10)->nullable(); // Max admission grade den
            $table->string('not_min_rpt_elp', 10)->nullable(); // Min repeat grade
            $table->string('bar_min_rpt_elp', 10)->nullable(); // Min repeat threshold
            $table->string('not_min_con_elp', 10)->nullable(); // Min continuous grade
            $table->string('bar_min_con_elp', 10)->nullable(); // Min continuous threshold

            // Boolean flags (stored as VARCHAR2 in Oracle, typically 'O'/'N')
            $table->string('tem_elp_cap', 1)->nullable(); // Capacity flag
            $table->string('tem_rei_ipe_acq', 1)->nullable(); // Acquired flag
            $table->string('tem_sus_elp', 1)->nullable(); // Suspended flag
            $table->string('tem_rel_pos_syt', 1)->nullable(); // Position flag
            $table->string('tem_sca_elp', 1)->nullable(); // Scale flag
            $table->string('tem_elp_prm_niv', 1)->nullable(); // First level flag
            $table->string('tem_not_elp', 1)->nullable(); // Grade flag
            $table->string('tem_pnt_jur_elp', 1)->nullable(); // Jury points flag
            $table->string('tem_mnd_elp', 1)->nullable(); // Mandatory flag
            $table->string('tem_res_elp', 1)->nullable(); // Result flag
            $table->string('tem_jur_elp', 1)->nullable(); // Jury flag
            $table->string('tem_ctl_val_cad_elp', 1)->nullable(); // Control validation flag
            $table->string('tem_anl_rpt_elp', 1)->nullable(); // Annual repeat flag
            $table->string('tem_con_elp', 1)->nullable(); // Continuous flag
            $table->string('tem_cap_elp', 1)->nullable(); // Capacity flag
            $table->string('tem_ses_uni', 1)->nullable(); // Unique session flag
            $table->string('tem_adi', 1)->nullable(); // ADI flag
            $table->string('tem_ado', 1)->nullable(); // ADO flag
            $table->string('tem_heu_ens_elp', 1)->nullable(); // Teaching hours flag
            $table->string('tem_mcc_elp', 1)->nullable(); // MCC flag
            $table->string('tem_rpt_dsc_elp', 1)->nullable(); // Repeat desc flag

            // Additional fields
            $table->string('lib_sus_elp', 255)->nullable(); // Suspension reason
            $table->string('bar_sai_elp', 10)->nullable(); // Input threshold
            $table->string('cod_cfm', 20)->nullable(); // CFM code
            $table->string('dur_con_elp', 10)->nullable(); // Continuous duration
            $table->string('cod_scc', 20)->nullable(); // SCC code
            $table->string('nbr_eff_prv_elp', 10)->nullable(); // Expected number

            // Teaching hours
            $table->string('nbr_heu_cm_elp', 10)->nullable(); // CM hours
            $table->string('nbr_heu_td_elp', 10)->nullable(); // TD hours
            $table->string('nbr_heu_tp_elp', 10)->nullable(); // TP hours

            // Panel codes
            $table->string('cod_pan_1', 20)->nullable(); // Panel 1 code
            $table->string('cod_pan_2', 20)->nullable(); // Panel 2 code
            $table->string('cod_pan_3', 20)->nullable(); // Panel 3 code
            $table->string('cod_pan_4', 20)->nullable(); // Panel 4 code

            // Arabic translations
            $table->string('lib_elp_arb', 255)->nullable(); // Module title (Arabic)
            $table->string('lic_elp_arb', 255)->nullable(); // Module short title (Arabic)
            $table->text('lib_elp_arb_fixed')->nullable(); // Fixed Arabic title

            $table->timestamps();

            // Indexes for better performance
            $table->index(['cod_elp']);
            $table->index(['cod_cmp']);
            $table->index(['eta_elp']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
