<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'agent_id',
        'effective_date',
        'is_current',

        // High / Low du jour
        'day_high',
        'day_low',

        // Variation
        'change_abs',
        'change_pct',
        'direction'
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'day_high' => 'decimal:4',
        'day_low' => 'decimal:4',
        'change_abs' => 'decimal:4',
        'change_pct' => 'decimal:4',
        'is_current' => 'boolean',
        'effective_date' => 'date',
    ];

    /* ===================== RELATIONS ===================== */

    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function history()
    {
        return $this->hasMany(ExchangeRateHistory::class)->orderBy('created_at', 'desc');
    }

    /* ===================== BOOT MODEL ===================== */

    protected static function boot()
    {
        parent::boot();

        /**
         * Lors de la création d’un nouveau taux :
         * - désactive les anciens taux
         * - initialise high/low
         * - calcule la tendance (aucune sur création)
         */
        static::creating(function ($rate) {

            // Désactive l'ancien "taux actuel"
            self::where('from_currency_id', $rate->from_currency_id)
                ->where('to_currency_id', $rate->to_currency_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // high/low initial = valeur courante
            $rate->day_high = $rate->rate;
            $rate->day_low = $rate->rate;

            // aucune variation à la création
            $rate->change_abs = 0;
            $rate->change_pct = 0;
            $rate->direction = "flat";
        });

        /**
         * Lors de la mise à jour :
         * - calcule la variation
         * - met à jour high/low si nécessaire
         * - enregistre l'historique
         */
        static::updating(function ($rate) {

            // Ancienne valeur
            $old = $rate->getOriginal('rate');

            if ($rate->isDirty('rate')) {

                // Variation absolue
                $rate->change_abs = $rate->rate - $old;

                // Variation en %
                $rate->change_pct = $old > 0
                    ? (($rate->rate - $old) / $old) * 100
                    : 0;

                // Direction
                $rate->direction =
                    ($rate->rate > $old) ? 'up' :
                    (($rate->rate < $old) ? 'down' : 'flat');

                // High / Low du jour
                $rate->day_high = max($rate->day_high, $rate->rate);
                $rate->day_low  = min($rate->day_low, $rate->rate);

                // Historique
                ExchangeRateHistory::create([
                    'exchange_rate_id'   => $rate->id,
                    'from_currency_id'   => $rate->from_currency_id,
                    'to_currency_id'     => $rate->to_currency_id,
                    'old_rate'           => $old,
                    'new_rate'           => $rate->rate,
                    'changed_by'         => auth()->id() ?? $rate->agent_id,
                    'change_reason'      => 'Mise à jour du taux de change',
                ]);
            }
        });

        /**
         * Après création — on logue l’historique
         */
        static::created(function ($rate) {
            ExchangeRateHistory::create([
                'exchange_rate_id' => $rate->id,
                'from_currency_id' => $rate->from_currency_id,
                'to_currency_id'   => $rate->to_currency_id,
                'old_rate'         => null,
                'new_rate'         => $rate->rate,
                'changed_by'       => $rate->agent_id,
                'change_reason'    => 'Création initiale du taux de change'
            ]);
        });
    }

    /* ===================== SCOPES ===================== */

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)
                     ->where('effective_date', '<=', now());
    }

    public function scopeForPair($query, $fromId, $toId)
    {
        return $query->where('from_currency_id', $fromId)
                     ->where('to_currency_id', $toId);
    }

    public function scopeEffectiveAt($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
                     ->orderBy('effective_date', 'desc');
    }

    /* ===================== HELPERS ===================== */

    public function convert($amount)
    {
        return $amount * $this->rate;
    }

    public function getInverseRate()
    {
        return $this->rate > 0 ? 1 / $this->rate : 0;
    }

    public function getFormattedRateAttribute()
    {
        return number_format($this->rate, 4, '.', ' ');
    }

    public function getDescriptionAttribute()
    {
        return "1 {$this->fromCurrency->code} = {$this->formatted_rate} {$this->toCurrency->code}";
    }
}
