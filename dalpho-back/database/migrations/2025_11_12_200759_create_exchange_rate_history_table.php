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
        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_rate_id')
                ->constrained('exchange_rates')
                ->onDelete('cascade')
                ->comment('Référence au taux de change');
            $table->foreignId('from_currency_id')
                ->constrained('currencies')
                ->onDelete('cascade')
                ->comment('Devise source');
            $table->foreignId('to_currency_id')
                ->constrained('currencies')
                ->onDelete('cascade')
                ->comment('Devise cible');
            $table->decimal('old_rate', 15, 4)->nullable()->comment('Ancien taux');
            $table->decimal('new_rate', 15, 4)->comment('Nouveau taux');
            $table->foreignId('changed_by')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Agent qui a effectué le changement');
            $table->text('change_reason')->nullable()->comment('Raison du changement');
            $table->timestamp('created_at')->useCurrent()->comment('Date du changement');

            // Index pour améliorer les performances des requêtes historiques
            $table->index(['exchange_rate_id', 'created_at'], 'idx_history_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
    }
};