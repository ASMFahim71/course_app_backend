<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'age', 'avatar', 'type', 'open_id', 'access_token', 'token'];
}
