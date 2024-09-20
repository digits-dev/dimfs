<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $fillable = [
        'user_id',
        'password',
    ]; 
}
