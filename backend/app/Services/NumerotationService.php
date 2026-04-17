<?php
namespace App\Services;

use App\Models\SequenceNumerotation;
use App\Models\Entreprise;
use Illuminate\Support\Facades\DB;

/**
 * Service de numérotation automatique des documents.
 * Gère les séquences par type de document, par mois/année, avec verrouillage transactionnel.
 */
class NumerotationService
{
    /**
     * Génère le prochain numéro pour un type de document donné.
     */
    public function generer(int $tenantId, string $typeDocument, ?\DateTime $date = null): string
    {
        $date = $date ?? now();
        $annee = (int) $date->format('Y');
        $mois  = (int) $date->format('m');
        
        return DB::transaction(function () use ($tenantId, $typeDocument, $date, $annee, $mois) {
            // Récupérer ou créer la séquence avec verrouillage
            $sequence = SequenceNumerotation::lockForUpdate()->firstOrCreate(
                [
                    'tenant_id'     => $tenantId,
                    'type_document' => $typeDocument,
                    'prefixe'       => $this->getPrefixe($typeDocument),
                    'annee'         => $annee,
                    'mois'          => $mois,
                ],
                ['dernier_numero' => 0]
            );
            
            $sequence->dernier_numero++;
            $sequence->save();
            
            $seq = str_pad($sequence->dernier_numero, 4, '0', STR_PAD_LEFT);
            
            // Récupérer le format personnalisé de l'entreprise
            $format = $this->getFormat($tenantId, $typeDocument);
            
            return str_replace(
                ['{YYYY}', '{YY}', '{MM}', '{DD}', '{SEQ}'],
                [$annee, $date->format('y'), $date->format('m'), $date->format('d'), $seq],
                $format
            );
        });
    }

    private function getFormat(int $tenantId, string $type): string
    {
        $entreprise = Entreprise::where('tenant_id', $tenantId)->first();
        
        if ($entreprise) {
            $field = match($type) {
                'FACTURE'  => 'format_numero_facture',
                'DEVIS'    => 'format_numero_devis',
                'COMMANDE' => 'format_numero_cmd',
                'BL'       => 'format_numero_bl',
                'BR'       => 'format_numero_br',
                'AVOIR'    => 'format_numero_avoir',
                default    => null,
            };
            if ($field && $entreprise->$field) {
                return $entreprise->$field;
            }
        }
        
        return $this->getDefaultFormat($type);
    }

    private function getDefaultFormat(string $type): string
    {
        return match($type) {
            'FACTURE'  => 'FAC-{YYYY}{MM}-{SEQ}',
            'DEVIS'    => 'DV-{YYYY}{MM}{DD}-{SEQ}',
            'COMMANDE' => 'CMD-{YYYY}{MM}{DD}-{SEQ}',
            'BL'       => 'BL-{YYYY}{MM}{DD}-{SEQ}',
            'BR'       => 'BR-{YYYY}{MM}{DD}-{SEQ}',
            'AVOIR'    => 'AV-{YYYY}{MM}-{SEQ}',
            'DETTE'    => 'DT-{YYYY}{MM}{DD}-{SEQ}',
            default    => 'DOC-{YYYY}{MM}-{SEQ}',
        };
    }
    
    private function getPrefixe(string $type): string
    {
        return match($type) {
            'FACTURE'  => 'FAC',
            'DEVIS'    => 'DV',
            'COMMANDE' => 'CMD',
            'BL'       => 'BL',
            'BR'       => 'BR',
            'AVOIR'    => 'AV',
            'DETTE'    => 'DT',
            default    => 'DOC',
        };
    }
}
