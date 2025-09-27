<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'name',
        'thumbnail',
        'video',
        'description'
    ];
    
    protected $casts = [
        'video' => 'json',
    ];

    public function course()
{
    return $this->belongsTo(\App\Models\Course::class);
}



}
