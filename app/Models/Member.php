<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasApiTokens, HasFactory;
    protected $fillable = ['name', 'email', 'age', 'avatar', 'type', 'open_id', 'access_token', 'token'];
}
