<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, ProductController, CompanyController};

Route::get('/password-reset/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');

Route::post('/register-company', [AuthController::class, 'registerCompany']);
Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password-reset/request', [AuthController::class, 'requestPasswordReset']);
Route::post('/password-reset/reset', [AuthController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/companies', [CompanyController::class, 'index']);
});

// Seller-specific routes
Route::group(['middleware' => ['role:seller']], function () {
    Route::post('/products', [ProductController::class, 'store']);
});

// Buyer-specific routes
Route::group(['middleware' => ['role:buyer']], function () {
    Route::post('/cart', 'CartController@add');
});

// Company admin-specific routes
Route::group(['middleware' => ['role:company-admin']], function () {
    Route::post('/dashboard', 'DashboardController@index');
});
