<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Throwable;

class UsersShowController extends Controller
{
    use JsonResponseTrait;

    /**
     * Affiche un utilisateur spécifique.
     */
    public function show(int $id)
    {
        try {
            $user = User::select(
                'id',
                'prenom',
                'nom',
                'email',
                'phone',
                'role',
                'statut',
                'pays',
                'adresse',
                'ville',
                'code_postal',
                'quartier',
                'created_at'
            )->findOrFail($id);

            return $this->successResponse(
                'Utilisateur récupéré avec succès.',
                $user
            );
        } catch (Throwable $e) {
            return $this->handleException(
                $e,
                'Une erreur est survenue lors de la récupération de l’utilisateur.'
            );
        }
    }
}
