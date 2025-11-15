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
            $table->decimal('rate', 15, 4)->comment('Taux de change');
            $table->foreignId('agent_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Agent qui a saisi le taux');
            $table->date('effective_date')->comment('Date d\'application du taux');
            $table->boolean('is_current')->default(true)->comment('Taux actuel');
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['from_currency_id', 'to_currency_id', 'is_current'], 'idx_current_rates');
            $table->index('effective_date', 'idx_effective_date');
            
            // Contrainte unique pour éviter les doublons
            $table->unique(['from_currency_id', 'to_currency_id', 'is_current', 'effective_date'], 'unique_current_rate');
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