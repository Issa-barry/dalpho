<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_currency_id')
                ->constrained('currencies')
                ->onDelete('cascade')
                ->comment('Devise source');

            $table->foreignId('to_currency_id')
                ->constrained('currencies')
                ->onDelete('cascade')
                ->comment('Devise cible');

            $table->unsignedBigInteger('rate')
                ->comment('Taux de change (en centimes/plus petite unité)');

            $table->foreignId('agent_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Agent qui a saisi le taux');

            $table->date('effective_date')->comment('Date d\'application du taux');

            $table->boolean('is_current')
                ->default(true)
                ->comment('Taux actuel');

            /* ---- Statistiques High/Low ---- */
            $table->unsignedBigInteger('day_high')
                ->nullable()
                ->comment('Plus haut du jour');

            $table->unsignedBigInteger('day_low')
                ->nullable()
                ->comment('Plus bas du jour');

            /* ---- Variations ---- */
            $table->bigInteger('change_abs')
                ->nullable()
                ->comment('Variation absolue');

            $table->decimal('change_pct', 8, 4)
                ->nullable()
                ->comment('Variation en %');

            $table->string('direction', 10)
                ->nullable()
                ->comment('Direction: up / down / flat');

            $table->unsignedBigInteger('buy_rate')
                ->nullable()
                ->comment('Taux d\'achat');

            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['from_currency_id', 'to_currency_id', 'is_current'], 'idx_current_rates');
            $table->index('effective_date', 'idx_effective_date');

            // Contrainte unique pour éviter les doublons
            $table->unique(
                ['from_currency_id', 'to_currency_id', 'is_current', 'effective_date'],
                'unique_current_rate'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};