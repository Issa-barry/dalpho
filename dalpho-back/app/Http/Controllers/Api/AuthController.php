<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends Controller
{
    use JsonResponseTrait;

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'       => 'required|email',
                'password'    => 'required',
                'device_name' => 'required', // ex: "mobile", "web-app"
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return $this->unauthorizedResponse('Identifiants invalides');
            }

            // Création du token perso
            $token = $user->createToken($validated['device_name'])->plainTextToken;

            return $this->successResponse('Connexion réussie', [
                'token' => $token,
                'user'  => $user,
            ]);
        } catch (Throwable $e) {
            // ValidationException, ModelNotFound, etc. gérés par handleException
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
