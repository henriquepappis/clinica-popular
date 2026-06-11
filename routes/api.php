<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\WaitingListController;

// Rotas públicas de autenticação
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Pacientes
    Route::apiResource('patients', PatientController::class);

    // Especialidades
    Route::apiResource('specialties', SpecialtyController::class);

    // Médicos
    Route::apiResource('doctors', DoctorController::class);

    // Turnos
    Route::apiResource('shifts', ShiftController::class);

    // Agendamentos
    Route::prefix('appointments')->group(function () {
        Route::get('', [AppointmentController::class, 'index']);
        Route::post('', [AppointmentController::class, 'store']);
        Route::get('{appointment}', [AppointmentController::class, 'show']);
        Route::post('{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::post('{appointment}/cancel', [AppointmentController::class, 'cancel']);
    });

    // Preços
    Route::apiResource('prices', PriceController::class);

    // Fila de espera
    Route::prefix('waiting-lists')->group(function () {
        Route::get('', [WaitingListController::class, 'index']);
        Route::post('', [WaitingListController::class, 'store']);
        Route::get('{waitingList}', [WaitingListController::class, 'show']);
        Route::post('{waitingList}/notify', [WaitingListController::class, 'notify']);
        Route::delete('{waitingList}', [WaitingListController::class, 'destroy']);
    });
});
