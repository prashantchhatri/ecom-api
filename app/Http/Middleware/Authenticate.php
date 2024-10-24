<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * Since we are developing a REST API, we don't need to redirect the user. Just throw an exception.
     */
    protected function redirectTo(Request $request): ?string
    {
        // This will let the exception handler handle the unauthenticated case
        return null;
    }
}
