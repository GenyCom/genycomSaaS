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
        
        return DB::connection('tenant')->transaction(function () use ($tenantId, $typeDocument, $date, $annee, $mois) {
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
                'BCC'      => 'format_numero_bcc',
                'COMMANDE' => 'format_numero_cmd',
                'BL'       => 'format_numero_bl',
                'BR'       => 'format_numero_br',
                'BCF'      => 'format_numero_bcf',
                'FACTURE_ACHAT' => 'format_numero_facture_achat',
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
            'DEVIS'    => 'DEV-{YYYY}{MM}-{SEQ}',
            'BCC'      => 'BCC-{YYYY}{MM}-{SEQ}',
            'COMMANDE' => 'CMD-{YYYY}{MM}-{SEQ}',
            'BL'       => 'BL-{YYYY}{MM}-{SEQ}',
            'BR'       => 'BR-{YYYY}{MM}-{SEQ}',
            'BCF'      => 'BCF-{YYYY}{MM}-{SEQ}',
            'AVOIR'    => 'AV-{YYYY}{MM}-{SEQ}',
            'DETTE'    => 'DT-{YYYY}{MM}-{SEQ}',
            'FACTURE_ACHAT' => 'FA-{YYYY}{MM}-{SEQ}',
            default    => 'DOC-{YYYY}{MM}-{SEQ}',
        };
    }
    
    private function getPrefixe(string $type): string
    {
        return match($type) {
            'FACTURE'  => 'FAC',
            'DEVIS'    => 'DEV',
            'BCC'      => 'BCC',
            'COMMANDE' => 'CMD',
            'BL'       => 'BL',
            'BR'       => 'BR',
            'BCF'      => 'BCF',
            'AVOIR'    => 'AV',
            'DETTE'    => 'DT',
            'FACTURE_ACHAT' => 'FA',
            default    => 'DOC',
        };
    }
}
