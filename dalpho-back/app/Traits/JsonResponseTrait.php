<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait JsonResponseTrait
{
    /**
     * Réponse JSON de succès
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(string $message, $data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Réponse JSON d'erreur
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message, $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse JSON créée avec succès (201)
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    protected function createdResponse(string $message, $data = null): JsonResponse
    {
        return $this->successResponse($message, $data, 201);
    }

    /**
     * Réponse JSON de ressource non trouvée (404)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Réponse JSON d'erreur de validation (422)
     *
     * @param string $message
     * @param array $errors
     * @return JsonResponse
     */
    protected function validationErrorResponse(string $message = 'Erreur de validation', array $errors = []): JsonResponse
    {
        return $this->errorResponse($message, $errors, 422);
    }

    /**
     * Réponse JSON non autorisé (401)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Non autorisé'): JsonResponse
    {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Réponse JSON interdit (403)
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Accès interdit'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * Réponse JSON d'erreur serveur (500)
     *
     * @param string $message
     * @param Throwable|null $exception
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Erreur serveur', ?Throwable $exception = null): JsonResponse
    {
        // Logger l'erreur pour le débogage
        if ($exception) {
            Log::error($message, [
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        }

        // En production, ne pas exposer les détails de l'exception
        $errors = null;
        if (config('app.debug') && $exception) {
            $errors = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return $this->errorResponse($message, $errors, 500);
    }

    /**
     * Réponse JSON sans contenu (204)
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Gestion centralisée des exceptions
     *
     * @param Throwable $exception
     * @param string $defaultMessage
     * @return JsonResponse
     */
    protected function handleException(Throwable $exception, string $defaultMessage = 'Une erreur est survenue'): JsonResponse
    {
        // Erreur de validation
        if ($exception instanceof ValidationException) {
            return $this->validationErrorResponse(
                'Les données fournies sont invalides',
                $exception->errors()
            );
        }

        // Modèle non trouvé
        if ($exception instanceof ModelNotFoundException) {
            return $this->notFoundResponse('Ressource non trouvée');
        }

        // Route non trouvée
        if ($exception instanceof NotFoundHttpException) {
            return $this->notFoundResponse('Endpoint non trouvé');
        }

        // Erreur générique
        return $this->serverErrorResponse($defaultMessage, $exception);
    }

    /**
     * Réponse JSON avec pagination
     *
     * @param mixed $paginator
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($paginator, string $message = 'Données récupérées avec succès'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ], 200);
    }

    /**
     * Réponse JSON générique (fonction de base)
     *
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function responseJson(bool $success, string $message, $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}