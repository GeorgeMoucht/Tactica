<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GuardianController;
use App\Http\Controllers\Api\V1\MembershipController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\StudentHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

/**
 * This is how we create routes with middleware
 * Route::middleware(['auth:api', CheckToken::using('admin')])
 *    ->get('/admin/dashboard', ...);
 *
 * Route::middleware(['auth:api', CheckToken::using('teacher')])
 *    ->get('/teacher/classes', ...);
 */

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
    Route::post('auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh')->middleware('throttle:auth-refresh');

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:auth-change-password');

        // THS IS GOING TO BE DEPRECATED
        // Route::post('/registrations', [RegistrationController::class, 'store']);

        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{student}', [StudentController::class, 'show']);
        Route::put('/students/{student}', [StudentController::class, 'update']);

        // Route::post('/students/{student}/purchases', [PurchaseController::class, 'store']);

        Route::post('/students/{student}/memberships', [MembershipController::class, 'store']);
        Route::get('/students/{student}/history', [StudentHistoryController::class, 'index']);


        Route::post('/guardians', [GuardianController::class, 'store']);
        Route::get('/guardians', [GuardianController::class, 'index']);
        Route::get('/guardians/{guardian}', [GuardianController::class, 'show']);
        Route::put('/guardians/{guardian}', [GuardianController::class, 'update']);


    });
});
