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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique()->comment('Code ISO de la devise (EUR, USD, GNF)');
            $table->string('name', 100)->comment('Nom complet de la devise');
            $table->string('symbol', 10)->comment('Symbole de la devise (€, $, GNF)');
            $table->boolean('is_active')->default(true)->comment('Devise active ou non');
            $table->boolean('is_base_currency')->default(false)->comment('Devise de base (GNF)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};