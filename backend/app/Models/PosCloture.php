<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosCloture extends BaseModel
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'pos_clotures';

    protected $fillable = [
        'tenant_id',
        'numero',
        'date_cloture',
        'opened_at',
        'closed_at',
        'user_id',
        'nom_caissier',
        'fond_initial',
        'total_ventes',
        'total_especes',
        'total_carte',
        'total_cheque',
        'total_virement',
        'total_autre',
        'total_attendu',
        'total_declare',
        'ecart',
        'statut_ecart',
        'nb_ventes',
        'observations',
        'rapport_texte',
    ];

    protected $casts = [
        'date_cloture'   => 'date:Y-m-d',
        'opened_at'      => 'datetime',
        'closed_at'      => 'datetime',
        'fond_initial'   => 'decimal:2',
        'total_ventes'   => 'decimal:2',
        'total_especes'  => 'decimal:2',
        'total_carte'    => 'decimal:2',
        'total_cheque'   => 'decimal:2',
        'total_virement' => 'decimal:2',
        'total_autre'    => 'decimal:2',
        'total_attendu'  => 'decimal:2',
        'total_declare'  => 'decimal:2',
        'ecart'          => 'decimal:2',
        'nb_ventes'      => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
