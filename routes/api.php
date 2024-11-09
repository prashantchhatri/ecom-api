<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, ProductController, CompanyController};

// Password reset routes
Route::get('/password-reset/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');

Route::post('/register-company', [AuthController::class, 'registerCompany']);
Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password-reset/request', [AuthController::class, 'requestPasswordReset']);
Route::post('/password-reset/reset', [AuthController::class, 'resetPassword']);

// Protected routes that require authentication
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/companies', [CompanyController::class, 'index']);

    // Product-related routes accessible to authenticated users
    Route::get('/products', [ProductController::class, 'index']); // List all products
    Route::get('/products/{id}', [ProductController::class, 'show']); // View product details

    // Wishlist routes (requires 'buyer' role)
    Route::group(['middleware' => ['role:buyer']], function () {
        Route::post('/wishlist', [ProductController::class, 'addToWishlist']);
        Route::delete('/wishlist/{product_id}', [ProductController::class, 'removeFromWishlist']);
        Route::get('/wishlist', [ProductController::class, 'listWishlist']);
    });

    // Seller-specific routes
    Route::group(['middleware' => ['role:seller']], function () {
        Route::post('/products', [ProductController::class, 'store']); // Add new product
        Route::patch('/products/{id}/stock', [ProductController::class, 'updateStock']); // Update product stock
        Route::patch('/products/{id}/sponsored', [ProductController::class, 'toggleSponsoredStatus']); // Toggle sponsored status
        Route::patch('/products/{id}/assign-categories-tags', [ProductController::class, 'assignCategoriesAndTags']); // Assign categories and tags
    });

    // Company admin-specific routes
    Route::group(['middleware' => ['role:company-admin']], function () {
        Route::post('/dashboard', 'DashboardController@index');
        // Additional admin-specific routes
    });
});
