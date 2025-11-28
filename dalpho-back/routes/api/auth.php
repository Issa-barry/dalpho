<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientRegisterController;
 
use App\Http\Controllers\Api\StaffRegisterController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Users\UsersIndexController;
use App\Http\Controllers\Users\UsersShowController;


Route::post('/login', [AuthController::class, 'login']);
 
Route::get('/users', [UsersIndexController::class, 'index']);
 

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/clients', [ClientRegisterController::class, 'store']);

    // Staff : agent, manager, admin
    Route::post('/staff', [StaffRegisterController::class, 'store']); 

    Route::get('/me', [ProfileController::class, 'me']);

   Route::get('/users/{id}', [UsersShowController::class, 'show']); // OK
});
 

 
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur connecté',
            'data' => $request->user(),
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });


});

  