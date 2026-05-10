<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\ProfesionalController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\DisponibilidadController;

// Rutas públicas (no necesitan token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Rutas protegidas (necesitan token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::apiResource('citas',         CitaController::class);
    Route::apiResource('profesionales', ProfesionalController::class);
    Route::apiResource('servicios',     ServicioController::class);

    Route::get('disponibilidades/{profesional_id}',       [DisponibilidadController::class, 'getByProfesional']);
    Route::post('disponibilidades',                       [DisponibilidadController::class, 'store']);
    Route::delete('disponibilidades/{id}',                [DisponibilidadController::class, 'destroy']);

    Route::get('citas-ocupadas/{profesional_id}/{fecha}', [CitaController::class, 'horasOcupadas']);
});