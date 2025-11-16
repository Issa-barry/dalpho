<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            // Toujours obligatoires pour tout le monde
            'prenom'  => ['required', 'string', 'max:255'],
            'nom'     => ['required', 'string', 'max:255'],
            'phone'   => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class),
            ],
            'role'    => [
                'required',
                'string',
                Rule::in(User::ROLES), // ['client','agent','manager','admin']
            ],
            'password' => $this->passwordRules(),

            // Obligatoires pour agent / manager / admin, optionnels pour client
            'email' => [
                'required_unless:role,client',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'type_id' => [
                'required_unless:role,client',
                'string',
                Rule::in(['passeport', 'carte_identite']),
            ],
            'numero_id' => [
                'required_unless:role,client',
                'string',
                'max:255',
                Rule::unique(User::class),
            ],
            'statut' => [
                'required_unless:role,client',
                'string',
                Rule::in(User::STATUTS), // ['attente','active','bloque','archive']
            ],

            'pays'        => ['required_unless:role,client', 'string', 'max:255'],
            'ville'       => ['required_unless:role,client', 'string', 'max:255'],
            'quartier'    => ['required_unless:role,client', 'string', 'max:255'],
            'adresse'     => ['required_unless:role,client', 'string', 'max:255'],
            'code_postal' => ['required_unless:role,client', 'string', 'max:50'],
        ])->validate();

        return User::create([
            'prenom'    => $input['prenom'],
            'nom'       => $input['nom'],
            'phone'     => $input['phone'],
            'role'      => $input['role'],

            // Pour un client, ces champs peuvent être absents/null
            'email'     => $input['email']     ?? null,
            'type_id'   => $input['type_id']   ?? null,
            'numero_id' => $input['numero_id'] ?? null,
            'statut'    => $input['statut']    ?? User::STATUT_ATTENTE,

            'pays'        => $input['pays']        ?? 'Guinée-Conakry',
            'ville'       => $input['ville']       ?? null,
            'quartier'    => $input['quartier']    ?? null,
            'adresse'     => $input['adresse']     ?? null,
            'code_postal' => $input['code_postal'] ?? null,

            // hash géré par le cast du model
            'password'  => $input['password'],
        ]);
    }
}
