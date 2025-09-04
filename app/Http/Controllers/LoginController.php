<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;

class LoginController extends Controller
{
    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user can login (approved and active)
            if (!$user->canLogin()) {
                Auth::logout();
                
                if (!$user->isApproved()) {
                    $message = 'Akun Anda belum disetujui oleh Super Admin. Silakan tunggu persetujuan.';
                    if ($user->isRejected()) {
                        $message = 'Akun Anda telah ditolak oleh Super Admin. Hubungi administrator untuk informasi lebih lanjut.';
                    }
                } else {
                    $message = 'Akun Anda sedang dinonaktifkan. Hubungi administrator untuk informasi lebih lanjut.';
                }
                
                return back()->withErrors([
                    'email' => $message,
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
