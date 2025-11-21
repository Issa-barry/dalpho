<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    use JsonResponseTrait;

    /**
     * Connexion via numéro de téléphone + mot de passe
     */
    public function login(Request $request)
    {
        try {
            // Validation manuelle
            $validator = Validator::make($request->all(), [
                'phone'    => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse(
                    'Les données fournies sont invalides',
                    $validator->errors()->toArray()   // 👈 ICI le toArray()
                );
            }

            $validated = $validator->validated();

            // Recherche utilisateur par téléphone
            $user = User::where('phone', $validated['phone'])->first();

            // Vérification du mot de passe
            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return $this->unauthorizedResponse('Identifiants invalides');
            }

            // Création du token d'accès
            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse('Connexion réussie', [
                'access_token' => $token,
                'user' => [
                    'id'     => $user->id,
                    'prenom' => $user->prenom,
                    'nom'    => $user->nom,
                    'role'   => $user->role,
                    'phone'  => $user->phone,
                    'email'  => $user->email,
                    'statut' => $user->statut,
                ]
            ]);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la connexion');
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->user()?->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            return $this->successResponse('Déconnexion réussie');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la déconnexion');
        }
    }

    public function logoutAll(Request $request)
    {
        try {
            $request->user()?->tokens()->delete();
            return $this->successResponse('Toutes les sessions ont été déconnectées');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Erreur lors de la déconnexion globale');
        }
    }
}
