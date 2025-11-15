<?php

namespace App\Http\Controllers\Api\ExchangeRate;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;

class ExchangeRateShowController extends Controller
{
    /**
     * Détail d'un taux de change.
     */
    public function show(ExchangeRate $exchangeRate): ExchangeRateResource
    {
        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent', 'history.changedBy']);

        return new ExchangeRateResource($exchangeRate);
    }
}
