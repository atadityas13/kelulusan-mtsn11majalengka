<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'year',
        'is_active',
        'target_date',
        'maintenance_mode',
    ];

    /**
     * Relasi ke data siswa.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Relasi ke riwayat pengecekan.
     */
    public function checkHistories()
    {
        return $this->hasMany(CheckHistory::class);
    }
}
?>
