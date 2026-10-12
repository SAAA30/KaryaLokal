<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Update last_login_at sekali per sesi (cek session key)
            if (!session('last_login_updated')) {
                $user->timestamps = false;
                $user->last_login_at = now();
                $user->save();
                $user->timestamps = true;
                session(['last_login_updated' => true]);
            }
        }

        return $next($request);
    }
}
