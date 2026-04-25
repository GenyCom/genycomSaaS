<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference'             => 'nullable|string|max:50|unique:tenant.produits,reference',
            'designation'           => 'required|string|max:255',
            'detail'                => 'nullable|string',
            'is_service'            => 'boolean',
            'is_actif'              => 'boolean',
            'famille_id'            => 'nullable|integer|exists:tenant.famille_produit,id',
            'fournisseur_id'        => 'nullable|integer|exists:tenant.fournisseurs,id',
            'reference_fournisseur' => 'nullable|string|max:50',
            'prix_ht_achat'         => 'nullable|numeric|min:0',
            'prix_ht_vente'         => 'required|numeric|min:0',
            'taux_tva'              => 'nullable|numeric|min:0|max:100',
            'prix_ttc_achat'        => 'nullable|numeric|min:0',
            'prix_ttc_vente'        => 'nullable|numeric|min:0',
            'prix_revient'          => 'nullable|numeric|min:0',
            'marge_pourcentage'     => 'nullable|numeric',
            'code_barre'            => 'nullable|string|max:50',
            'marque'                => 'nullable|string|max:100',
            'unite'                 => 'nullable|string|max:50',
            'image_path'            => 'nullable|string|max:500',
            'image_upload'          => 'nullable|image|max:5120',
            'stock_initial'         => 'nullable|numeric|min:0',
            'stock_actuel'          => 'nullable|numeric|min:0',
            'stock_min'             => 'nullable|numeric|min:0',
            'stock_max'             => 'nullable|numeric|min:0',
            'seuil_alerte'          => 'nullable|numeric|min:0',
            'emplacement_stock'     => 'nullable|string|max:100',
            'delai_appro'           => 'nullable|integer|min:0',
            'poids'                 => 'nullable|numeric|min:0',
            'dimensions'            => 'nullable|string|max:100',
            'is_perissable'         => 'boolean',
            'is_lot'                => 'boolean',
            'garantie_mois'         => 'nullable|integer|min:0',
        ];
    }
}
