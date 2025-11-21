<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class StaffRegisterController extends Controller
{
    use JsonResponseTrait;

    /**
     * Création d'un utilisateur interne (agent, manager, admin).
     */
    public function store(Request $request)
    {
        // 1) Validation manuelle
        $validator = Validator::make($request->all(), [
            'prenom'  => ['required', 'string', 'max:255'],
            'nom'     => ['required', 'string', 'max:255'],
            'phone'   => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class),
            ],
            'email'   => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'min:8'],

            // rôle staff uniquement
            'role' => [
                'required',
                'string',
                Rule::in([User::ROLE_AGENT, User::ROLE_MANAGER, User::ROLE_ADMIN]),
            ],

            'type_id'     => ['required', 'string', Rule::in(['passeport', 'carte_identite'])],
            'numero_id'   => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'statut'      => ['required', 'string', Rule::in(User::STATUTS)],

            // facultatifs
            'pays'        => ['nullable', 'string', 'max:255'],
            'ville'       => ['nullable', 'string', 'max:255'],
            'quartier'    => ['nullable', 'string', 'max:255'],
            'adresse'     => ['nullable', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                'Les données fournies sont invalides',
                $validator->errors()->toArray()
            );
        }

        $validated = $validator->validated();

        try {
            // 2) Création du staff
            $user = User::create([
                'prenom'    => $validated['prenom'],
                'nom'       => $validated['nom'],
                'phone'     => $validated['phone'],
                'email'     => $validated['email'],
                'password'  => $validated['password'], // hash auto

                'role'      => $validated['role'],

                'type_id'   => $validated['type_id'],
                'numero_id' => $validated['numero_id'],
                'statut'    => $validated['statut'],

                // facultatifs
                'pays'        => $validated['pays']        ?? 'Guinée-Conakry',
                'ville'       => $validated['ville']       ?? null,
                'quartier'    => $validated['quartier']    ?? null,
                'adresse'     => $validated['adresse']     ?? null,
                'code_postal' => $validated['code_postal'] ?? null,
            ]);

            return $this->createdResponse('Utilisateur interne créé avec succès', [
                'user' => $user,
            ]);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la création de l\'utilisateur interne');
        }
    }
}
