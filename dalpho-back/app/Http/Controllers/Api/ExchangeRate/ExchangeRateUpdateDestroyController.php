<?php

namespace App\Http\Controllers\Api\ExchangeRate;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Throwable;

class ExchangeRateUpdateDestroyController extends Controller
{
    use JsonResponseTrait;

    /**
     * Mise à jour d'un taux de change.
     */
    public function update(UpdateExchangeRateRequest $request, ExchangeRate $exchangeRate): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Update partiel
            $exchangeRate->fill($validated);
            $exchangeRate->save();

            $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent']);

            return $this->successResponse(
                'Taux de change mis à jour avec succès',
                new ExchangeRateResource($exchangeRate)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la mise à jour du taux de change');
        }
    }

    /**
     * Suppression d'un taux de change.
     */
    public function destroy(ExchangeRate $exchangeRate): JsonResponse
    {
        try {
            $exchangeRate->delete();

            return $this->successResponse('Taux de change supprimé avec succès');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la suppression du taux de change');
        }
    }
}
