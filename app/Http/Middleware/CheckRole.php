<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Periksa apakah user memiliki role yang diizinkan.
    * Usage di route: middleware('role:admin') atau middleware('role:admin,curator')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Cek status akun
        if ($user->status_akun !== 'aktif') {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda tidak aktif atau diblokir. Hubungi administrator.']);
        }

        // Cek role
        if (!empty($roles) && !in_array($user->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
