<?php
/**
 * GenyCom Web SaaS — Routes API
 */

use App\Http\Controllers\Api\{
    AuthController,
    DashboardController,
    ClientController,
    FactureController,
    FournisseurController,
    ProduitController,
    DevisController,
    CommandeController,
    StockController,
    ProjetController,
    DepenseController,
};
use Illuminate\Support\Facades\Route;

// ─── Public Routes ──────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ─── Protected Routes ───────────────────────────────────────
use App\Http\Controllers\Api\SuperAdmin\SuperAdminUserController;

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // SuperAdmin Routes
    Route::prefix('superadmin')->group(function() {
        Route::apiResource('users', SuperAdminUserController::class);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/kpis', [DashboardController::class, 'kpis']);
        Route::get('/ca-mensuel', [DashboardController::class, 'caMensuel']);
        Route::get('/top-ventes', [DashboardController::class, 'topVentes']);
        Route::get('/top-clients', [DashboardController::class, 'topClients']);
        Route::get('/echeances', [DashboardController::class, 'echeances']);
        Route::get('/alertes-stock', [DashboardController::class, 'alertesStock']);
    });

    // Clients
    Route::apiResource('clients', ClientController::class);

    // Factures
    Route::apiResource('factures', FactureController::class);
    Route::post('factures/{facture}/valider', [FactureController::class, 'valider']);
    Route::post('factures/{facture}/reglement', [FactureController::class, 'reglement']);

    // --- Autres modules activés ---
    Route::apiResource('fournisseurs', FournisseurController::class);
    Route::apiResource('produits', ProduitController::class);
    Route::apiResource('devis', DevisController::class);
    Route::apiResource('commandes', CommandeController::class);
    Route::apiResource('stock', StockController::class)->only(['index','show']);
    Route::apiResource('projets', ProjetController::class);
    Route::apiResource('depenses', DepenseController::class);
    
    // Modules restants (à venir)
    // Route::apiResource('bons-livraison', BonLivraisonController::class);
    // Route::apiResource('bons-reception', BonReceptionController::class);
    // Route::apiResource('avoirs-client', AvoirClientController::class);
    // Route::apiResource('avoirs-fournisseur', AvoirFournisseurController::class);
    // Route::apiResource('inventaires', InventaireController::class);
    // Route::apiResource('dettes', DetteController::class);
    // Route::apiResource('reglements', ReglementController::class);
    
    // Paramétrage
    // Route::prefix('parametrage')->group(function () { ... });
});
