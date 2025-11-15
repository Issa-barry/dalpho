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
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return $this->unauthorizedResponse('Identifiants invalides');
            }

            // Token simple (nom générique)
            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse('Connexion réussie', [
                'token' => $token,
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
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
