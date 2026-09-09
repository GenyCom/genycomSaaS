<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PATCH DATABASE : SYNCHRONISATION DU DICTIONNAIRE DES PERMISSIONS ===\n";

$permissionsToSync = [
    // Dashboard & Clients & Fournisseurs & Produits
    ['name' => 'dashboard.view', 'display_name' => 'Voir le tableau de bord', 'module' => 'dashboard'],
    ['name' => 'clients.view', 'display_name' => 'Voir les clients', 'module' => 'clients'],
    ['name' => 'clients.create', 'display_name' => 'Créer un client', 'module' => 'clients'],
    ['name' => 'clients.edit', 'display_name' => 'Modifier un client', 'module' => 'clients'],
    ['name' => 'clients.delete', 'display_name' => 'Supprimer un client', 'module' => 'clients'],
    ['name' => 'fournisseurs.view', 'display_name' => 'Voir les fournisseurs', 'module' => 'fournisseurs'],
    ['name' => 'fournisseurs.create', 'display_name' => 'Créer un fournisseur', 'module' => 'fournisseurs'],
    ['name' => 'fournisseurs.edit', 'display_name' => 'Modifier un fournisseur', 'module' => 'fournisseurs'],
    ['name' => 'fournisseurs.delete', 'display_name' => 'Supprimer un fournisseur', 'module' => 'fournisseurs'],
    ['name' => 'produits.view', 'display_name' => 'Voir les produits', 'module' => 'produits'],
    ['name' => 'produits.create', 'display_name' => 'Créer un produit', 'module' => 'produits'],
    ['name' => 'produits.edit', 'display_name' => 'Modifier un produit', 'module' => 'produits'],
    ['name' => 'produits.delete', 'display_name' => 'Supprimer un produit', 'module' => 'produits'],

    // Ventes
    ['name' => 'devis.view', 'display_name' => 'Voir les devis', 'module' => 'ventes'],
    ['name' => 'devis.create', 'display_name' => 'Créer un devis', 'module' => 'ventes'],
    ['name' => 'devis.edit', 'display_name' => 'Modifier un devis', 'module' => 'ventes'],
    ['name' => 'devis.delete', 'display_name' => 'Supprimer un devis', 'module' => 'ventes'],
    ['name' => 'devis.transform', 'display_name' => 'Transformer un devis en facture/BL/BCC', 'module' => 'ventes'],
    ['name' => 'bons-commande-client.view', 'display_name' => 'Voir les bons de commande client', 'module' => 'ventes'],
    ['name' => 'bons-commande-client.create', 'display_name' => 'Créer un bon de commande client', 'module' => 'ventes'],
    ['name' => 'bons-commande-client.edit', 'display_name' => 'Modifier un bon de commande client', 'module' => 'ventes'],
    ['name' => 'bons-commande-client.delete', 'display_name' => 'Supprimer un bon de commande client', 'module' => 'ventes'],
    ['name' => 'bons-livraison.view', 'display_name' => 'Voir les bons de livraison', 'module' => 'ventes'],
    ['name' => 'bons-livraison.create', 'display_name' => 'Créer un bon de livraison', 'module' => 'ventes'],
    ['name' => 'bons-livraison.edit', 'display_name' => 'Modifier un bon de livraison', 'module' => 'ventes'],
    ['name' => 'bons-livraison.delete', 'display_name' => 'Supprimer un bon de livraison', 'module' => 'ventes'],
    ['name' => 'factures.view', 'display_name' => 'Voir les factures de vente', 'module' => 'ventes'],
    ['name' => 'factures.create', 'display_name' => 'Créer une facture de vente', 'module' => 'ventes'],
    ['name' => 'factures.edit', 'display_name' => 'Modifier une facture de vente', 'module' => 'ventes'],
    ['name' => 'factures.delete', 'display_name' => 'Supprimer une facture de vente', 'module' => 'ventes'],
    ['name' => 'factures.import', 'display_name' => 'Importer des lignes de facture', 'module' => 'ventes'],
    ['name' => 'avoirs-clients.view', 'display_name' => 'Voir les avoirs clients', 'module' => 'ventes'],
    ['name' => 'avoirs-clients.create', 'display_name' => 'Créer un avoir client', 'module' => 'ventes'],
    ['name' => 'contrats.view', 'display_name' => 'Voir les contrats & abonnements', 'module' => 'ventes'],
    ['name' => 'contrats.create', 'display_name' => 'Créer un contrat', 'module' => 'ventes'],
    ['name' => 'contrats.edit', 'display_name' => 'Modifier un contrat', 'module' => 'ventes'],

    // Achats
    ['name' => 'commandes.view', 'display_name' => 'Voir les commandes fournisseur', 'module' => 'achats'],
    ['name' => 'commandes.create', 'display_name' => 'Créer une commande fournisseur', 'module' => 'achats'],
    ['name' => 'commandes.edit', 'display_name' => 'Modifier une commande fournisseur', 'module' => 'achats'],
    ['name' => 'commandes.delete', 'display_name' => 'Supprimer une commande fournisseur', 'module' => 'achats'],
    ['name' => 'bons-reception.view', 'display_name' => 'Voir les bons de réception', 'module' => 'achats'],
    ['name' => 'bons-reception.create', 'display_name' => 'Créer un bon de réception', 'module' => 'achats'],
    ['name' => 'bons-reception.edit', 'display_name' => 'Modifier un bon de réception', 'module' => 'achats'],
    ['name' => 'factures-achats.view', 'display_name' => 'Voir les factures d\'achat', 'module' => 'achats'],
    ['name' => 'factures-achats.create', 'display_name' => 'Créer une facture d\'achat', 'module' => 'achats'],
    ['name' => 'factures-achats.edit', 'display_name' => 'Modifier une facture d\'achat', 'module' => 'achats'],
    ['name' => 'avoirs-fournisseurs.view', 'display_name' => 'Voir les avoirs fournisseurs', 'module' => 'achats'],
    ['name' => 'avoirs-fournisseurs.create', 'display_name' => 'Créer un avoir fournisseur', 'module' => 'achats'],

    // Stock
    ['name' => 'stock.view', 'display_name' => 'Voir le stock', 'module' => 'stock'],
    ['name' => 'stock.mouvement', 'display_name' => 'Effectuer un mouvement de stock', 'module' => 'stock'],
    ['name' => 'stock.inventaire', 'display_name' => 'Gérer les inventaires', 'module' => 'stock'],
    ['name' => 'stock.initialisation_complete', 'display_name' => 'Initialisation complète du stock', 'module' => 'stock'],

    // Finances
    ['name' => 'reglements.view', 'display_name' => 'Voir les règlements', 'module' => 'finances'],
    ['name' => 'reglements.create', 'display_name' => 'Enregistrer un règlement', 'module' => 'finances'],
    ['name' => 'depenses.view', 'display_name' => 'Voir les dépenses', 'module' => 'finances'],
    ['name' => 'depenses.create', 'display_name' => 'Créer une dépense', 'module' => 'finances'],
    ['name' => 'dettes.view', 'display_name' => 'Voir les dettes fournisseur', 'module' => 'finances'],
    ['name' => 'caisse.view', 'display_name' => 'Voir la trésorerie et la caisse', 'module' => 'finances'],

    // Projets & Reporting & Paramétrage
    ['name' => 'projets.view', 'display_name' => 'Voir les projets', 'module' => 'projets'],
    ['name' => 'projets.create', 'display_name' => 'Créer un projet', 'module' => 'projets'],
    ['name' => 'projets.edit', 'display_name' => 'Modifier un projet', 'module' => 'projets'],
    ['name' => 'reporting.view', 'display_name' => 'Voir les rapports et analyses', 'module' => 'reporting'],
    ['name' => 'parametrage.view', 'display_name' => 'Voir les paramètres', 'module' => 'parametrage'],
    ['name' => 'parametrage.edit', 'display_name' => 'Modifier les paramètres', 'module' => 'parametrage'],
    ['name' => 'users.manage', 'display_name' => 'Gérer les sous-comptes', 'module' => 'parametrage'],
    ['name' => 'roles.manage', 'display_name' => 'Gérer les rôles & habilitations', 'module' => 'parametrage'],
];

$addedCount = 0;
foreach ($permissionsToSync as $p) {
    $exists = DB::connection('central')->table('permissions')->where('name', $p['name'])->exists();
    if (!$exists) {
        DB::connection('central')->table('permissions')->insert([
            'name'         => $p['name'],
            'display_name' => $p['display_name'],
            'module'       => $p['module'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $addedCount++;
        echo "➕ Permission ajoutée : {$p['name']} ({$p['display_name']})\n";
    }
}

echo "✅ Total permissions ajoutées : {$addedCount}\n";

$totalPerms = DB::connection('central')->table('permissions')->count();
echo "📊 Nombre total de permissions dans le dictionnaire central : {$totalPerms}\n";

echo "=== PATCH TERMINÉ AVEC SUCCÈS ===\n";
