<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'uid',
        'name',
        'message',
        'likes',
        'status',
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
