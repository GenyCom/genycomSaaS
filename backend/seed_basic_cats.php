<?php

    use Illuminate\Support\Facades\DB;

    // Type de client (Secteur / Domaine)
    $clients = [
        ['libelle' => 'Particulier (B2C)', 'exempt_tva' => 0, 'vip' => 0],
        ['libelle' => 'PME / TPE', 'exempt_tva' => 0, 'vip' => 0],
        ['libelle' => 'Grand Compte', 'exempt_tva' => 0, 'vip' => 1],
        ['libelle' => 'Administration Publique', 'exempt_tva' => 1, 'vip' => 0],
        ['libelle' => 'Revendeur / Distributeur', 'exempt_tva' => 0, 'vip' => 0],
    ];

    foreach ($clients as $c) {
        if (!DB::table('type_client')->where('libelle', $c['libelle'])->exists()) {
            DB::table('type_client')->insert($c);
        }
    }

    // Type de fournisseur (Domaine / Catégorie)
    $fournisseurs = [
        ['libelle' => 'Marchandises & Matières', 'detail' => 'Fournisseur de matières premières et produits finis', 'vip' => 0],
        ['libelle' => 'Matériel & Équipement', 'detail' => 'Fournisseur d\'équipements IT, machines, véhicules', 'vip' => 0],
        ['libelle' => 'Services & Prestations', 'detail' => 'Prestataire de services (Marketing, Consulting, IT)', 'vip' => 0],
        ['libelle' => 'Sous-traitant', 'detail' => 'Sous-traitance spécialisée ou BTP', 'vip' => 0],
        ['libelle' => 'Logistique & Transport', 'detail' => 'Prestataire de fret, transport et stockage', 'vip' => 0],
        ['libelle' => 'Fournitures (Consommables)', 'detail' => 'Fournitures de bureau, entretien, hygiène', 'vip' => 0],
    ];

    foreach ($fournisseurs as $f) {
        if (!DB::table('type_fournisseur')->where('libelle', $f['libelle'])->exists()) {
            DB::table('type_fournisseur')->insert($f);
        }
    }

    echo "Categories installees avec succes!\n";

?>
