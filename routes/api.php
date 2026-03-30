<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\CitaController;

Route::get('/citas', [CitaController::class, 'index']);
Route::post('/citas', [CitaController::class, 'store']);
Route::get('/test', [TestController::class, 'index']);
Route::delete('/citas/{id}', [CitaController::class, 'destroy']);
Route::put('/citas/{id}', [CitaController::class, 'update']);