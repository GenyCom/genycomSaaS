<?php
/**
 * GenyCom Web SaaS — Routes API
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
};
use Illuminate\Support\Facades\Route;

// ─── Public Routes ──────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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
        Route::apiResource('users', SuperAdminUserController::class);
        Route::apiResource('tenants', SuperAdminTenantController::class)->only(['index', 'show', 'store', 'destroy']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/kpis', [DashboardController::class, 'kpis']);
        Route::get('/ca-mensuel', [DashboardController::class, 'caMensuel']);
        Route::get('/top-ventes', [DashboardController::class, 'topVentes']);
        Route::get('/top-clients', [DashboardController::class, 'topClients']);
        Route::get('/echeances', [DashboardController::class, 'echeances']);
        Route::get('/alertes-stock', [DashboardController::class, 'alertesStock']);
        Route::get('/stock-stats', [DashboardController::class, 'stockStats']);
    });

    // Clients
    Route::apiResource('clients', ClientController::class);

    // Factures
    Route::apiResource('factures', FactureController::class);
    Route::apiResource('avoirs-clients', AvoirClientController::class);
    Route::post('avoirs-clients/{id}/valider', [AvoirClientController::class, 'valider']);
    Route::post('factures/{facture}/valider', [FactureController::class, 'valider']);
    Route::post('factures/{facture}/reglement', [FactureController::class, 'reglement']);
	Route::post('factures/{id}/reglement', [FactureController::class, 'reglement']);
	
    // Ventes / Contrats
    Route::apiResource('contrats', ContratController::class);
    
    // --- Autres modules activés ---
    Route::apiResource('fournisseurs', FournisseurController::class);
    Route::apiResource('produits', ProduitController::class);
    Route::get('produits-next-ref', [ProduitController::class, 'nextReference']);
    Route::apiResource('devis', DevisController::class);
    Route::apiResource('commandes', CommandeController::class);
    Route::post('stock/adjust', [StockController::class, 'adjust']);
    Route::post('stock/transfer', [StockController::class, 'transfer']);
    Route::get('stock', [StockController::class, 'index']);
    Route::get('stock/{id}', [StockController::class, 'show']);
    Route::apiResource('projets', ProjetController::class);
    Route::apiResource('depenses', DepenseController::class);
    
    // Workflow de Transformation
    Route::prefix('workflow')->group(function () {
        Route::post('devis-to-bc/{devis}', [WorkflowVenteController::class, 'devisToBC']);
        Route::post('bc-to-bl/{bcc}', [WorkflowVenteController::class, 'bcToBL']);
        Route::post('bl-to-facture/{bl}', [WorkflowVenteController::class, 'blToFacture']);
        Route::post('facture-to-bl/{facture}', [WorkflowVenteController::class, 'factureToBL']);
        
        // Achats
        Route::post('commande-fournisseur-to-br/{id}', [WorkflowAchatController::class, 'commandeToBR']);
        Route::post('br-to-facture-achat/{br}', [WorkflowAchatController::class, 'brToFacture']);
    });

    // Modules Ventes
    Route::apiResource('bons-commande-client', BonCommandeClientController::class);
    Route::apiResource('bons-livraison', BonLivraisonController::class);

    // Modules Achats
    Route::apiResource('bons-reception', BonReceptionController::class);
    Route::apiResource('factures-achats', FactureAchatController::class);
    Route::apiResource('avoirs-fournisseurs', AvoirFournisseurController::class);
    Route::post('avoirs-fournisseurs/{id}/valider', [AvoirFournisseurController::class, 'valider']);
    Route::apiResource('dettes', DetteController::class)->only(['index', 'show']);
    Route::post('dettes/{id}/reglement', [DetteController::class, 'reglement']);
    
    // Paramétrage & Référentiels
	Route::get('parametrage/taux-tva', [\App\Http\Controllers\Api\TvaController::class, 'index']);
    Route::prefix('parametrage')->group(function () {
        Route::get('entreprise', [App\Http\Controllers\Api\EntrepriseController::class, 'show']);
        Route::put('entreprise', [App\Http\Controllers\Api\EntrepriseController::class, 'update']);
        
        // Dynamic reference CRUD handles: 'taux-tva', 'devises', 'entrepots', 'modes-reglement', 'conditions-reglement', 'familles-produit'
        Route::apiResource('referentiels/{type}', App\Http\Controllers\Api\Parametrage\ReferentielController::class)
             ->parameters(['{type}' => 'id']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// Debug route – temporary
Route::get('debug/stock', function () {
    return \App\Models\Stock::with(['produit', 'entrepot'])->get();
});
