<?php

 use Illuminate\Support\Facades\Route;

// Nouveaux contrôleurs ExchangeRate
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateIndexController;
 use App\Http\Controllers\Api\ExchangeRate\ExchangeRateShowController;
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateStoreController;
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateUpdateDestroyController;


/*
|--------------------------------------------------------------------------
| ROUTES AUTHENTIFIÉES (SANCTUM)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
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


});


 