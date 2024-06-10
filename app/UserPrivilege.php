<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPrivilege extends Model
{
    protected $table = 'cms_privileges';

    protected $fillable = [
        'name',
        'is_superadmin'
    ];

    public function scopePrivileges($query)
    {
        return $query->where('is_superadmin',0)->get();
    }
}
