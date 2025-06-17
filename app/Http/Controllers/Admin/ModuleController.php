<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules
     */
    public function index(Request $request)
    {
        $query = Module::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cod_elp', 'like', "%{$search}%")
                  ->orWhere('lib_elp', 'like', "%{$search}%")
                  ->orWhere('lib_elp_arb', 'like', "%{$search}%")
                  ->orWhere('cod_cmp', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('eta_elp', $request->status);
        }

        // Filter by component
        if ($request->filled('component')) {
            $query->where('cod_cmp', $request->component);
        }

        $modules = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module
     */
    public function create()
    {
        return view('admin.modules.create');
    }

    /**
     * Store a newly created module
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cod_elp' => 'required|string|max:20|unique:modules,cod_elp',
            'lib_elp' => 'required|string|max:255',
            'cod_cmp' => 'nullable|string|max:20',
            'nbr_pnt_ect_elp' => 'nullable|numeric',
            'eta_elp' => 'nullable|string|max:10',
            'lib_elp_arb' => 'nullable|string|max:255',
        ]);

        try {
            Module::create($validated);
            return redirect()->route('admin.modules.index')
                           ->with('success', 'Module créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Erreur lors de la création du module.');
        }
    }

    /**
     * Display the specified module
     */
    public function show(Module $module)
    {
        return view('admin.modules.show', compact('module'));
    }

    /**
     * Show the form for editing the specified module
     */
    public function edit(Module $module)
    {
        return view('admin.modules.edit', compact('module'));
    }

    /**
     * Update the specified module
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'lib_elp' => 'required|string|max:255',
            'cod_cmp' => 'nullable|string|max:20',
            'nbr_pnt_ect_elp' => 'nullable|numeric',
            'eta_elp' => 'nullable|string|max:10',
            'lib_elp_arb' => 'nullable|string|max:255',
        ]);

        try {
            $module->update($validated);
            return redirect()->route('admin.modules.show', $module)
                           ->with('success', 'Module mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Erreur lors de la mise à jour du module.');
        }
    }

    /**
     * Remove the specified module
     */
    public function destroy(Module $module)
    {
        try {
            $module->delete();
            return redirect()->route('admin.modules.index')
                           ->with('success', 'Module supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.modules.index')
                           ->with('error', 'Erreur lors de la suppression du module.');
        }
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('admin.modules.import');
    }

    /**
     * Handle JSON file import
     */
    public function import(Request $request)
    {
        // Increase execution limits for large imports
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '512M'); // 512MB memory

        $request->validate([
            'json_file' => [
                'required',
                'file',
                'max:51200', // 50MB max
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $extension = strtolower($value->getClientOriginalExtension());
                        $mimeType = $value->getClientMimeType();

                        if ($extension !== 'json' && $mimeType !== 'application/json') {
                            $fail('Le fichier doit être un fichier JSON valide.');
                            return;
                        }

                        $content = file_get_contents($value->getPathname());
                        $data = json_decode($content, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Le fichier JSON est invalide.');
                        }
                    }
                },
            ],
        ]);

        try {
            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getPathname());
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Le fichier JSON est invalide.');
            }

            $modulesData = [];

            if (isset($jsonData['results']) && is_array($jsonData['results'])) {
                // Oracle export format
                foreach ($jsonData['results'] as $result) {
                    if (isset($result['items']) && is_array($result['items']) &&
                        isset($result['columns']) && is_array($result['columns'])) {

                        $columns = $result['columns'];
                        $columnNames = array_map(function($col) {
                            return $col['name'] ?? $col;
                        }, $columns);

                        foreach ($result['items'] as $item) {
                            if (is_array($item)) {
                                $moduleRow = [];
                                foreach ($item as $index => $value) {
                                    if (isset($columnNames[$index])) {
                                        $moduleRow[strtolower($columnNames[$index])] = $value;
                                    }
                                }
                                $modulesData[] = $moduleRow;
                            }
                        }
                    }
                }
            } elseif (is_array($jsonData)) {
                $modulesData = $jsonData;
            } else {
                return back()->with('error', 'Format JSON non reconnu.');
            }

            if (empty($modulesData)) {
                return back()->with('error', 'Aucune donnée de module trouvée dans le fichier.');
            }

            if (count($modulesData) > 10000) {
                return back()->with('error', 'Le fichier ne peut pas contenir plus de 10000 modules à la fois.');
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $lineNumber = 1;

            DB::beginTransaction();

            foreach ($modulesData as $moduleData) {
                try {
                    // Map the JSON fields to our database columns
                    $mappedData = $this->mapJsonToDatabase($moduleData);

                    // Validate required fields
                    if (!isset($mappedData['cod_elp']) || empty($mappedData['cod_elp'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => 'N/A',
                            'message' => 'Code module manquant (cod_elp)',
                            'type' => 'validation'
                        ];
                        $lineNumber++;
                        continue;
                    }

                    // Check if module already exists
                    if (Module::where('cod_elp', $mappedData['cod_elp'])->exists()) {
                        $skipped++;
                        $lineNumber++;
                        continue;
                    }

                    // Create module
                    Module::create($mappedData);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'code' => $mappedData['cod_elp'] ?? 'N/A',
                        'message' => $e->getMessage(),
                        'type' => 'database'
                    ];
                }
                $lineNumber++;
            }

            DB::commit();

            // Store import statistics in session
            session([
                'import_stats' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'total' => count($modulesData),
                    'success_rate' => count($modulesData) > 0 ? ($imported / count($modulesData)) * 100 : 0
                ],
                'import_errors' => $errors
            ]);

            if ($imported > 0) {
                $message = "Import réussi! {$imported} modules importés";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ignorés (déjà existants)";
                }
                if (count($errors) > 0) {
                    $message .= ", " . count($errors) . " erreurs";
                }

                return redirect()->route('admin.modules.import.results')->with('success', $message);
            } else {
                return back()->with('error', 'Aucun module n\'a pu être importé. Vérifiez les erreurs.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Module import error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Map JSON fields to database columns
     */
    private function mapJsonToDatabase($moduleData)
    {
        $mapped = [];

        // Direct mapping for all fields (convert to lowercase)
        $fieldMappings = [
            'cod_elp', 'cod_cmp', 'cod_nel', 'cod_pel', 'lib_elp', 'lic_elp', 'lib_cmt_elp',
            'dat_cre_elp', 'dat_mod_elp', 'dat_deb_ope_ipe', 'dat_fin_ope_ipe',
            'nbr_vol_elp', 'cod_vol_elp', 'nbr_pnt_ect_elp', 'eta_elp', 'lib_lie_elp', 'lib_nom_rsp_elp',
            'nbr_adm_elp', 'nbr_adm_fra', 'nbr_adm_etr',
            'not_obt_elp_num', 'not_obt_elp_den', 'not_min_rpt_elp_num', 'not_min_rpt_elp_den',
            'not_min_adm_num', 'not_min_adm_den', 'not_max_adm_num', 'not_max_adm_den',
            'tem_elp_cap', 'tem_rei_ipe_acq', 'tem_sus_elp', 'lib_sus_elp', 'tem_rel_pos_syt',
            'tem_sca_elp', 'tem_elp_prm_niv', 'tem_not_elp', 'bar_sai_elp', 'tem_pnt_jur_elp',
            'tem_mnd_elp', 'cod_cfm', 'tem_res_elp', 'tem_jur_elp', 'tem_ctl_val_cad_elp',
            'tem_anl_rpt_elp', 'not_min_rpt_elp', 'bar_min_rpt_elp', 'tem_con_elp', 'dur_con_elp',
            'not_min_con_elp', 'bar_min_con_elp', 'tem_cap_elp', 'tem_ses_uni', 'tem_adi', 'tem_ado',
            'tem_heu_ens_elp', 'cod_scc', 'nbr_eff_prv_elp', 'nbr_heu_cm_elp', 'nbr_heu_td_elp',
            'nbr_heu_tp_elp', 'tem_mcc_elp', 'tem_rpt_dsc_elp', 'cod_pan_1', 'cod_pan_2',
            'cod_pan_3', 'cod_pan_4', 'lib_elp_arb', 'lic_elp_arb', 'lib_elp_arb_fixed'
        ];

        foreach ($fieldMappings as $field) {
            $lowerField = strtolower($field);
            if (isset($moduleData[$lowerField])) {
                $mapped[$field] = $moduleData[$lowerField];
            }
        }

        return $mapped;
    }

    /**
     * Show import results
     */
    public function importResults()
    {
        if (!session()->has('import_stats')) {
            return redirect()->route('admin.modules.import')
                           ->with('error', 'Aucun résultat d\'import trouvé.');
        }

        return view('admin.modules.import-results');
    }
}
