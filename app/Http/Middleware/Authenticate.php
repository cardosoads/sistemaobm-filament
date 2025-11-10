<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Redirect unauthenticated users to the Filament login entry point.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson()
            ? null
            : '/admin';
    }
}
