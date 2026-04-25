<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntrepriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raison_sociale'    => 'required|string|max:255',
            'forme_juridique'   => 'nullable|string|max:100',
            'adresse'           => 'required|string|max:500',
            'ville'             => 'required|string|max:100',
            'code_postal'       => 'nullable|string|max:20',
            'pays'              => 'nullable|string|max:100',
            'telephone'         => 'nullable|string|max:20',
            'fax'               => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'site_web'          => 'nullable|string|max:255',
            'logo_path'         => 'nullable|string',
            'rib'               => 'nullable|string|max:50',
            'banque'            => 'nullable|string|max:100',
            'ice'               => 'nullable|string|max:30',
            'rc'                => 'nullable|string|max:50',
            'patente'           => 'nullable|string|max:50',
            'if_fiscal'         => 'nullable|string|max:50',
            'cnss'              => 'nullable|string|max:50',
            'devise_id'         => 'nullable|integer',
            'exercice_debut'    => 'nullable|integer|min:1|max:12',
            'format_numero_facture' => 'nullable|string|max:100',
            'format_numero_devis'   => 'nullable|string|max:100',
            'format_numero_cmd'     => 'nullable|string|max:100',
            'format_numero_bl'      => 'nullable|string|max:100',
            'format_numero_br'      => 'nullable|string|max:100',
            'format_numero_avoir'   => 'nullable|string|max:100',
            'entete_impression'     => 'nullable|string',
            'pied_page_impression'  => 'nullable|string',
            'conditions_generales'  => 'nullable|string',
        ];
    }
}
