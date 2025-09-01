<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KasirMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = auth()->user();

        // Check if user can login (approved and active)
        if (!$user->canLogin()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif atau belum disetujui.');
        }

        // Check if user is kasir, admin, or superadmin
        if (!in_array($user->role, ['kasir', 'admin', 'superadmin'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
