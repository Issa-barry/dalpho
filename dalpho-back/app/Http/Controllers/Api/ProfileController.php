<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use JsonResponseTrait;

    /**
     * Retourner les informations du user connecté
     */
    public function me(Request $request)
    {
        $user = $request->user(); // récupéré via Sanctum

        return $this->successResponse('Utilisateur connecté', [
            'user' => $user,
        ]);
    }
}
