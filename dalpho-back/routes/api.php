<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientRegisterController;
use App\Http\Controllers\Api\CurrencyController;

// Nouveaux contrôleurs ExchangeRate
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateIndexController;
 use App\Http\Controllers\Api\ExchangeRate\ExchangeRateShowController;
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateStoreController;
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateUpdateDestroyController;

use App\Http\Controllers\Api\ExchangeRateHistoryController;
use App\Http\Controllers\Api\StaffRegisterController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Users\UsersIndexController;
use App\Http\Controllers\Users\UsersShowController;

// EXCHANGE RATE CONTROLLEURS SÉPARÉS
 
 
/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES (PAS D'AUTH)
|--------------------------------------------------------------------------
*/


Route::post('/login', [AuthController::class, 'login']);

 
// Routes publiques (sans authentification)
 
    Route::get('/users', [UsersIndexController::class, 'index']);
 

Route::middleware(['auth:sanctum'])->group(function () {
    // Clients
    Route::post('/clients', [ClientRegisterController::class, 'store']);

    // Staff : agent, manager, admin
    Route::post('/staff', [StaffRegisterController::class, 'store']); 

    Route::get('/me', [ProfileController::class, 'me']);

//    Route::get('/users', [UsersIndexController::class, 'index']);          // OK
   Route::get('/users/{id}', [UsersShowController::class, 'show']); // OK
});


Route::prefix('public')->group(function () {

    // Devise
    Route::get('/currencies/active', [CurrencyController::class, 'getActiveCurrencies']);

    // Taux actuels
    Route::get('/exchange-rates/current', [ExchangeRateIndexController::class, 'getCurrentRates']);

    // Taux pour une paire EUR/GNF
    Route::get('/exchange-rates/current/{from}/{to}', [ExchangeRateIndexController::class, 'getCurrent']);
 
    // Conversion
    Route::post('/exchange-rates/convert', [ExchangeRateIndexController::class, 'convert']);
});


/*
|--------------------------------------------------------------------------
| ROUTES AUTHENTIFIÉES (SANCTUM)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | UTILISATEUR CONNECTÉ
    |--------------------------------------------------------------------------
    */
    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur connecté',
            'data' => $request->user(),
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });


    /*
    |--------------------------------------------------------------------------
    | CURRENCIES CRUD
    |--------------------------------------------------------------------------
    */
    Route::prefix('currencies')->group(function () {

        Route::get('/', [CurrencyController::class, 'index']);
        Route::post('/', [CurrencyController::class, 'store']);
        Route::get('/{currency}', [CurrencyController::class, 'show']);
        Route::put('/{currency}', [CurrencyController::class, 'update']);
        Route::delete('/{currency}', [CurrencyController::class, 'destroy']);

        Route::post('/{currency}/toggle-active', [CurrencyController::class, 'toggleActive']);
        Route::get('/base/currency', [CurrencyController::class, 'getBaseCurrency']);
    });


    /*
    |--------------------------------------------------------------------------
    | EXCHANGE RATES (CRUD avec contrôleurs séparés)
    |--------------------------------------------------------------------------
    */
    Route::prefix('exchange-rates')->group(function () {

        // LISTE + FILTRES
        Route::get('/', [ExchangeRateIndexController::class, 'index']);

        // CRÉER
        Route::post('/store', [ExchangeRateStoreController::class, 'store']);

        // AFFICHER
        Route::get('/{exchangeRate}', [ExchangeRateShowController::class, 'show']);

        // MODIFIER
        Route::put('/{exchangeRate}', [ExchangeRateUpdateDestroyController::class, 'update']);
        Route::patch('/{exchangeRate}', [ExchangeRateUpdateDestroyController::class, 'update']);

        // SUPPRIMER
        Route::delete('/{exchangeRate}', [ExchangeRateUpdateDestroyController::class, 'destroy']);
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

});


/*
|--------------------------------------------------------------------------
| DEBUG TOKEN (à désactiver en production)
|--------------------------------------------------------------------------
*/

Route::get('/debug/create-token', function () {
    $user = \App\Models\User::first();
    return ['token' => $user->createToken('postman')->plainTextToken];
});
