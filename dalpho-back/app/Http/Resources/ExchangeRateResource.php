<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\UserResource;

class ExchangeRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'from_currency_id'=> $this->from_currency_id,
            'to_currency_id'  => $this->to_currency_id,
            'rate'            => $this->rate,
            'buy_rate'       => $this->buy_rate,
            'agent_id'        => $this->agent_id,
            'effective_date'  => $this->effective_date?->toDateString(),
            'is_current'      => (bool) $this->is_current,

            // High / Low du jour (avec fallback sur le rate si null)
            'day_high'        => $this->day_high ?? $this->rate,
            'day_low'         => $this->day_low  ?? $this->rate,

            // Variation & direction (calculées dans le modèle)
            'change_abs'      => $this->change_abs,
            'change_pct'      => $this->change_pct,
            'direction'       => $this->direction,

            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),

            // Relations
            'from_currency'   => new CurrencyResource($this->whenLoaded('fromCurrency')),
            'to_currency'     => new CurrencyResource($this->whenLoaded('toCurrency')),
            'agent'           => new UserResource($this->whenLoaded('agent')),
        ];
    }
}
