<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CurrencyController;

// Nouveaux contrôleurs ExchangeRate
use App\Http\Controllers\Api\ExchangeRate\ExchangeRateIndexController;


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
| DEBUG TOKEN (à désactiver en production)
|--------------------------------------------------------------------------
*/

Route::get('/debug/create-token', function () {
    $user = \App\Models\User::first();
    return ['token' => $user->createToken('postman')->plainTextToken];
});

 
Route::prefix('v1')->group(function () {
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/rate.php';
    require __DIR__.'/api/history.php';
    require __DIR__.'/api/currency.php';
    
});
