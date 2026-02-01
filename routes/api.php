<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\InspectionController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CompanySettingsController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisterController::class, 'store'])->middleware('guest');
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:6,1']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/settings', [CompanySettingsController::class, 'show']);
    Route::put('/settings', [CompanySettingsController::class, 'update']);

    Route::middleware('role:admin,werfleider')->group(function () {
        Route::get('/sites', [SiteController::class, 'index']);
        Route::get('/sites/{id}', [SiteController::class, 'show']);
        Route::post('/sites', [SiteController::class, 'store']);
        Route::put('/sites/{id}', [SiteController::class, 'update']);
        Route::delete('/sites/{id}', [SiteController::class, 'destroy']);

        Route::get('/machines', [MachineController::class, 'index']);
        Route::get('/machines/{id}', [MachineController::class, 'show']);
        Route::post('/machines', [MachineController::class, 'store']);
        Route::put('/machines/{id}', [MachineController::class, 'update']);
        Route::delete('/machines/{id}', [MachineController::class, 'destroy']);

        Route::post('/machines/{machineId}/inspections', [InspectionController::class, 'store']);
        Route::put('/inspections/{id}', [InspectionController::class, 'update']);
        Route::delete('/inspections/{id}', [InspectionController::class, 'destroy']);

        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::put('/employees/{id}', [EmployeeController::class, 'update']);
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

        Route::post('/employees/{employeeId}/certificates', [CertificateController::class, 'store']);
        Route::put('/certificates/{id}', [CertificateController::class, 'update']);
        Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    });

    Route::middleware('role:admin,werfleider,preventieadviseur')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/{id}', [EmployeeController::class, 'show']);
        Route::get('/employees/{employeeId}/certificates', [CertificateController::class, 'index']);
        Route::get('/certificates/{id}', [CertificateController::class, 'show']);
        Route::get('/machines/{machineId}/inspections', [InspectionController::class, 'index']);
        Route::get('/inspections/{id}', [InspectionController::class, 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
    });
});

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
