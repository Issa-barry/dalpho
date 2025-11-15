<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExchangeRateRequest;
use App\Http\Requests\UpdateExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExchangeRateController extends Controller
{
    /**
     * Liste paginée des taux de change.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ExchangeRate::with(['fromCurrency', 'toCurrency', 'agent']);

        // Filtrer par devise source
        if ($request->filled('from_currency_id')) {
            $query->where('from_currency_id', $request->from_currency_id);
        }

        // Filtrer par devise cible
        if ($request->filled('to_currency_id')) {
            $query->where('to_currency_id', $request->to_currency_id);
        }

        // Filtrer sur les taux actuels uniquement
        if ($request->boolean('current')) {
            $query->current();
        }

        // Filtrer par agent
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $exchangeRates = $query
            ->orderBy('effective_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return ExchangeRateResource::collection($exchangeRates);
    }

    /**
     * Création d'un nouveau taux de change.
     *
     * @param  \App\Http\Requests\StoreExchangeRateRequest  $request
     * @return \Illuminate\Http\JsonResponse
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

    /**
     * Détail d'un taux de change.
     *
     * @param  \App\Models\ExchangeRate  $exchangeRate
     * @return \App\Http\Resources\ExchangeRateResource
     */
    public function show(ExchangeRate $exchangeRate): ExchangeRateResource
    {
        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent', 'history.changedBy']);

        return new ExchangeRateResource($exchangeRate);
    }

    /**
     * Mise à jour d'un taux de change.
     *
     * @param  \App\Http\Requests\UpdateExchangeRateRequest  $request
     * @param  \App\Models\ExchangeRate  $exchangeRate
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateExchangeRateRequest $request, ExchangeRate $exchangeRate): JsonResponse
    {
        $exchangeRate->update($request->validated());

        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent']);

        return response()->json([
            'message' => 'Taux de change mis à jour avec succès',
            'data'    => new ExchangeRateResource($exchangeRate),
        ]);
    }

    /**
     * Suppression d'un taux de change.
     *
     * @param  \App\Models\ExchangeRate  $exchangeRate
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ExchangeRate $exchangeRate): JsonResponse
    {
        $exchangeRate->delete();

        return response()->json([
            'message' => 'Taux de change supprimé avec succès',
        ], 200);
    }

    /**
     * Obtenir le taux de change actuel entre deux devises (par code).
     *
     * @param  string  $fromCode  Code devise source (ex: EUR)
     * @param  string  $toCode    Code devise cible (ex: GNF)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrent(string $fromCode, string $toCode): JsonResponse
    {
        $fromCurrency = Currency::where('code', $fromCode)->first();
        $toCurrency   = Currency::where('code', $toCode)->first();

        if (! $fromCurrency || ! $toCurrency) {
            return response()->json([
                'message' => 'Devise non trouvée',
            ], 404);
        }

        $exchangeRate = ExchangeRate::current()
            ->forPair($fromCurrency->id, $toCurrency->id)
            ->with(['fromCurrency', 'toCurrency', 'agent'])
            ->first();

        if (! $exchangeRate) {
            return response()->json([
                'message' => 'Aucun taux de change trouvé pour cette paire de devises',
            ], 404);
        }

        return response()->json([
            'data' => new ExchangeRateResource($exchangeRate),
        ]);
    }

    /**
     * Convertir un montant entre deux devises.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'amount'             => 'required|numeric|min:0',
            'from_currency_code' => 'required|string|exists:currencies,code',
            'to_currency_code'   => 'required|string|exists:currencies,code',
        ]);

        $fromCurrency = Currency::where('code', $request->from_currency_code)->first();
        $toCurrency   = Currency::where('code', $request->to_currency_code)->first();

        $exchangeRate = ExchangeRate::current()
            ->forPair($fromCurrency->id, $toCurrency->id)
            ->first();

        if (! $exchangeRate) {
            return response()->json([
                'message' => 'Aucun taux de change trouvé pour cette paire de devises',
            ], 404);
        }

        $convertedAmount = $exchangeRate->convert($request->amount);

        return response()->json([
            'data' => [
                'amount'           => $request->amount,
                'from_currency'    => $fromCurrency->code,
                'to_currency'      => $toCurrency->code,
                'rate'             => $exchangeRate->rate,
                'converted_amount' => round($convertedAmount, 2),
                'formatted'        => number_format($convertedAmount, 2, '.', ' ') . ' ' . $toCurrency->code,
            ],
        ]);
    }

    /**
     * Obtenir tous les taux actuels (non paginés).
     * Les champs high/low/day_high/day_low sont déjà exposés
     * via les accessors du modèle et la resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentRates(): JsonResponse
    {
        $exchangeRates = ExchangeRate::current()
            ->with(['fromCurrency', 'toCurrency', 'agent'])
            ->get();

        return response()->json([
            'data' => ExchangeRateResource::collection($exchangeRates),
        ]);
    }
}
