<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CancerController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LgaController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\EnrolleesController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::middleware('jwt.auth')->post('/dispense', function (Request $request) {
    // Validate prescription_id, quantity, check inventory, etc.
    // Update inventory and return response
    return response()->json(['message' => 'Drug dispensed successfully']);
});



// Protected routes (require JWT)
Route::middleware('auth:api')->group(function () {
    // Route::get('/cancers', CancerController::class . '@index');
   
});


Route::get('/roles', [RolesController::class, 'retrieveAll']);
Route::get('/roles/hospital', [RolesController::class, 'hospitalRoles']);
Route::get('/roles/nicrat', [RolesController::class, 'nicratRoles']);
Route::post('/roles', [RolesController::class, 'store']);

Route::post('/users/register', [AuthController::class, 'register']);
Route::post('/users/login', [AuthController::class, 'login']);
Route::post('/users/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/users/profile', [AuthController::class, 'profile'])->middleware('auth:api');

// Route::middleware('auth:api')->get('/user', function () {
//     $user = auth()->user();
//     return response()->json([
//         'name' => $user->name,
//         'role' => $user->role, // Adjust based on your User model
//     ]);
// });


Route::middleware(['auth.jwt'])->group(function () {
Route::get('/user', function(){
    $user = auth()->user();
    return response()->json([
        'firstName' => $user->firstName,
        'lastName' => $user->lastName,
        'email' => $user->email,
            'role' => $user->role,
            'id' => $user->id,
        'message' => 'User authenticated successfully'
    ]);
});

    Route::get('/diseases', CancerController::class . '@index');
    Route::post('/diseases', CancerController::class . '@store');
    Route::put('/diseases/{cancerId}/edit', CancerController::class . '@edit');
    Route::delete('/diseases/{cancerId}/delete', CancerController::class . '@destroy');

    Route::get('/products', ProductsController::class . '@index');
    Route::post('/products', ProductsController::class . '@store');
    Route::put('/products/{productId}/edit', ProductsController::class . '@edit');
    Route::delete('/products/{productId}/delete', ProductsController::class . '@destroy');
    Route::get('/products/types', ProductsController::class . '@productTypes');

Route::get('/locations', StateController::class . '@index');
Route::get('/contacts', AuthController::class . '@contacts');

    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', UsersController::class . '@index');
    Route::get('/supervisors', UsersController::class . '@supervisors');
    Route::get('/lgas', LgaController::class . '@index');
    Route::get('/staff-types', UsersController::class . '@staff_type');

    Route::get('/enrollees', EnrolleesController::class . '@index');
    Route::post('/enrollees', EnrolleesController::class . '@store');
    Route::get('/enrollees/types', EnrolleesController::class . '@enrolleeTypes');

});
