<?php
/**
 * GenyCom Web SaaS —  Routes API
 */

use App\Http\Controllers\Api\{
    AuthController,
    DashboardController,
    ClientController,
    FactureController,
    AvoirClientController,
    AvoirFournisseurController,
    FournisseurController,
    ProduitController,
    ProduitFiniController,
    DevisController,
    CommandeController,
    StockController,
    ProjetController,
    DepenseController,
    ContratController,
    WorkflowVenteController,
    WorkflowAchatController,
    BonCommandeClientController,
    BonLivraisonController,
    BonReceptionController,
    DetteController,
    FactureAchatController,
    NotificationController,
    ReportingController,
};
use Illuminate\Support\Facades\Route;

// ─── Public Routes ──────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/monitoring/frontend-error', [\App\Http\Controllers\Api\FrontendErrorController::class, 'report']);

// ─── Protected Routes ───────────────────────────────────────
use App\Http\Controllers\Api\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\Api\SuperAdmin\SuperAdminTenantController;
use App\Http\Controllers\Api\SuperAdmin\SuperAdminDashboardController;

Route::middleware(['auth:sanctum', \App\Http\Middleware\TenantMiddleware::class])->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me/profile', [AuthController::class, 'updateProfile']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);
    
    // SuperAdmin Routes
    Route::prefix('superadmin')->group(function() {
        Route::get('dashboard', SuperAdminDashboardController::class);
        Route::get('dashboard-stats', [SuperAdminDashboardController::class, 'stats']);
        Route::post('users/{user}/impersonate', [SuperAdminUserController::class, 'impersonate']);
        Route::apiResource('users', SuperAdminUserController::class);
        Route::apiResource('tenants', SuperAdminTenantController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    });


    // Dashboard
    // Dashboard
    Route::prefix('dashboard')->middleware('permission:dashboard.view')->group(function () {
        Route::get('/kpis', [DashboardController::class, 'kpis']);
        Route::get('/ca-mensuel', [DashboardController::class, 'caMensuel']);
        Route::get('/top-ventes', [DashboardController::class, 'topVentes']);
        Route::get('/top-clients', [DashboardController::class, 'topClients']);
        Route::get('/echeances', [DashboardController::class, 'echeances']);
        Route::get('/alertes-stock', [DashboardController::class, 'alertesStock']);
        Route::get('/stock-stats', [DashboardController::class, 'stockStats']);
    });

    // Clients
    Route::get('clients', [ClientController::class, 'index'])->middleware('permission:clients.view');
    Route::get('clients/{client}', [ClientController::class, 'show'])->middleware('permission:clients.view');
    Route::post('clients', [ClientController::class, 'store'])->middleware('permission:clients.create');
    Route::put('clients/{client}', [ClientController::class, 'update'])->middleware('permission:clients.edit');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->middleware('permission:clients.delete');

    // Factures
    Route::get('factures', [FactureController::class, 'index'])->middleware('permission:factures.view');
    Route::get('factures/{facture}', [FactureController::class, 'show'])->middleware('permission:factures.view');
    Route::post('factures', [FactureController::class, 'store'])->middleware('permission:factures.create');
    Route::put('factures/{facture}', [FactureController::class, 'update'])->middleware('permission:factures.edit');
    Route::delete('factures/{facture}', [FactureController::class, 'destroy'])->middleware('permission:factures.delete');
    Route::post('factures/{id}/annuler', [FactureController::class, 'annuler'])->middleware('permission:factures.edit');
    Route::post('factures/{facture}/valider', [FactureController::class, 'valider'])->middleware('permission:factures.edit');
    Route::post('factures/{facture}/reglement', [FactureController::class, 'reglement'])->middleware('permission:reglements.create');

    // Avoirs Clients
    Route::get('avoirs-clients', [AvoirClientController::class, 'index'])->middleware('permission:avoirs-clients.view');
    Route::get('avoirs-clients/{id}', [AvoirClientController::class, 'show'])->middleware('permission:avoirs-clients.view');
    Route::post('avoirs-clients', [AvoirClientController::class, 'store'])->middleware('permission:avoirs-clients.create');
    Route::post('avoirs-clients/{id}/valider', [AvoirClientController::class, 'valider'])->middleware('permission:avoirs-clients.create');
	
    // Contrats & Abonnements
    Route::get('contrats', [ContratController::class, 'index'])->middleware('permission:contrats.view');
    Route::get('contrats/{contrat}', [ContratController::class, 'show'])->middleware('permission:contrats.view');
    Route::post('contrats', [ContratController::class, 'store'])->middleware('permission:contrats.create');
    Route::put('contrats/{contrat}', [ContratController::class, 'update'])->middleware('permission:contrats.edit');
    Route::delete('contrats/{contrat}', [ContratController::class, 'destroy'])->middleware('permission:contrats.edit');
    
    // Fournisseurs
    Route::get('fournisseurs', [FournisseurController::class, 'index'])->middleware('permission:fournisseurs.view');
    Route::get('fournisseurs/{fournisseur}', [FournisseurController::class, 'show'])->middleware('permission:fournisseurs.view');
    Route::post('fournisseurs', [FournisseurController::class, 'store'])->middleware('permission:fournisseurs.create');
    Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->middleware('permission:fournisseurs.edit');
    Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->middleware('permission:fournisseurs.delete');

    // Produits
    Route::get('produits', [ProduitController::class, 'index'])->middleware('permission:produits.view');
    Route::get('produits/{produit}', [ProduitController::class, 'show'])->middleware('permission:produits.view');
    Route::post('produits', [ProduitController::class, 'store'])->middleware('permission:produits.create');
    Route::put('produits/{produit}', [ProduitController::class, 'update'])->middleware('permission:produits.edit');
    Route::delete('produits/{produit}', [ProduitController::class, 'destroy'])->middleware('permission:produits.delete');
    Route::post('produits/upload-image', [ProduitController::class, 'uploadImage'])->middleware('permission:produits.edit');
    Route::get('produits-next-ref', [ProduitController::class, 'nextReference'])->middleware('permission:produits.view');
    Route::get('produits-next-barcode', [ProduitController::class, 'nextBarcode'])->middleware('permission:produits.view');
    
    Route::get('produits-finis', [ProduitFiniController::class, 'index'])->middleware('permission:produits.view');
    Route::get('produits-finis/{id}', [ProduitFiniController::class, 'show'])->middleware('permission:produits.view');
    Route::post('produits-finis', [ProduitFiniController::class, 'store'])->middleware('permission:produits.create');
    Route::put('produits-finis/{id}', [ProduitFiniController::class, 'update'])->middleware('permission:produits.edit');
    Route::delete('produits-finis/{id}', [ProduitFiniController::class, 'destroy'])->middleware('permission:produits.delete');
    Route::get('produits-finis-next-ref', [ProduitFiniController::class, 'nextReference'])->middleware('permission:produits.view');

    // Devis
    Route::get('devis', [DevisController::class, 'index'])->middleware('permission:devis.view');
    Route::get('devis/{devi}', [DevisController::class, 'show'])->middleware('permission:devis.view');
    Route::post('devis', [DevisController::class, 'store'])->middleware('permission:devis.create');
    Route::put('devis/{devi}', [DevisController::class, 'update'])->middleware('permission:devis.edit');
    Route::delete('devis/{devi}', [DevisController::class, 'destroy'])->middleware('permission:devis.delete');

    // Commandes Fournisseurs
    Route::get('commandes', [CommandeController::class, 'index'])->middleware('permission:commandes.view');
    Route::get('commandes/{commande}', [CommandeController::class, 'show'])->middleware('permission:commandes.view');
    Route::post('commandes', [CommandeController::class, 'store'])->middleware('permission:commandes.create');
    Route::put('commandes/{commande}', [CommandeController::class, 'update'])->middleware('permission:commandes.edit');
    Route::delete('commandes/{commande}', [CommandeController::class, 'destroy'])->middleware('permission:commandes.delete');

    // Stock
    Route::get('stock', [StockController::class, 'index'])->middleware('permission:stock.view');
    Route::get('stock/{id}', [StockController::class, 'show'])->middleware('permission:stock.view');
    Route::post('stock/adjust', [StockController::class, 'adjust'])->middleware('permission:stock.mouvement');
    Route::post('stock/transfer', [StockController::class, 'transfer'])->middleware('permission:stock.mouvement');

    // Projets & Dépenses
    Route::get('projets', [ProjetController::class, 'index'])->middleware('permission:projets.view');
    Route::get('projets/{projet}', [ProjetController::class, 'show'])->middleware('permission:projets.view');
    Route::post('projets', [ProjetController::class, 'store'])->middleware('permission:projets.create');
    Route::put('projets/{projet}', [ProjetController::class, 'update'])->middleware('permission:projets.edit');
    Route::delete('projets/{projet}', [ProjetController::class, 'destroy'])->middleware('permission:projets.edit');

    Route::get('depenses', [DepenseController::class, 'index'])->middleware('permission:depenses.view');
    Route::get('depenses/{depense}', [DepenseController::class, 'show'])->middleware('permission:depenses.view');
    Route::post('depenses', [DepenseController::class, 'store'])->middleware('permission:depenses.create');
    Route::delete('depenses/{depense}', [DepenseController::class, 'destroy'])->middleware('permission:depenses.create');
    
    // Workflow de Transformation
    Route::prefix('workflow')->group(function () {
        Route::post('devis-to-bc/{devis}', [WorkflowVenteController::class, 'devisToBC'])->middleware('permission:devis.transform');
        Route::post('bc-to-bl/{bcc}', [WorkflowVenteController::class, 'bcToBL'])->middleware('permission:bons-livraison.create');
        Route::post('bl-to-facture/{bl}', [WorkflowVenteController::class, 'blToFacture'])->middleware('permission:factures.create');
        Route::post('facture-to-bl/{facture}', [WorkflowVenteController::class, 'factureToBL'])->middleware('permission:bons-livraison.create');
        
        // Achats
        Route::post('commande-fournisseur-to-br/{id}', [WorkflowAchatController::class, 'commandeToBR'])->middleware('permission:bons-reception.create');
        Route::post('br-to-facture-achat/{br}', [WorkflowAchatController::class, 'brToFacture'])->middleware('permission:factures-achats.create');
    });

    // Modules Ventes
    Route::get('bons-commande-client', [BonCommandeClientController::class, 'index'])->middleware('permission:bons-commande-client.view');
    Route::get('bons-commande-client/{bons_commande_client}', [BonCommandeClientController::class, 'show'])->middleware('permission:bons-commande-client.view');
    Route::post('bons-commande-client', [BonCommandeClientController::class, 'store'])->middleware('permission:bons-commande-client.create');
    Route::put('bons-commande-client/{bons_commande_client}', [BonCommandeClientController::class, 'update'])->middleware('permission:bons-commande-client.edit');
    Route::delete('bons-commande-client/{bons_commande_client}', [BonCommandeClientController::class, 'destroy'])->middleware('permission:bons-commande-client.delete');

    Route::get('bons-livraison', [BonLivraisonController::class, 'index'])->middleware('permission:bons-livraison.view');
    Route::get('bons-livraison/{bons_livraison}', [BonLivraisonController::class, 'show'])->middleware('permission:bons-livraison.view');
    Route::post('bons-livraison', [BonLivraisonController::class, 'store'])->middleware('permission:bons-livraison.create');
    Route::put('bons-livraison/{bons_livraison}', [BonLivraisonController::class, 'update'])->middleware('permission:bons-livraison.edit');
    Route::delete('bons-livraison/{bons_livraison}', [BonLivraisonController::class, 'destroy'])->middleware('permission:bons-livraison.delete');
    Route::post('bons-livraison/{id}/annuler', [BonLivraisonController::class, 'annuler'])->middleware('permission:bons-livraison.edit');

    // Modules Achats
    Route::get('bons-reception', [BonReceptionController::class, 'index'])->middleware('permission:bons-reception.view');
    Route::get('bons-reception/{bons_reception}', [BonReceptionController::class, 'show'])->middleware('permission:bons-reception.view');
    Route::post('bons-reception', [BonReceptionController::class, 'store'])->middleware('permission:bons-reception.create');
    Route::put('bons-reception/{bons_reception}', [BonReceptionController::class, 'update'])->middleware('permission:bons-reception.edit');
    Route::delete('bons-reception/{bons_reception}', [BonReceptionController::class, 'destroy'])->middleware('permission:bons-reception.edit');
    Route::post('bons-reception/{id}/annuler', [BonReceptionController::class, 'annuler'])->middleware('permission:bons-reception.edit');

    Route::get('factures-achats', [FactureAchatController::class, 'index'])->middleware('permission:factures-achats.view');
    Route::get('factures-achats/{factures_achat}', [FactureAchatController::class, 'show'])->middleware('permission:factures-achats.view');
    Route::post('factures-achats', [FactureAchatController::class, 'store'])->middleware('permission:factures-achats.create');
    Route::put('factures-achats/{factures_achat}', [FactureAchatController::class, 'update'])->middleware('permission:factures-achats.edit');
    Route::delete('factures-achats/{factures_achat}', [FactureAchatController::class, 'destroy'])->middleware('permission:factures-achats.edit');
    Route::post('factures-achats/{id}/annuler', [FactureAchatController::class, 'annuler'])->middleware('permission:factures-achats.edit');

    Route::get('avoirs-fournisseurs', [AvoirFournisseurController::class, 'index'])->middleware('permission:avoirs-fournisseurs.view');
    Route::get('avoirs-fournisseurs/{id}', [AvoirFournisseurController::class, 'show'])->middleware('permission:avoirs-fournisseurs.view');
    Route::post('avoirs-fournisseurs', [AvoirFournisseurController::class, 'store'])->middleware('permission:avoirs-fournisseurs.create');
    Route::post('avoirs-fournisseurs/{id}/valider', [AvoirFournisseurController::class, 'valider'])->middleware('permission:avoirs-fournisseurs.create');

    Route::get('dettes', [DetteController::class, 'index'])->middleware('permission:dettes.view');
    Route::get('dettes/{id}', [DetteController::class, 'show'])->middleware('permission:dettes.view');
    Route::post('dettes/{id}/reglement', [DetteController::class, 'reglement'])->middleware('permission:reglements.create');
    
    // Paramétrage & Référentiels
	Route::get('parametrage/taux-tva', [\App\Http\Controllers\Api\TvaController::class, 'index']);
    Route::prefix('parametrage')->group(function () {
        Route::get('entreprise', [App\Http\Controllers\Api\EntrepriseController::class, 'show'])->middleware('permission:parametrage.view');
        Route::put('entreprise', [App\Http\Controllers\Api\EntrepriseController::class, 'update'])->middleware('permission:parametrage.edit');
        
        // Sous-comptes & Habilitations (Rôles & Permissions)
        Route::get('users', [\App\Http\Controllers\Api\Parametrage\TenantUserController::class, 'index'])->middleware('permission:users.manage');
        Route::post('users', [\App\Http\Controllers\Api\Parametrage\TenantUserController::class, 'store'])->middleware('permission:users.manage');
        Route::get('users/{id}', [\App\Http\Controllers\Api\Parametrage\TenantUserController::class, 'show'])->middleware('permission:users.manage');
        Route::put('users/{id}', [\App\Http\Controllers\Api\Parametrage\TenantUserController::class, 'update'])->middleware('permission:users.manage');
        Route::put('users/{id}/password', [\App\Http\Controllers\Api\Parametrage\TenantUserController::class, 'updatePassword'])->middleware('permission:users.manage');
        Route::delete('users/{id}', [\App\Http\Controllers\Api\Parametrage\TenantUserController::class, 'destroy'])->middleware('permission:users.manage');

        Route::get('roles', [\App\Http\Controllers\Api\Parametrage\TenantRoleController::class, 'index'])->middleware('permission:roles.manage');
        Route::get('permissions', [\App\Http\Controllers\Api\Parametrage\TenantRoleController::class, 'permissions'])->middleware('permission:roles.manage');
        Route::post('roles', [\App\Http\Controllers\Api\Parametrage\TenantRoleController::class, 'store'])->middleware('permission:roles.manage');
        Route::put('roles/{id}', [\App\Http\Controllers\Api\Parametrage\TenantRoleController::class, 'update'])->middleware('permission:roles.manage');
        Route::delete('roles/{id}', [\App\Http\Controllers\Api\Parametrage\TenantRoleController::class, 'destroy'])->middleware('permission:roles.manage');

        // Dynamic reference CRUD handles: 'taux-tva', 'devises', 'entrepots', 'modes-reglement', 'conditions-reglement', 'familles-produit'
        Route::apiResource('referentiels/{type}', App\Http\Controllers\Api\Parametrage\ReferentielController::class)
             ->parameters(['{type}' => 'id'])
             ->middleware('permission:parametrage.edit');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Reporting
    Route::prefix('reporting')->middleware('permission:reporting.view')->group(function () {
        Route::get('/all', [ReportingController::class, 'all']);
        Route::get('/sales', [ReportingController::class, 'sales']);
        Route::get('/purchases', [ReportingController::class, 'purchases']);
        Route::get('/finance', [ReportingController::class, 'finance']);
        Route::get('/stock', [ReportingController::class, 'stock']);
        Route::get('/payments', [ReportingController::class, 'payments']);
        Route::get('/unpaid', [ReportingController::class, 'unpaid']);
        Route::get('/unpaid-statement', [ReportingController::class, 'unpaidStatement']);
        Route::get('/cash-flow', [ReportingController::class, 'cashFlow']);
        Route::get('/cheques', [ReportingController::class, 'cheques']);
        Route::put('/cheques/{id}/statut', [ReportingController::class, 'updateChequeStatus']);
    });

});

// Debug route – temporary
Route::get('debug/stock', function () {
    return \App\Models\Stock::with(['produit', 'entrepot'])->get();
});
