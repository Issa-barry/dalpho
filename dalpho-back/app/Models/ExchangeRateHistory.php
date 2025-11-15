<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRateHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_rate_history';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exchange_rate_id',
        'from_currency_id',
        'to_currency_id',
        'old_rate',
        'new_rate',
        'changed_by',
        'change_reason'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_rate' => 'decimal:4',
        'new_rate' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    /**
     * Relation : Taux de change associé
     */
    public function exchangeRate()
    {
        return $this->belongsTo(ExchangeRate::class);
    }

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
     * Relation : Utilisateur qui a effectué le changement
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Boot method pour gérer les événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Toujours définir created_at lors de la création
        static::creating(function ($history) {
            $history->created_at = now();
        });
    }

    /**
     * Calculer la différence de taux
     *
     * @return float|null
     */
    public function getRateDifference()
    {
        if ($this->old_rate === null) {
            return null;
        }

        return $this->new_rate - $this->old_rate;
    }

    /**
     * Calculer le pourcentage de changement
     *
     * @return float|null
     */
    public function getPercentageChange()
    {
        if ($this->old_rate === null || $this->old_rate == 0) {
            return null;
        }

        return (($this->new_rate - $this->old_rate) / $this->old_rate) * 100;
    }

    /**
     * Obtenir la différence formatée
     *
     * @return string|null
     */
    public function getFormattedDifferenceAttribute()
    {
        $diff = $this->getRateDifference();
        
        if ($diff === null) {
            return 'Création';
        }

        $sign = $diff > 0 ? '+' : '';
        return $sign . number_format($diff, 4, '.', ' ');
    }

    /**
     * Obtenir le pourcentage formaté
     *
     * @return string|null
     */
    public function getFormattedPercentageAttribute()
    {
        $percentage = $this->getPercentageChange();
        
        if ($percentage === null) {
            return null;
        }

        $sign = $percentage > 0 ? '+' : '';
        return $sign . number_format($percentage, 2) . '%';
    }

    /**
     * Scope : Historique pour une paire de devises
     */
    public function scopeForPair($query, $fromCurrencyId, $toCurrencyId)
    {
        return $query->where('from_currency_id', $fromCurrencyId)
                     ->where('to_currency_id', $toCurrencyId);
    }

    /**
     * Scope : Historique par agent
     */
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('changed_by', $agentId);
    }

    /**
     * Scope : Historique récent
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Obtenir la description du changement
     *
     * @return string
     */
    public function getChangeDescriptionAttribute()
    {
        if ($this->old_rate === null) {
            return "Taux initial créé : {$this->new_rate} {$this->toCurrency->code}";
        }

        $diff = $this->getRateDifference();
        $direction = $diff > 0 ? 'augmenté' : 'diminué';
        
        return "Taux {$direction} de {$this->old_rate} à {$this->new_rate} {$this->toCurrency->code}";
    }
}