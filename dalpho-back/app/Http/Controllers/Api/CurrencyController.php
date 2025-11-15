<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class CurrencyController extends Controller
{
    use JsonResponseTrait;

    /**
     * Display a listing of the resource.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $currencies = Currency::with(['exchangeRatesFrom', 'exchangeRatesTo'])
                ->orderBy('is_base_currency', 'desc')
                ->orderBy('name')
                ->get();

            return $this->successResponse(
                'Liste des devises récupérée avec succès',
                CurrencyResource::collection($currencies)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la récupération des devises');
        }
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param StoreCurrencyRequest $request
     * @return JsonResponse
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        try {
            // Si la devise est marquée comme base, désactiver les autres devises de base
            if ($request->is_base_currency) {
                Currency::where('is_base_currency', true)->update(['is_base_currency' => false]);
            }

            $currency = Currency::create($request->validated());

            return $this->createdResponse(
                'Devise créée avec succès',
                new CurrencyResource($currency)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la création de la devise');
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param Currency $currency
     * @return JsonResponse
     */
    public function show(Currency $currency): JsonResponse
    {
        try {
            $currency->load(['exchangeRatesFrom.toCurrency', 'exchangeRatesTo.fromCurrency']);
            
            return $this->successResponse(
                'Détails de la devise récupérés avec succès',
                new CurrencyResource($currency)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la récupération de la devise');
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param UpdateCurrencyRequest $request
     * @param Currency $currency
     * @return JsonResponse
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
    {
        try {
            // Si la devise est marquée comme base, désactiver les autres devises de base
            if ($request->has('is_base_currency') && $request->is_base_currency) {
                Currency::where('id', '!=', $currency->id)
                    ->where('is_base_currency', true)
                    ->update(['is_base_currency' => false]);
            }

            $currency->update($request->validated());

            return $this->successResponse(
                'Devise mise à jour avec succès',
                new CurrencyResource($currency)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la mise à jour de la devise');
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param Currency $currency
     * @return JsonResponse
     */
    public function destroy(Currency $currency): JsonResponse
    {
        try {
            // Vérifier si la devise est utilisée dans des taux de change
            if ($currency->exchangeRatesFrom()->exists() || $currency->exchangeRatesTo()->exists()) {
                return $this->forbiddenResponse(
                    'Impossible de supprimer cette devise car elle est utilisée dans des taux de change'
                );
            }

            // Empêcher la suppression de la devise de base
            if ($currency->is_base_currency) {
                return $this->forbiddenResponse(
                    'Impossible de supprimer la devise de base'
                );
            }

            $currency->delete();

            return $this->successResponse('Devise supprimée avec succès');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la suppression de la devise');
        }
    }

    /**
     * Obtenir la devise de base (GNF)
     * 
     * @return JsonResponse
     */
    public function getBaseCurrency(): JsonResponse
    {
        try {
            $baseCurrency = Currency::base()->first();

            if (!$baseCurrency) {
                return $this->notFoundResponse('Aucune devise de base trouvée');
            }

            return $this->successResponse(
                'Devise de base récupérée avec succès',
                new CurrencyResource($baseCurrency)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la récupération de la devise de base');
        }
    }

    /**
     * Obtenir uniquement les devises actives
     * 
     * @return JsonResponse
     */
    public function getActiveCurrencies(): JsonResponse
    {
        try {
            $currencies = Currency::active()
                ->orderBy('is_base_currency', 'desc')
                ->orderBy('name')
                ->get();

            return $this->successResponse(
                'Devises actives récupérées avec succès',
                CurrencyResource::collection($currencies)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la récupération des devises actives');
        }
    }

    /**
     * Activer/Désactiver une devise
     * 
     * @param Currency $currency
     * @return JsonResponse
     */
    public function toggleActive(Currency $currency): JsonResponse
    {
        try {
            // Empêcher la désactivation de la devise de base
            if ($currency->is_base_currency && $currency->is_active) {
                return $this->forbiddenResponse(
                    'Impossible de désactiver la devise de base'
                );
            }

            $currency->update(['is_active' => !$currency->is_active]);

            $message = $currency->is_active 
                ? 'Devise activée avec succès' 
                : 'Devise désactivée avec succès';

            return $this->successResponse(
                $message,
                new CurrencyResource($currency)
            );
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors du changement de statut de la devise');
        }
    }
}