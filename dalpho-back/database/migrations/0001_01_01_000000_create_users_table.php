<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identité
            $table->string('prenom');
            $table->string('nom');

            // Email NON obligatoire
            $table->string('email')->unique()->nullable();

            // Téléphone TOUJOURS obligatoire
            $table->string('phone')->unique();

            // Identification (nullable si client)
            $table->enum('type_id', ['passeport', 'carte_identite'])->nullable();
            $table->string('numero_id')->unique()->nullable();

            // Statut
            $table->enum('statut', ['attente', 'active', 'bloque', 'archive'])
                  ->default('attente');

            // Rôle
            $table->enum('role', ['client', 'agent', 'manager', 'admin'])
                  ->default('client');

            // Adresse (facultative pour client)
            $table->string('pays')->default('Guinée-Conakry');
            $table->string('ville')->nullable();
            $table->string('quartier')->nullable();
            $table->string('adresse')->nullable();
            $table->string('code_postal')->nullable();

            // Login & sécurité
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Fortify (optionnel)
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
