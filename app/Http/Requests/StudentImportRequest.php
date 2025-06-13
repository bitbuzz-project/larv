<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'json_file' => [
                'required',
                'file',
                'mimes:json',
                'max:51200', // 50MB max
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $content = file_get_contents($value->getPathname());
                        $data = json_decode($content, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Le fichier JSON est invalide.');
                            return;
                        }

                        // Handle array of student objects
                        $studentsData = [];

                        if (is_array($data)) {
                            $studentsData = $data;
                        } else {
                            $fail('Le fichier JSON doit contenir un tableau d\'étudiants.');
                            return;
                        }

                        if (empty($studentsData)) {
                            $fail('Aucune donnée d\'étudiant trouvée dans le fichier.');
                            return;
                        }

                        if (count($studentsData) > 20000) {
                            $fail('Le fichier ne peut pas contenir plus de 20000 étudiants à la fois.');
                            return;
                        }

                        // Validate structure of first few items
                        $sampleSize = min(5, count($studentsData));
                        for ($i = 0; $i < $sampleSize; $i++) {
                            if (!$this->validateStudentStructure($studentsData[$i], $i)) {
                                $fail("Structure invalide à l'index {$i}. Les champs requis sont manquants.");
                                return;
                            }
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Validate student structure - checking for both lowercase and uppercase field names
     */
    private function validateStudentStructure($student, $index): bool
    {
        if (!is_array($student)) {
            return false;
        }

        // Required fields with alternatives (both lowercase and uppercase)
        $requiredFieldSets = [
            // Student code
            ['cod_etu', 'cod_etu_1', 'COD_ETU', 'COD_ETU_1'],
            // Last name
            ['lib_nom_pat_ind', 'lib_nom_pat_ind_1', 'LIB_NOM_PAT_IND', 'LIB_NOM_PAT_IND_1'],
            // First name
            ['lib_pr1_ind', 'lib_pr1_ind_1', 'LIB_PR1_IND', 'LIB_PR1_IND_1']
        ];

        foreach ($requiredFieldSets as $fieldSet) {
            $hasField = false;
            foreach ($fieldSet as $field) {
                if (isset($student[$field]) && !empty($student[$field])) {
                    $hasField = true;
                    break;
                }
            }
            if (!$hasField) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'json_file.required' => 'Veuillez sélectionner un fichier JSON.',
            'json_file.file' => 'Le fichier sélectionné n\'est pas valide.',
            'json_file.mimes' => 'Seuls les fichiers JSON sont acceptés.',
            'json_file.max' => 'La taille du fichier ne doit pas dépasser 50MB.',
        ];
    }
}
