<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'agent_id',
        'effective_date',
        'is_current'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:4',
        'is_current' => 'boolean',
        'effective_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation : Devise source
     */
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * Relation : Devise cible
     */
    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * Relation : Agent qui a créé/modifié le taux
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Relation : Historique des modifications
     */
    public function history()
    {
        return $this->hasMany(ExchangeRateHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Boot method pour gérer les événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant de créer un nouveau taux
        static::creating(function ($exchangeRate) {
            // Désactiver les anciens taux pour la même paire de devises
            self::where('from_currency_id', $exchangeRate->from_currency_id)
                ->where('to_currency_id', $exchangeRate->to_currency_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        });

        // Après création, enregistrer dans l'historique
        static::created(function ($exchangeRate) {
            ExchangeRateHistory::create([
                'exchange_rate_id' => $exchangeRate->id,
                'from_currency_id' => $exchangeRate->from_currency_id,
                'to_currency_id' => $exchangeRate->to_currency_id,
                'old_rate' => null,
                'new_rate' => $exchangeRate->rate,
                'changed_by' => $exchangeRate->agent_id,
                'change_reason' => 'Création initiale du taux de change'
            ]);
        });

        // Avant de mettre à jour un taux
        static::updating(function ($exchangeRate) {
            // Si le taux change, créer une entrée dans l'historique
            if ($exchangeRate->isDirty('rate')) {
                $oldRate = $exchangeRate->getOriginal('rate');
                
                ExchangeRateHistory::create([
                    'exchange_rate_id' => $exchangeRate->id,
                    'from_currency_id' => $exchangeRate->from_currency_id,
                    'to_currency_id' => $exchangeRate->to_currency_id,
                    'old_rate' => $oldRate,
                    'new_rate' => $exchangeRate->rate,
                    'changed_by' => auth()->id() ?? $exchangeRate->agent_id,
                    'change_reason' => 'Mise à jour du taux de change'
                ]);
            }
        });
    }

    /**
     * Scope : Taux actuels uniquement
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)
                     ->where('effective_date', '<=', now());
    }

    /**
     * Scope : Taux pour une paire de devises spécifique
     */
    public function scopeForPair($query, $fromCurrencyId, $toCurrencyId)
    {
        return $query->where('from_currency_id', $fromCurrencyId)
                     ->where('to_currency_id', $toCurrencyId);
    }

    /**
     * Scope : Taux effectifs à une date donnée
     */
    public function scopeEffectiveAt($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
                     ->orderBy('effective_date', 'desc');
    }

    /**
     * Convertir un montant avec ce taux
     *
     * @param float $amount
     * @return float
     */
    public function convert($amount)
    {
        return $amount * $this->rate;
    }

    /**
     * Obtenir le taux inverse
     *
     * @return float
     */
    public function getInverseRate()
    {
        return 1 / $this->rate;
    }

    /**
     * Formater le taux pour l'affichage
     *
     * @return string
     */
    public function getFormattedRateAttribute()
    {
        return number_format($this->rate, 4, '.', ' ');
    }

    /**
     * Obtenir la description du taux
     *
     * @return string
     */
    public function getDescriptionAttribute()
    {
        return "1 {$this->fromCurrency->code} = {$this->formatted_rate} {$this->toCurrency->code}";
    }
}