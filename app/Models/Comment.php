<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'item_uid',
        'item_type',
        'author',
        'comment',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Relasi ke pesan guru atau testimoni.
     * Karena database memakai item_uid & item_type secara manual, 
     * kita dapat mendefinisikannya secara terpisah atau lewat kueri dinamis.
     */
    public function testimonial()
    {
        return $this->belongsTo(Testimonial::class, 'item_uid', 'uid');
    }

    public function teacherMessage()
    {
        return $this->belongsTo(TeacherMessage::class, 'item_uid', 'uid');
    }
}
?>
