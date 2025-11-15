<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateHistoryResource;
use App\Models\ExchangeRate;
use App\Models\ExchangeRateHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExchangeRateHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * Obtenir l'historique d'un taux de change spécifique
     * 
     * @param int $exchangeRateId
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, int $exchangeRateId)
    {
        $exchangeRate = ExchangeRate::findOrFail($exchangeRateId);

        $history = ExchangeRateHistory::where('exchange_rate_id', $exchangeRateId)
            ->with(['changedBy', 'fromCurrency', 'toCurrency'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ExchangeRateHistoryResource::collection($history);
    }

    /**
     * Obtenir l'historique pour une paire de devises
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getByPair(Request $request)
    {
        $request->validate([
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id',
            'days' => 'nullable|integer|min:1|max:365'
        ]);

        $query = ExchangeRateHistory::forPair(
            $request->from_currency_id,
            $request->to_currency_id
        )->with(['changedBy', 'fromCurrency', 'toCurrency']);

        // Filtrer par période si spécifié
        if ($request->has('days')) {
            $query->recent($request->days);
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ExchangeRateHistoryResource::collection($history);
    }

    /**
     * Obtenir l'historique d'un agent
     * 
     * @param Request $request
     * @param int $agentId
     * @return AnonymousResourceCollection
     */
    public function getByAgent(Request $request, int $agentId)
    {
        $history = ExchangeRateHistory::byAgent($agentId)
            ->with(['changedBy', 'fromCurrency', 'toCurrency', 'exchangeRate'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ExchangeRateHistoryResource::collection($history);
    }

    /**
     * Obtenir les statistiques de l'historique
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $request->validate([
            'from_currency_id' => 'nullable|exists:currencies,id',
            'to_currency_id' => 'nullable|exists:currencies,id',
            'days' => 'nullable|integer|min:1|max:365'
        ]);

        $query = ExchangeRateHistory::query();

        if ($request->has('from_currency_id') && $request->has('to_currency_id')) {
            $query->forPair($request->from_currency_id, $request->to_currency_id);
        }

        if ($request->has('days')) {
            $query->recent($request->days);
        }

        $history = $query->get();

        // Calculer les statistiques
        $stats = [
            'total_changes' => $history->count(),
            'average_rate' => $history->avg('new_rate'),
            'min_rate' => $history->min('new_rate'),
            'max_rate' => $history->max('new_rate'),
            'latest_rate' => $history->first()?->new_rate,
            'first_rate' => $history->last()?->new_rate,
        ];

        // Calculer la variation totale
        if ($stats['first_rate'] && $stats['latest_rate']) {
            $stats['total_variation'] = $stats['latest_rate'] - $stats['first_rate'];
            $stats['total_variation_percentage'] = (($stats['latest_rate'] - $stats['first_rate']) / $stats['first_rate']) * 100;
        }

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Obtenir l'historique récent (30 derniers jours par défaut)
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getRecent(Request $request)
    {
        $days = $request->input('days', 30);

        $history = ExchangeRateHistory::recent($days)
            ->with(['changedBy', 'fromCurrency', 'toCurrency', 'exchangeRate'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ExchangeRateHistoryResource::collection($history);
    }
}