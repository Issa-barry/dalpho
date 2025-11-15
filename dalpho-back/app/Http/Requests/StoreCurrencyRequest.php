<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Pour le moment on autorise tous les users authentifiés
        return auth()->check(); // ou simplement: return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'code' => 'sometimes|string|max:5|unique:currencies,code,' . $this->currency,
        'name' => 'sometimes|string|max:100',
        'symbol' => 'sometimes|string|max:10',
        'is_active' => 'sometimes|boolean',
        'is_base_currency' => 'sometimes|boolean'
        ];
    }
}
