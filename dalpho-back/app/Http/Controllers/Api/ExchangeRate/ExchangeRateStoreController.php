<?php

namespace App\Http\Controllers\Api\ExchangeRate;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Throwable;

class ExchangeRateStoreController extends Controller
{
    use JsonResponseTrait;

    /**
     * Création d'un nouveau taux de change.
     */
    public function store(StoreExchangeRateRequest $request): JsonResponse
    {
        try {
            // Données validées
            $validated = $request->validated();

            $exchangeRate = ExchangeRate::create([
                'from_currency_id' => $validated['from_currency_id'],
                'to_currency_id'   => $validated['to_currency_id'],
                'rate'             => $validated['rate'],
                'buy_rate'       => $validated['buy_rate'] ?? null,  // 👈 ajouté
                'agent_id'         => auth()->id(),
                'effective_date'   => $validated['effective_date'] ?? now(),
                'is_current'       => true,
            ]);

            $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent']);

            return $this->createdResponse(
                'Taux de change créé avec succès',
                new ExchangeRateResource($exchangeRate)
            );

        } catch (Throwable $e) {
            return $this->handleException(
                $e,
                'Erreur lors de la création du taux de change'
            );
        }
    }
}
