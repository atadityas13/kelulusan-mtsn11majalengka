<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('nomor_peserta', 50);
            $table->string('nisn', 20);
            $table->string('nama', 150);
            $table->string('jenis_kelamin', 20)->default('Laki-laki');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir');
            $table->string('kelas', 20);
            $table->string('status_kelulusan', 30)->default('Lulus'); // Lulus, Tidak Lulus, Ditangguhkan
            $table->dateTime('release_timestamp')->nullable(); // Rilis bertahap (batch release)
            $table->timestamps();

            // Kombinasi kredensial per tahun ajaran harus unik
            $table->unique(['nomor_peserta', 'academic_year_id']);
            $table->unique(['nisn', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
?>
