<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckHistory extends Model
{
    protected $table = 'check_histories';

    protected $fillable = [
        'academic_year_id',
        'nomor_peserta',
        'student_name',
        'result',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Relasi balik ke Tahun Ajaran (AcademicYear).
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
?>
