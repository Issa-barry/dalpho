<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ExchangeRateHistoryController;

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('exchange-rate-history')->group(function () {
        Route::get('/{exchangeRateId}', [ExchangeRateHistoryController::class, 'index']);
        Route::get('/pair/history', [ExchangeRateHistoryController::class, 'getByPair']);
        Route::get('/agent/{agentId}', [ExchangeRateHistoryController::class, 'getByAgent']);
        Route::get('/recent/all', [ExchangeRateHistoryController::class, 'getRecent']);
        Route::get('/stats/all', [ExchangeRateHistoryController::class, 'getStats']);
    });

});

 
  