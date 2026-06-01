<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
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

        // 3. Seed Data Kepala Madrasah
        Setting::set('kepala_nama', 'H. Asep Awaludin, S.Pd., M.M.');
        Setting::set('kepala_jabatan', 'Kepala MTsN 11 Majalengka');
        Setting::set('kepala_pesan', 'Kami ucapkan selamat kepada seluruh siswa yang telah dinyatakan lulus. Semoga pencapaian ini menjadi batu lompatan menuju masa depan yang lebih cerah, gemilang, dan penuh keberkahan. Teruslah belajar, berprestasi, dan jaga nama baik almamater MTsN 11 Majalengka di manapun kalian berada.');
    }
}
?>
