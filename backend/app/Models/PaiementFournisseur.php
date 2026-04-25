<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class PaiementFournisseur extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'paiements_fournisseurs';

    protected $fillable = [
        'tenant_id',
        'facture_achat_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'observations',
        'created_by'
    ];

    protected $casts = [
        'date_paiement' => 'date',
    ];

    public function facture()
    {
        return $this->belongsTo(FactureAchat::class, 'facture_achat_id');
    }
}
