<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = [
        'user_token',
        'name',
        'thumbnail',
        'video',
        'description',
        'type_id',
        'price',
        'lesson_num',
        'video_length',
        'follow',
        'score',
        'recommended'
    ];

    /**
     * Get the course type that owns the course.
     */
    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class, 'type_id');
    }
}
