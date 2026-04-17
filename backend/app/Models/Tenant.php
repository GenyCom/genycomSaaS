<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;
    
    // Set to central database
    protected $connection = 'central';

    protected $fillable = [
        'nom', 
        'database_name', 
        'domain', 
        'logo', 
        'statut'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role_id', 'is_owner')
                    ->withTimestamps();
    }
}
