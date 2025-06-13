<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        Student::firstOrCreate(
            ['apoL_a01_code' => '16005333'],
            [
                'apoL_a02_nom' => 'Admin',
                'apoL_a03_prenom' => 'System',
                'apoL_a04_naissance' => '05/09/2000',
            ],
            [
                'apoL_a01_code' => '12345682',
                'apoL_a02_nom' => 'El Fassi',
                'apoL_a03_prenom' => 'Youssef',
                'apoL_a04_naissance' => '18/12/1998',
            ]
        ];

        foreach ($test_students as $student_data) {
            Student::firstOrCreate(
                ['apoL_a01_code' => $student_data['apoL_a01_code']],
                $student_data
            );
        }
    }
}' => '06/04/1987',
            ]
        );

        // Create some test students
        $test_students = [
            [
                'apoL_a01_code' => '12345678',
                'apoL_a02_nom' => 'Alami',
                'apoL_a03_prenom' => 'Mohammed',
                'apoL_a04_naissance' => '15/03/2000',
            ],
            [
                'apoL_a01_code' => '12345679',
                'apoL_a02_nom' => 'Benali',
                'apoL_a03_prenom' => 'Fatima',
                'apoL_a04_naissance' => '22/07/1999',
            ],
            [
                'apoL_a01_code' => '12345680',
                'apoL_a02_nom' => 'Chakir',
                'apoL_a03_prenom' => 'Ahmed',
                'apoL_a04_naissance' => '10/11/2001',
            ],
            [
                'apoL_a01_code' => '12345681',
                'apoL_a02_nom' => 'Driouech',
                'apoL_a03_prenom' => 'Zineb',
                'apoL_a04_naissance