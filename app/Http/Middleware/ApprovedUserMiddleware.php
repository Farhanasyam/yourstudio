<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedUserMiddleware
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
            
            if (!$user->isApproved()) {
                $message = 'Akun Anda belum disetujui oleh Super Admin. Silakan tunggu persetujuan.';
                if ($user->isRejected()) {
                    $message = 'Akun Anda telah ditolak oleh Super Admin. Hubungi administrator untuk informasi lebih lanjut.';
                }
            } else {
                $message = 'Akun Anda sedang dinonaktifkan. Hubungi administrator untuk informasi lebih lanjut.';
            }
            
            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
