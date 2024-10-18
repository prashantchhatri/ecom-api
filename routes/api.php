<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\{ScopeController, AccessTokenController, AuthorizationController, ApproveAuthorizationController, DenyAuthorizationController, TransientTokenController, ClientController, PersonalAccessTokenController, AuthorizedAccessTokenController};
use App\Http\Controllers\{AuthController, ProductController};



Route::prefix('oauth')->group(function () {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])->name('passport.token');
    Route::get('/authorize', [AuthorizationController::class, 'authorize'])->name('passport.authorizations.authorize');
    Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])->name('passport.authorizations.approve');
    Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])->name('passport.authorizations.deny');
    Route::get('/scopes', [ScopeController::class, 'all'])->name('passport.scopes.index');
    Route::get('/personal-access-tokens', [PersonalAccessTokenController::class, 'forUser'])->name('passport.personal.tokens.index');
    Route::post('/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('passport.personal.tokens.store');
    Route::delete('/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('passport.personal.tokens.destroy');
    Route::get('/clients', [ClientController::class, 'forUser'])->name('passport.clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('passport.clients.store');
    Route::put('/clients/{client_id}', [ClientController::class, 'update'])->name('passport.clients.update');
    Route::delete('/clients/{client_id}', [ClientController::class, 'destroy'])->name('passport.clients.destroy');
    Route::get('/tokens', [AuthorizedAccessTokenController::class, 'forUser'])->name('passport.tokens.index');
    Route::delete('/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy'])->name('passport.tokens.destroy');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/products', [ProductController::class, 'index']);

Route::post('/register-company', [AuthController::class, 'registerCompany']);
Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::post('/login', [AuthController::class, 'login']);

