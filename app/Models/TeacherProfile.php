<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class TeacherProfile extends Model
{
    use HasApiTokens;
    protected $fillable = ['user_token', 'avatar', 'cover', 'rating',
     'downloads', 'total_students', 'experience_years', 'job'];
    public function member()
    {
        return $this->belongsTo(Member::class, 'user_token', 'token');
    }
}
