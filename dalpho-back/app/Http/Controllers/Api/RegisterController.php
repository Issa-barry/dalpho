<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Throwable;

class RegisterController extends Controller
{
    use JsonResponseTrait;

    /**
     * Inscription d'un nouvel utilisateur (API)
     */
    public function register(Request $request, CreateNewUser $creator)
    {
        try {
            // CreateNewUser fait déjà la validation + création
            $user = $creator->create($request->all());

            // Token API (Sanctum)
            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse('Inscription réussie', [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'        => $user->id,
                    'prenom'    => $user->prenom,
                    'nom'       => $user->nom,
                    'email'     => $user->email,
                    'phone'     => $user->phone,
                    'role'      => $user->role,
                    'statut'    => $user->statut,
                    'pays'      => $user->pays,
                    'ville'     => $user->ville,
                    'quartier'  => $user->quartier,
                    'adresse'   => $user->adresse,
                    'code_postal' => $user->code_postal,
                ],
            ], 201);
        } catch (Throwable $e) {
            // même pattern que ton AuthController
            return $this->handleException($e, 'Erreur lors de l\'inscription');
        }
    }
}
