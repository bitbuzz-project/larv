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
                'max:51200', // 50MB max (increased from 10MB)
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $content = file_get_contents($value->getPathname());
                        $data = json_decode($content, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Le fichier JSON est invalide.');
                            return;
                        }

                        // Handle different JSON structures
                        $studentsData = $this->extractStudentsData($data);

                        if (empty($studentsData)) {
                            $fail('Aucune donnée d\'étudiant trouvée dans le fichier.');
                            return;
                        }

                        if (count($studentsData) > 20000) { // Increased from 1000
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
     * Extract students data from various JSON structures
     */
    private function extractStudentsData($data): array
    {
        $studentsData = [];

        if (isset($data['results']) && is_array($data['results'])) {
            // Oracle export format
            foreach ($data['results'] as $result) {
                if (isset($result['items']) && is_array($result['items'])) {
                    $columns = $result['columns'] ?? [];
                    $columnNames = array_column($columns, 'name');

                    foreach ($result['items'] as $item) {
                        if (is_array($item)) {
                            $studentRow = [];
                            foreach ($item as $index => $value) {
                                if (isset($columnNames[$index])) {
                                    $studentRow[$columnNames[$index]] = $value;
                                }
                            }
                            $studentsData[] = $studentRow;
                        } elseif (is_array($item) || is_object($item)) {
                            $studentsData[] = (array) $item;
                        }
                    }
                }
            }
        } elseif (is_array($data)) {
            // Simple array format
            $studentsData = $data;
        } elseif (isset($data['data']) && is_array($data['data'])) {
            // Data wrapped in 'data' property
            $studentsData = $data['data'];
        } elseif (isset($data['items']) && is_array($data['items'])) {
            // Data wrapped in 'items' property
            $studentsData = $data['items'];
        } else {
            // Try to find any array in the object
            foreach ($data as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $studentsData = $value;
                    break;
                }
            }
        }

        return $studentsData;
    }

    /**
     * Validate student structure
     */
    private function validateStudentStructure($student, $index): bool
    {
        if (!is_array($student)) {
            return false;
        }

        // Required fields with alternatives
        $requiredFields = [
            ['COD_ETU', 'COD_ETU_1'],
            ['LIB_NOM_PAT_IND', 'LIB_NOM_PAT_IND_1'],
            ['LIB_PR1_IND', 'LIB_PR1_IND_1']
        ];

        foreach ($requiredFields as $fieldGroup) {
            $hasField = false;
            foreach ($fieldGroup as $field) {
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
