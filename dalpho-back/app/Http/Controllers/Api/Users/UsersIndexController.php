<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Throwable;

class UsersIndexController extends Controller
{
    use JsonResponseTrait;

    /**
     * Affiche la liste des utilisateurs.
     */
    public function index()
    {
        try {
            $users = User::select(
                'id',
                'prenom',
                'nom',
                'email',
                'phone',
                'role',
                'statut',
                'pays',
                'ville',
                'created_at'
            )
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse(
                'Liste des utilisateurs récupérée avec succès.',
                $users
            );
        } catch (Throwable $e) {
            return $this->handleException(
                $e,
                'Une erreur est survenue lors de la récupération de la liste des utilisateurs.'
            );
        }
    }
}