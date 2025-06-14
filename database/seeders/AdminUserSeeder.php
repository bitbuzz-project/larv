<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        Student::firstOrCreate(
            ['apoL_a01_code' => '16005333'],
            [
                'apoL_a02_nom' => 'Admin',
                'apoL_a03_prenom' => 'System',
                'apoL_a04_naissance' => '06/04/1987',
            ]
        );

        echo "Admin user created successfully!\n";
        echo "Login with: 16005333 / 06/04/1987\n";
    }
}
