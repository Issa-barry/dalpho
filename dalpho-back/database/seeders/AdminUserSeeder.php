<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifie si un admin existe déjà
        if (User::where('role', 'admin')->exists()) {
            $this->command->info('Un administrateur existe déjà. Aucun nouvel admin créé.');
            return;
        }

        User::create([
               'prenom'        => 'Super',
                'nom'           => 'Admin',
                'email'         => 'issabarry67@gmail.com',
                'phone'         => '+33758855039',
                'type_id'       => 'carte_identite',
                'numero_id'     => '05329545',
                'statut'        => 'active',
                'role'          => 'admin',

                'pays'          => 'Guinée-Conakry',
                'ville'         => 'Conakry',
                'quartier'      => 'Kaporo',
                'adresse'       => 'Rue de la Corniche',
                'code_postal'   => '0000',

                'password'      => Hash::make('Jeux@2019'),
                'email_verified_at' => now(),
        ]);

        $this->command->info('Administrateur créé : issabarry67@gmail.com');
    }
}

// php artisan db:seed --class=AdminUserSeeder