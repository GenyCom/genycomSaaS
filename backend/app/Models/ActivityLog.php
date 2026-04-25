<?php

namespace App\Models;

class ActivityLog extends BaseModel
{
    protected $table = 'activity_log';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'properties' => 'json'
    ];

    public function user() { return $this->belongsTo(User::class); }
}
