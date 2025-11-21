<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pour le moment on autorise tous les users authentifiés
        return auth()->check(); // ou simplement: return true;
    }

    public function rules(): array
    {
        return [
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id'   => 'required|exists:currencies,id',
            'rate'             => 'required|integer|min:0',
            'buy_rate'         => 'nullable|integer|min:0',
            'effective_date'   => 'nullable|date',
            'is_current'       => 'nullable|boolean',
        ];
    }
}
