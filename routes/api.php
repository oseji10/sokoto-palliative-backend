<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancerController;
use App\Http\Controllers\EnrolleesController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\LgaController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Remove Sanctum route if not using Sanctum
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['cors'])->group(function () {
    // Public routes
    Route::post('/users/register', [AuthController::class, 'register']);
    Route::post('/users/login', [AuthController::class, 'login']);
    Route::post('/users/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/users/profile', [AuthController::class, 'profile'])->middleware('auth.jwt'); // Use auth.jwt instead of auth:api

    // Protected routes with JWT authentication
    Route::middleware(['auth.jwt'])->group(function () {
        Route::get('/user', function () {
            $user = auth()->user();
            return response()->json([
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'role' => $user->role,
                'id' => $user->id,
                'message' => 'User authenticated successfully',
            ]);
        });

        // Cancer routes
        Route::get('/diseases', [CancerController::class, 'index']);
        Route::post('/diseases', [CancerController::class, 'store']);
        Route::put('/diseases/{cancerId}/edit', [CancerController::class, 'edit']);
        Route::delete('/diseases/{cancerId}/delete', [CancerController::class, 'destroy']);

        // Product routes
        Route::get('/products', [ProductsController::class, 'index']);
        Route::post('/products', [ProductsController::class, 'store']);
        Route::put('/products/{productId}/edit', [ProductsController::class, 'edit']);
        Route::delete('/products/{productId}/delete', [ProductsController::class, 'destroy']);
        Route::get('/products/types', [ProductsController::class, 'productTypes']);

        // Location and contact routes
        Route::get('/locations', [StateController::class, 'index']);
        Route::get('/contacts', [AuthController::class, 'contacts']);

        // User profile and logout (logout might be redundant here)
        Route::get('/profile', [UsersController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // User and staff routes
        Route::get('/users', [UsersController::class, 'index']);
        Route::get('/supervisors', [UsersController::class, 'supervisors']);
        Route::get('/lgas', [LgaController::class, 'index']);
        Route::get('/staff-types', [UsersController::class, 'staff_type']);

        // Enrollee routes
        Route::get('/enrollees', [EnrolleesController::class, 'index']);
        Route::post('/enrollees', [EnrolleesController::class, 'store']);
        Route::get('/enrollees/types', [EnrolleesController::class, 'enrolleeTypes']);
    });
});