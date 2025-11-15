<?php

namespace App\Http\Controllers\Api\ExchangeRate;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;

class ExchangeRateStoreController extends Controller
{
    /**
     * Création d'un nouveau taux de change.
     */
    public function store(StoreExchangeRateRequest $request): JsonResponse
    {
        $exchangeRate = ExchangeRate::create([
            'from_currency_id' => $request->from_currency_id,
            'to_currency_id'   => $request->to_currency_id,
            'rate'             => $request->rate,
            'agent_id'         => auth()->id(),
            'effective_date'   => $request->effective_date ?? now(),
            'is_current'       => true,
        ]);

        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent']);

        return response()->json([
            'message' => 'Taux de change créé avec succès',
            'data'    => new ExchangeRateResource($exchangeRate),
        ], 201);
    }
}
