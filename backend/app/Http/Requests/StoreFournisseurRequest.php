<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'societe'               => 'required|string|max:255',
            'code_fournisseur'      => 'nullable|string|max:50|unique:tenant.fournisseurs,code_fournisseur',
            'is_personne_physique'  => 'boolean',
            'civilite'              => 'nullable|string|max:10',
            'nom'                   => 'nullable|string|max:100',
            'prenom'                => 'nullable|string|max:100',
            'ice'                   => 'nullable|string|max:30',
            'rc'                    => 'nullable|string|max:50',
            'if_fiscal'             => 'nullable|string|max:50',
            'patente'               => 'nullable|string|max:50',
            'adresse'               => 'nullable|string|max:500',
            'ville'                 => 'nullable|string|max:100',
            'code_postal'           => 'nullable|string|max:20',
            'pays'                  => 'nullable|string|max:100',
            'telephone'             => 'nullable|string|max:20',
            'mobile'                => 'nullable|string|max:20',
            'fax'                   => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'site_web'              => 'nullable|string|max:255',
            'rib'                   => 'nullable|string|max:50',
            'banque'                => 'nullable|string|max:100',
            'observations'          => 'nullable|string',
            'type_fournisseur_id'   => 'nullable|integer|exists:tenant.type_fournisseur,id',
            'delai_livraison'       => 'nullable|integer|min:0',
            'is_active'             => 'boolean',
        ];
    }
}
