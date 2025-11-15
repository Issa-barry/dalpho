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
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = ExchangeRate::with(['fromCurrency', 'toCurrency', 'agent']);

        // Filtrer par devise source
        if ($request->has('from_currency_id')) {
            $query->where('from_currency_id', $request->from_currency_id);
        }

        // Filtrer par devise cible
        if ($request->has('to_currency_id')) {
            $query->where('to_currency_id', $request->to_currency_id);
        }

        // Filtrer par taux actuel uniquement
        if ($request->has('current') && $request->current) {
            $query->current();
        }

        // Filtrer par agent
        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $exchangeRates = $query->orderBy('effective_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ExchangeRateResource::collection($exchangeRates);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreExchangeRateRequest $request
     * @return JsonResponse
     */
    public function store(StoreExchangeRateRequest $request)
    {
        $exchangeRate = ExchangeRate::create([
            'from_currency_id' => $request->from_currency_id,
            'to_currency_id' => $request->to_currency_id,
            'rate' => $request->rate,
            'agent_id' => auth()->id(),
            'effective_date' => $request->effective_date ?? now(),
            'is_current' => true
        ]);

        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent']);

        return response()->json([
            'message' => 'Taux de change créé avec succès',
            'data' => new ExchangeRateResource($exchangeRate)
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @param ExchangeRate $exchangeRate
     * @return ExchangeRateResource
     */
    public function show(ExchangeRate $exchangeRate)
    {
        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent', 'history.changedBy']);
        
        return new ExchangeRateResource($exchangeRate);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param UpdateExchangeRateRequest $request
     * @param ExchangeRate $exchangeRate
     * @return JsonResponse
     */
    public function update(UpdateExchangeRateRequest $request, ExchangeRate $exchangeRate)
    {
        $exchangeRate->update($request->validated());

        $exchangeRate->load(['fromCurrency', 'toCurrency', 'agent']);

        return response()->json([
            'message' => 'Taux de change mis à jour avec succès',
            'data' => new ExchangeRateResource($exchangeRate)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param ExchangeRate $exchangeRate
     * @return JsonResponse
     */
    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();

        return response()->json([
            'message' => 'Taux de change supprimé avec succès'
        ], 200);
    }

    /**
     * Obtenir le taux de change actuel entre deux devises
     * 
     * @param string $fromCode Code de la devise source (ex: EUR)
     * @param string $toCode Code de la devise cible (ex: GNF)
     * @return JsonResponse
     */
    public function getCurrent(string $fromCode, string $toCode)
    {
        $fromCurrency = Currency::where('code', $fromCode)->first();
        $toCurrency = Currency::where('code', $toCode)->first();

        if (!$fromCurrency || !$toCurrency) {
            return response()->json([
                'message' => 'Devise non trouvée'
            ], 404);
        }

        $exchangeRate = ExchangeRate::current()
            ->forPair($fromCurrency->id, $toCurrency->id)
            ->with(['fromCurrency', 'toCurrency'])
            ->first();

        if (!$exchangeRate) {
            return response()->json([
                'message' => 'Aucun taux de change trouvé pour cette paire de devises'
            ], 404);
        }

        return response()->json([
            'data' => new ExchangeRateResource($exchangeRate)
        ]);
    }

    /**
     * Convertir un montant entre deux devises
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency_code' => 'required|string|exists:currencies,code',
            'to_currency_code' => 'required|string|exists:currencies,code'
        ]);

        $fromCurrency = Currency::where('code', $request->from_currency_code)->first();
        $toCurrency = Currency::where('code', $request->to_currency_code)->first();

        $exchangeRate = ExchangeRate::current()
            ->forPair($fromCurrency->id, $toCurrency->id)
            ->first();

        if (!$exchangeRate) {
            return response()->json([
                'message' => 'Aucun taux de change trouvé pour cette paire de devises'
            ], 404);
        }

        $convertedAmount = $exchangeRate->convert($request->amount);

        return response()->json([
            'data' => [
                'amount' => $request->amount,
                'from_currency' => $fromCurrency->code,
                'to_currency' => $toCurrency->code,
                'rate' => $exchangeRate->rate,
                'converted_amount' => round($convertedAmount, 2),
                'formatted' => number_format($convertedAmount, 2, '.', ' ') . ' ' . $toCurrency->code
            ]
        ]);
    }

    /**
     * Obtenir tous les taux actuels
     * 
     * @return AnonymousResourceCollection
     */
    public function getCurrentRates()
    {
        $exchangeRates = ExchangeRate::current()
            ->with(['fromCurrency', 'toCurrency'])
            ->get()
            ->groupBy('from_currency_id');

        return response()->json([
            'data' => $exchangeRates
        ]);
    }
}