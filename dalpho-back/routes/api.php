<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ExchangeRateController;
use App\Http\Controllers\Api\ExchangeRateHistoryController;

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (PAS D'AUTH)
|--------------------------------------------------------------------------
*/

// Login / Register
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Exemples de routes publiques pour ton module de taux
Route::prefix('public')->group(function () {

    // Currencies accessibles sans login
    Route::get('/currencies/active', [CurrencyController::class, 'getActiveCurrencies']);
    
    // Obtenir le taux courant
    Route::get('/exchange-rates/current/{from}/{to}', [ExchangeRateController::class, 'getCurrent']);

    // Convertir un montant
    Route::post('/exchange-rates/convert', [ExchangeRateController::class, 'convert']);

    // Taux de change actuels
    Route::get('/exchange-rates/current', [ExchangeRateController::class, 'getCurrentRates']);
});


/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES AVEC AUTH SANCTUM
|--------------------------------------------------------------------------
*/
// Route::middleware('auth:sanctum')->group(function () {

    // Récupérer l'utilisateur connecté
    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur connecté',
            'data'    => $request->user()
        ]);
    });

    // Déconnexion simple et déconnexion globale
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });

    /*
    |--------------------------------------------------------------------------
    | CURRENCIES (CRUD complet)
    |--------------------------------------------------------------------------
    */
    Route::prefix('currencies')->group(function () {

        Route::get('/', [CurrencyController::class, 'index']);        // Liste
        Route::post('/', [CurrencyController::class, 'store']);       // Créer
        Route::get('/{currency}', [CurrencyController::class, 'show']); // Détails
        Route::put('/{currency}', [CurrencyController::class, 'update']); // Modifier
        Route::delete('/{currency}', [CurrencyController::class, 'destroy']); // Supprimer

        // Spécifiques
        Route::post('/{currency}/toggle-active', [CurrencyController::class, 'toggleActive']);
        Route::get('/base/currency', [CurrencyController::class, 'getBaseCurrency']);
    });

    /*
    |--------------------------------------------------------------------------
    | EXCHANGE RATES
    |--------------------------------------------------------------------------
    */
    Route::prefix('exchange-rates')->group(function () {

        Route::get('/', [ExchangeRateController::class, 'index']);
        Route::post('/', [ExchangeRateController::class, 'store']);
        Route::get('/{exchangeRate}', [ExchangeRateController::class, 'show']);
        Route::put('/{exchangeRate}', [ExchangeRateController::class, 'update']);
        Route::delete('/{exchangeRate}', [ExchangeRateController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | EXCHANGE RATE HISTORY
    |--------------------------------------------------------------------------
    */
    Route::prefix('exchange-rate-history')->group(function () {

        Route::get('/{exchangeRateId}', [ExchangeRateHistoryController::class, 'index']);
        Route::get('/pair/history', [ExchangeRateHistoryController::class, 'getByPair']);
        Route::get('/agent/{agentId}', [ExchangeRateHistoryController::class, 'getByAgent']);
        Route::get('/recent/all', [ExchangeRateHistoryController::class, 'getRecent']);
        Route::get('/stats/all', [ExchangeRateHistoryController::class, 'getStats']);
    });
// });
