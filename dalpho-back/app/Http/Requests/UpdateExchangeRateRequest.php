<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pour l’instant on autorise tout utilisateur authentifié
        // si tu n'as pas encore mis Sanctum partout, tu peux laisser true
        return true;
    }

    public function rules(): array
    {
        return [
            // "sometimes" = seulement si le champ est présent dans le JSON
            'from_currency_id' => 'sometimes|exists:currencies,id',
            'to_currency_id'   => 'sometimes|exists:currencies,id',
            'rate'             => 'sometimes|numeric|min:0',
            'effective_date'   => 'sometimes|date',
            'is_current'       => 'sometimes|boolean',
        ];
    }
}
