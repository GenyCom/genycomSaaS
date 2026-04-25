<?php
/**
 * GenyCom Web SaaS — Configuration principale
 */
return [
    'name' => 'GenyCom',
    'version' => '1.0.0',
    
    // Devise par défaut pour les nouveaux tenants
    'devise_defaut' => [
        'nom' => 'Dirham Marocain',
        'code_iso' => 'MAD',
        'symbole' => 'DH',
    ],
    
    // TVA par défaut (Maroc)
    'tva_defaut' => [
        ['taux' => 20.000, 'libelle' => 'TVA 20%'],
        ['taux' => 14.000, 'libelle' => 'TVA 14%'],
        ['taux' => 10.000, 'libelle' => 'TVA 10%'],
        ['taux' => 7.000,  'libelle' => 'TVA 7%'],
        ['taux' => 0.000,  'libelle' => 'Exonéré'],
    ],
    
    // Modes de règlement par défaut
    'modes_reglement_defaut' => [
        'Espèces', 'Chèque', 'Carte bancaire', 'Virement', 'Effet de commerce',
    ],
    
    // Modes de livraison 
    'modes_livraison_defaut' => [
        'Normal', 'Recommandé', 'Transporteur', 'Retrait sur place',
    ],
    
    // Conditions de règlement
    'conditions_reglement_defaut' => [
        ['libelle' => 'Paiement immédiat', 'jours' => 0],
        ['libelle' => 'À réception',       'jours' => 1],
        ['libelle' => 'Net 15 jours',      'jours' => 15],
        ['libelle' => 'Net 30 jours',      'jours' => 30],
        ['libelle' => 'Net 60 jours',      'jours' => 60],
        ['libelle' => 'Net 90 jours',      'jours' => 90],
    ],
    
    // États des documents
    'etats_documents' => [
        'facture' => [
            ['code' => 'BRL', 'libelle' => 'Brouillon',  'couleur' => '#9ca3af', 'ordre' => 1],
            ['code' => 'OVR', 'libelle' => 'Ouverte',    'couleur' => '#3b82f6', 'ordre' => 2],
            ['code' => 'PAY', 'libelle' => 'Payée',      'couleur' => '#22c55e', 'ordre' => 3],
            ['code' => 'PPY', 'libelle' => 'Partielle',  'couleur' => '#f59e0b', 'ordre' => 4],
            ['code' => 'RTD', 'libelle' => 'En retard',  'couleur' => '#ef4444', 'ordre' => 5],
            ['code' => 'ANL', 'libelle' => 'Annulée',    'couleur' => '#6b7280', 'ordre' => 6],
        ],
        'devis' => [
            ['code' => 'BRL', 'libelle' => 'Brouillon',  'couleur' => '#9ca3af', 'ordre' => 1],
            ['code' => 'ENV', 'libelle' => 'Envoyé',     'couleur' => '#3b82f6', 'ordre' => 2],
            ['code' => 'ACC', 'libelle' => 'Accepté',    'couleur' => '#22c55e', 'ordre' => 3],
            ['code' => 'REF', 'libelle' => 'Refusé',     'couleur' => '#ef4444', 'ordre' => 4],
            ['code' => 'EXP', 'libelle' => 'Expiré',     'couleur' => '#6b7280', 'ordre' => 5],
            ['code' => 'FAC', 'libelle' => 'Facturé',    'couleur' => '#8b5cf6', 'ordre' => 6],
        ],
        'commande' => [
            ['code' => 'ENC', 'libelle' => 'En cours',   'couleur' => '#f59e0b', 'ordre' => 1],
            ['code' => 'OVR', 'libelle' => 'Ouverte',    'couleur' => '#3b82f6', 'ordre' => 2],
            ['code' => 'REC', 'libelle' => 'Reçue',      'couleur' => '#22c55e', 'ordre' => 3],
            ['code' => 'ANL', 'libelle' => 'Annulée',    'couleur' => '#6b7280', 'ordre' => 4],
        ],
        'dette' => [
            ['code' => 'BRL', 'libelle' => 'Brouillon',  'couleur' => '#9ca3af', 'ordre' => 1],
            ['code' => 'OVR', 'libelle' => 'Ouverte',    'couleur' => '#3b82f6', 'ordre' => 2],
            ['code' => 'PPY', 'libelle' => 'Partielle',  'couleur' => '#f59e0b', 'ordre' => 3],
            ['code' => 'PAY', 'libelle' => 'Soldée',     'couleur' => '#22c55e', 'ordre' => 4],
            ['code' => 'RTD', 'libelle' => 'En retard',  'couleur' => '#ef4444', 'ordre' => 5],
            ['code' => 'ANL', 'libelle' => 'Annulée',    'couleur' => '#6b7280', 'ordre' => 6],
        ],
        'depense' => [
            ['code' => 'BRL', 'libelle' => 'Brouillon',  'couleur' => '#9ca3af', 'ordre' => 1],
            ['code' => 'PAY', 'libelle' => 'Payée',      'couleur' => '#22c55e', 'ordre' => 2],
            ['code' => 'RTD', 'libelle' => 'En retard',  'couleur' => '#ef4444', 'ordre' => 3],
            ['code' => 'ANL', 'libelle' => 'Annulée',    'couleur' => '#6b7280', 'ordre' => 4],
        ],
    ],

    // Plans SaaS
    'plans' => [
        'free'       => ['max_users' => 2,  'max_produits' => 100,   'stockage_mb' => 100],
        'starter'    => ['max_users' => 5,  'max_produits' => 1000,  'stockage_mb' => 1000],
        'pro'        => ['max_users' => 15, 'max_produits' => 10000, 'stockage_mb' => 5000],
        'enterprise' => ['max_users' => -1, 'max_produits' => -1,    'stockage_mb' => 50000],
    ],
    
    // Catégories de dépenses par défaut
    'categories_depenses_defaut' => [
        ['libelle' => 'Charges variables', 'enfants' => [
            'Fête & Cadeaux', 'Frais de sous-traitance', 'Commissions', 'Achat de marchandises',
        ]],
        ['libelle' => 'Charges fixes', 'enfants' => [
            'Loyer', 'Eau, Électricité, Abonnement', 'Frais de carburant', 'Frais restauration',
        ]],
        ['libelle' => 'Autres', 'enfants' => []],
    ],
    // ─── Monitoring & Alerting ───────────────────────────────────
    'monitoring' => [
        // Activer/Désactiver l'envoi des rapports d'exceptions
        'enabled' => env('EXCEPTION_MAIL_ENABLED', true),

        // Adresse email du destinataire des rapports
        'recipient_email' => env('EXCEPTION_MAIL_TO', 'genycomc@gmail.com'),

        // Cooldown anti-spam : délai minimum (en minutes) entre deux envois
        // pour la même erreur (même classe + message + fichier + ligne)
        'cooldown_minutes' => env('EXCEPTION_MAIL_COOLDOWN', 15),

        // Nom de la queue dédiée au monitoring (séparée de la queue par défaut)
        'queue_name' => env('EXCEPTION_MAIL_QUEUE', 'monitoring'),
    ],
];
