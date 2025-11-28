<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $currencies = [
            [
                'code' => 'GNF',
                'name' => 'Franc guinéen',
                'symbol' => 'GNF',
                'is_active' => true,
                'is_base_currency' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'is_active' => true,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'USD',
                'name' => 'Dollar américain',
                'symbol' => '$',
                'is_active' => true,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'GBP',
                'name' => 'Livre sterling',
                'symbol' => '£',
                'is_active' => true,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'XOF',
                'name' => 'Franc CFA',
                'symbol' => 'CFA',
                'is_active' => true,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'code' => 'CHF',
                'name' => 'Franc suisse',
                'symbol' => 'CHF',
                'is_active' => true,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- NOUVELLES DEVISES ---
            [
                'code' => 'CAD',
                'name' => 'Dollar canadien',
                'symbol' => 'CA$',
                'is_active' => false,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CNY',
                'name' => 'Yuan chinois (Renminbi)',
                'symbol' => '¥',
                'is_active' => true,
                'is_base_currency' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('currencies')->insert($currencies);
    }
}
// php artisan db:seed --class=CurrencySeeder
