<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projetId = $this->route('projet');

        return [
            'code_projet'       => 'sometimes|string|max:50|unique:tenant.projets,code_projet,' . $projetId,
            'nom_projet'        => 'sometimes|string|max:255',
            'client_id'         => 'nullable|integer|exists:tenant.clients,id',
            'type_projet'       => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'date_debut'        => 'nullable|date',
            'date_fin_prevue'   => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle'   => 'nullable|date',
            'budget_prevu'      => 'nullable|numeric|min:0',
            'budget_consomme'   => 'nullable|numeric|min:0',
            'devise_id'         => 'nullable|integer|exists:tenant.devise,id',
            'taux_change_document' => 'nullable|numeric|min:0',
            'statut'            => 'nullable|string|in:brouillon,en_cours,en_pause,termine,annule',
            'avancement_pcent'  => 'nullable|integer|min:0|max:100',
            'priorite'          => 'nullable|string|in:basse,normale,haute,urgente',
            'responsable_id'    => 'nullable|integer',
        ];
    }
}
