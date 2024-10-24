<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\UnauthorizedHttpException;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Customize the unauthenticated response
        $this->renderable(function (AuthenticationException $e, $request) {
            // Only respond with a JSON response if the request expects it
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated, Please login'
                ], 401);
            }

            // For non-API requests, you could redirect if you want
            return null; 
        });
    }
}
