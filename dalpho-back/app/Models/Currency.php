<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'is_base_currency'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_base_currency' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation : Taux de change depuis cette devise (source)
     */
    public function exchangeRatesFrom()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    /**
     * Relation : Taux de change vers cette devise (cible)
     */
    public function exchangeRatesTo()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    /**
     * Obtenir le taux de change actuel vers une autre devise
     *
     * @param int $toCurrencyId
     * @return ExchangeRate|null
     */
    public function getCurrentRate($toCurrencyId)
    {
        return $this->exchangeRatesFrom()
            ->where('to_currency_id', $toCurrencyId)
            ->where('is_current', true)
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Obtenir tous les taux actuels depuis cette devise
     */
    public function getCurrentRates()
    {
        return $this->exchangeRatesFrom()
            ->where('is_current', true)
            ->where('effective_date', '<=', now())
            ->with('toCurrency')
            ->get();
    }

    /**
     * Vérifier si la devise est la devise de base
     *
     * @return bool
     */
    public function isBaseCurrency()
    {
        return $this->is_base_currency === true;
    }

    /**
     * Scope : Devises actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Obtenir la devise de base
     */
    public function scopeBase($query)
    {
        return $query->where('is_base_currency', true);
    }

    /**
     * Obtenir la devise de base (GNF)
     *
     * @return Currency|null
     */
    public static function getBaseCurrency()
    {
        return self::where('is_base_currency', true)->first();
    }
}