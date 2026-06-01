<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherMessage extends Model
{
    protected $fillable = [
        'uid',
        'name',
        'message',
        'likes',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Relasi ke komentar (comments).
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'item_uid', 'uid');
    }
}
?>
