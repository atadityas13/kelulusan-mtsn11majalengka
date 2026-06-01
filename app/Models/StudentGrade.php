<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicYear;

class StudentGrade extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'agama_al_quran_hadis',
        'agama_akidah_akhlak',
        'agama_fikih',
        'agama_sejarah_kebudayaan_islam',
        'pendidikan_pancasila',
        'bahasa_indonesia',
        'bahasa_arab',
        'matematika',
        'ilmu_pengetahuan_alam',
        'ilmu_pengetahuan_sosial',
        'bahasa_inggris',
        'seni_dan_prakarya',
        'pendidikan_jasmani_olahraga_dan_kesehatan',
        'informatika',
        'muatan_lokal_bahasa_sunda',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
