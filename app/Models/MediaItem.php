<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    protected $fillable = [
        'file_path',
        'type',
        'taken_at',
        'description',
        'tag',
    ];

    protected $casts = [
        'taken_at' => 'date',
    ];
}
