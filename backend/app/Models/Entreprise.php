<?php
namespace App\Models;

use App\Traits\BelongsToTenant;

class Entreprise extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'entreprise';

    protected $fillable = [
        'tenant_id', 'raison_sociale', 'forme_juridique', 'ice', 'rc', 'patente',
        'if_fiscal', 'cnss', 'adresse', 'ville', 'code_postal', 'pays',
        'telephone', 'fax', 'email', 'site_web', 'logo_path', 'rib', 'banque',
        'devise_id', 'exercice_debut',
        'format_numero_facture', 'format_numero_devis', 'format_numero_cmd',
        'format_numero_bl', 'format_numero_br', 'format_numero_avoir',
        'entete_impression', 'pied_page_impression', 'conditions_generales',
    ];

    public function devise() { return $this->belongsTo(Devise::class); }
}
