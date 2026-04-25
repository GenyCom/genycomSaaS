<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'central';

    protected $fillable = ['name', 'description', 'is_system'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }
}