<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancerController;
use App\Http\Controllers\BeneficiariesController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\LgaController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\CadreController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\TransactionsController;

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


// Route::middleware(['cors'])->group(function () {
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

        // Ministries routes
        Route::get('/ministries', [MinistryController::class, 'index']);
        Route::get('/ministries/{ministryId}', [MinistryController::class, 'show']);
        Route::post('/ministries', [MinistryController::class, 'store']);
        Route::put('/ministries/{ministryId}/edit', [MinistryController::class, 'update']);
        Route::delete('/ministries/{ministryId}/delete', [MinistryController::class, 'destroy']);

        // Cadres routes
        Route::get('/cadres', [CadreController::class, 'index']);
        Route::get('/cadres/{cadreId}', [CadreController::class, 'show']);
        Route::post('/cadres', [CadreController::class, 'store']);
        Route::put('/cadres/{cadreId}/edit', [CadreController::class, 'update']);
        Route::delete('/cadres/{cadreId}/delete', [CadreController::class, 'destroy']);

        // Stock routes
        Route::get('/stock', [StockController::class, 'index']);
        Route::get('/stock/{stockId}', [StockController::class, 'show']);
        Route::post('/stock', [StockController::class, 'store']);
        Route::put('/stock/{stockId}/edit', [StockController::class, 'update']);
        Route::delete('/stock/{stockId}/delete', [StockController::class, 'destroy']);

        // Transaction route
        Route::get('/transactions', [TransactionsController::class, 'index']);
        Route::get('/transactions/{transactionId}', [TransactionsController::class, 'show']);
        Route::post('/transactions', [TransactionsController::class, 'store']);
        Route::put('/transactions/{transactionId}/edit', [TransactionsController::class, 'update']);
        Route::delete('/transactions/{transactionId}/delete', [TransactionsController::class, 'destroy']);

         // Product Request routes
        Route::get('/product-request', [ProductRequestController::class, 'index']);
        Route::get('/product-request/{productRequestId}', [ProductRequestController::class, 'show']);
        Route::post('/product-request', [ProductRequestController::class, 'store']);
        Route::put('/product-request/{productRequestId}/edit', [ProductRequestController::class, 'update']);
        Route::delete('/product-request/{productRequestId}/delete', [ProductRequestController::class, 'destroy']);


        // Product routes
        Route::get('/products', [ProductsController::class, 'index']);
        Route::post('/products', [ProductsController::class, 'store']);
        Route::put('/products/{productId}/edit', [ProductsController::class, 'edit']);
        Route::delete('/products/{productId}/delete', [ProductsController::class, 'destroy']);
        Route::get('/products/types', [ProductsController::class, 'productTypes']);
        Route::get('/products/{productId}', [ProductsController::class, 'show']);
        Route::post('/products/add-product-image', [ProductsController::class, 'addProductImage']);
        
        Route::get('/products/{productId}/images', [ProductsController::class, 'getProductImages']);

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
        Route::get('/staff/types', [UsersController::class, 'staff_type']);

        // Beneficiary routes
        Route::get('/beneficiaries', [BeneficiariesController::class, 'index']);
        Route::post('/beneficiaries', [BeneficiariesController::class, 'store']);
        Route::get('/beneficiaries/types', [BeneficiariesController::class, 'beneficiaryTypes']);
        // Route::get('/beneficiaries/{id}', [BeneficiariesController::class, 'show']);
        Route::put('/beneficiaries/{id}/edit', [BeneficiariesController::class, 'update']);
        Route::delete('/beneficiaries/{id}/delete', [BeneficiariesController::class, 'destroy']);
        
        Route::get('/beneficiaries/onboarder', [BeneficiariesController::class, 'getOnboarderBeneficiaries']);
        

        Route::post('/staff', [UsersController::class, 'store']);
        Route::delete('/staff/{id}/delete', [UsersController::class, 'destroy']);

        
        Route::get('analytics/total-users', [AnalyticsController::class, 'getTotalBeneficiaries']);
    });
// });