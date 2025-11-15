<?php

namespace App\Http\Controllers\Api\ExchangeRate;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExchangeRateIndexController extends Controller
{
    /**
     * Liste paginée des taux de change.
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
     * Obtenir le taux de change actuel entre deux devises (par code).
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
