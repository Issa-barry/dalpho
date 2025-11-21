<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        // On récupère l'admin (adapté à ton système)
        $admin = User::where('email', 'issabarry67@gmail.com')->firstOrFail();
        // ou: $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        // Devises
        $gnf = Currency::where('code', 'GNF')->firstOrFail();
        $eur = Currency::where('code', 'EUR')->firstOrFail();
        $usd = Currency::where('code', 'USD')->firstOrFail();
        $gbp = Currency::where('code', 'GBP')->firstOrFail();
        $xof = Currency::where('code', 'XOF')->firstOrFail();

        // Taux d'exemple
        $rates = [
            [
                'from' => $eur,
                'to'   => $gnf,
                'rate' => 10700,   // 1 EUR = 10 700 GNF
            ],
            [
                'from' => $usd,
                'to'   => $gnf,
                'rate' => 10000,   // 1 USD = 10 000 GNF
            ],
            [
                'from' => $gbp,
                'to'   => $gnf,
                'rate' => 12500,   // 1 GBP = 12 500 GNF
            ],
            [
                'from' => $xof,
                'to'   => $gnf,
                'rate' => 16,      // 1 XOF = 16 GNF (exemple)
            ],
        ];

        foreach ($rates as $data) {
            ExchangeRate::updateOrCreate(
                [
                    'from_currency_id' => $data['from']->id,
                    'to_currency_id'   => $data['to']->id,
                    'is_current'       => true,
                ],
                [
                    'rate'           => $data['rate'],
                    'agent_id'       => $admin->id,   // 👈 plus de NULL
                    'effective_date' => now(),
                ]
            );
        }
    }
}
// php artisan db:seed --class=ExchangeRateSeeder