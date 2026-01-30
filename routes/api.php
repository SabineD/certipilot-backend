<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sites', [SiteController::class, 'index']);
    Route::get('/sites/{id}', [SiteController::class, 'show']);
    Route::get('/machines', [MachineController::class, 'index']);
    Route::get('/machines/{id}', [MachineController::class, 'show']);
    Route::post('/machines', [MachineController::class, 'store']);
    Route::put('/machines/{id}', [MachineController::class, 'update']);
    Route::delete('/machines/{id}', [MachineController::class, 'destroy']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
});

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
