<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'cms_users';

    protected $fillable = [
        'name', 
        'email', 
        'password',
        'updated_password_at',
        'waive_count'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
