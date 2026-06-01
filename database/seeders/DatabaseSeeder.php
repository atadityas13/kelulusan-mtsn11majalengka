<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Administrator User
        User::updateOrCreate(
            ['email' => 'admin@mtsn11majalengka.sch.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
            ]
        );

        // 2. Seed Initial Active Academic Year
        AcademicYear::updateOrCreate(
            ['year' => '2024/2025'],
            [
                'is_active' => true,
                'target_date' => '2025-06-02 15:00:00',
                'maintenance_mode' => false,
            ]
        );
    }
}
?>
