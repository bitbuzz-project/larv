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
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $content = file_get_contents($value->getPathname());
                        $data = json_decode($content, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Le fichier JSON est invalide.');
                            return;
                        }

                        if (!is_array($data)) {
                            $fail('Le fichier JSON doit contenir un tableau d\'étudiants.');
                            return;
                        }

                        if (count($data) > 1000) {
                            $fail('Le fichier ne peut pas contenir plus de 1000 étudiants à la fois.');
                            return;
                        }

                        // Validate structure of first few items
                        $sampleSize = min(5, count($data));
                        for ($i = 0; $i < $sampleSize; $i++) {
                            if (!isset($data[$i]['apoL_a01_code']) ||
                                !isset($data[$i]['apoL_a02_nom']) ||
                                !isset($data[$i]['apoL_a03_prenom'])) {
                                $fail("Structure invalide à l'index {$i}. Les champs apoL_a01_code, apoL_a02_nom et apoL_a03_prenom sont requis.");
                                return;
                            }
                        }
                    }
                },
            ],
        ];
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
            'json_file.max' => 'La taille du fichier ne doit pas dépasser 10MB.',
        ];
    }
}
