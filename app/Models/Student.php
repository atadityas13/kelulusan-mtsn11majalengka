<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StudentGrade;

class Student extends Model
{
    protected $fillable = [
        'academic_year_id',
        'nomor_peserta',
        'nisn',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kelas',
        'foto',
        'status_kelulusan',
        'nomor_skl',
        'release_timestamp',
        'signature',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'release_timestamp' => 'datetime',
    ];

    /**
     * Relasi balik ke Tahun Ajaran (AcademicYear).
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grades()
    {
        return $this->hasOne(StudentGrade::class);
    }
}
?>
