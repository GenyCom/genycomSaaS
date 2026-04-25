<?php
// Script simplifié sans bootstrap complet si possible
$conn = mysqli_connect("localhost", "root", "", "geny_hp");

// 1. Trouver l'ID de l'entrepôt par défaut
$res = mysqli_query($conn, "SELECT id FROM entrepots WHERE is_default = 1 LIMIT 1");
$row = mysqli_fetch_assoc($res);
$defaultId = $row['id'];

if (!$defaultId) {
    die("Aucun entrepôt par défaut trouvé.");
}

echo "Fusion vers l'entrepôt $defaultId...\n";

// 2. Récupérer les stocks orphelins
$res = mysqli_query($conn, "SELECT * FROM stocks WHERE entrepot_id IS NULL");
while ($orphan = mysqli_fetch_assoc($res)) {
    $pid = $orphan['produit_id'];
    $qty = $orphan['quantite'];
    
    // Vérifier si existe déjà dans le dépôt principal
    $check = mysqli_query($conn, "SELECT id, quantite FROM stocks WHERE produit_id = $pid AND entrepot_id = $defaultId");
    $existing = mysqli_fetch_assoc($check);
    
    if ($existing) {
        $newQty = $existing['quantite'] + $qty;
        mysqli_query($conn, "UPDATE stocks SET quantite = $newQty WHERE id = {$existing['id']}");
        mysqli_query($conn, "DELETE FROM stocks WHERE id = {$orphan['id']}");
        echo "Produit $pid: Fusionné ($newQty total)\n";
    } else {
        mysqli_query($conn, "UPDATE stocks SET entrepot_id = $defaultId WHERE id = {$orphan['id']}");
        echo "Produit $pid: Assigné au dépôt principal\n";
    }
}
echo "Terminé.";
