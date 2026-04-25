<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Reglement extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'reglements';

    protected $fillable = [
        'tenant_id',
        'payable_type',
        'payable_id',
        'date_reglement',
        'montant',
        'mode_reglement_id',
        'numero_cheque',
        'banque',
        'reference_virement',
        'observations',
        'created_by'
    ];

    protected $casts = [
        'date_reglement' => 'date',
        'montant' => 'decimal:2'
    ];

    public function payable()       { return $this->morphTo(); }
    public function modeReglement() { return $this->belongsTo(ModeReglement::class); }
    public function auteur()        { return $this->belongsTo(User::class, 'created_by'); }
}
