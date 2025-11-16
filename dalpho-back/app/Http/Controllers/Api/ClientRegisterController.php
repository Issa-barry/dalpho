<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class ClientRegisterController extends Controller
{
    use JsonResponseTrait;

    /**
     * Création d'un client (par l'admin, back-office).
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
            'password' => ['required', 'string', 'min:8'],

            // Email FACULTATIF
            'email'       => ['nullable', 'email', 'max:255', Rule::unique(User::class)],

            // Identification facultative
            'type_id'     => ['nullable', 'string', Rule::in(['passeport', 'carte_identite'])],
            'numero_id'   => ['nullable', 'string', 'max:255', Rule::unique(User::class)],

            // Statut facultatif
            'statut'      => ['nullable', 'string', Rule::in(User::STATUTS)],

            // Adresse facultative
            'pays'        => ['nullable', 'string', 'max:255'],
            'ville'       => ['nullable', 'string', 'max:255'],
            'quartier'    => ['nullable', 'string', 'max:255'],
            'adresse'     => ['nullable', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'max:50'],
        ]);

        // Validation échouée → JSON 422
        if ($validator->fails()) {
            return $this->validationErrorResponse(
                'Les données fournies sont invalides',
                $validator->errors()->toArray()
            );
        }

        $validated = $validator->validated();

        try {
            // 2) Création du client
            $user = User::create([
                'prenom'    => $validated['prenom'],
                'nom'       => $validated['nom'],
                'phone'     => $validated['phone'],

                'role'      => User::ROLE_CLIENT,
                'password'  => $validated['password'],

                // Facultatifs
                'email'       => $validated['email']       ?? null,
                'type_id'     => $validated['type_id']     ?? null,
                'numero_id'   => $validated['numero_id']   ?? null,
                'statut'      => $validated['statut']      ?? User::STATUT_ATTENTE,
                'pays'        => $validated['pays']        ?? 'Guinée-Conakry',
                'ville'       => $validated['ville']       ?? null,
                'quartier'    => $validated['quartier']    ?? null,
                'adresse'     => $validated['adresse']     ?? null,
                'code_postal' => $validated['code_postal'] ?? null,
            ]);

            return $this->createdResponse('Client créé avec succès', [
                'user' => $user,
            ]);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la création du client');
        }
    }
}
