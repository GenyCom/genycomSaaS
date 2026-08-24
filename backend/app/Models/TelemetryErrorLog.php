<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetryErrorLog extends Model
{
    protected $table = 'telemetry_error_logs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'status_code',
        'message',
        'file',
        'line',
        'url',
        'method',
        'ip',
        'trace',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'line'        => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
