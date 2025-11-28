<?php

use Illuminate\Support\Facades\Route;
 
use App\Http\Controllers\Api\CurrencyController;
 
 
Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('currencies')->group(function () {
            Route::get('/', [CurrencyController::class, 'index']);
            Route::post('/', [CurrencyController::class, 'store']);
            Route::get('/{currency}', [CurrencyController::class, 'show']);
            Route::put('/{currency}', [CurrencyController::class, 'update']);
            Route::delete('/{currency}', [CurrencyController::class, 'destroy']);
            Route::post('/{currency}/toggle-active', [CurrencyController::class, 'toggleActive']);
            Route::get('/base/currency', [CurrencyController::class, 'getBaseCurrency']);
        });
});

 
 

 
  