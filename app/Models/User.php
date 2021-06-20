<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory;
    use HasApiTokens;

    protected $table= 'users';
    
    protected $fillable =["id", "name", "email", "phoneno","user_type" ,"created_at", "updated_at"];

}
