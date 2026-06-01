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
        Schema::table('students', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('jenis_kelamin');
            $table->string('nomor_skl', 100)->nullable()->after('status_kelulusan');
        });

        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->unsignedSmallInteger('agama_al_quran_hadis')->nullable();
            $table->unsignedSmallInteger('agama_akidah_akhlak')->nullable();
            $table->unsignedSmallInteger('agama_fikih')->nullable();
            $table->unsignedSmallInteger('agama_sejarah_kebudayaan_islam')->nullable();
            $table->unsignedSmallInteger('pendidikan_pancasila')->nullable();
            $table->unsignedSmallInteger('bahasa_indonesia')->nullable();
            $table->unsignedSmallInteger('bahasa_arab')->nullable();
            $table->unsignedSmallInteger('matematika')->nullable();
            $table->unsignedSmallInteger('ilmu_pengetahuan_alam')->nullable();
            $table->unsignedSmallInteger('ilmu_pengetahuan_sosial')->nullable();
            $table->unsignedSmallInteger('bahasa_inggris')->nullable();
            $table->unsignedSmallInteger('seni_dan_prakarya')->nullable();
            $table->unsignedSmallInteger('pendidikan_jasmani_olahraga_dan_kesehatan')->nullable();
            $table->unsignedSmallInteger('informatika')->nullable();
            $table->unsignedSmallInteger('muatan_lokal_bahasa_sunda')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['foto', 'nomor_skl']);
        });
    }
};
