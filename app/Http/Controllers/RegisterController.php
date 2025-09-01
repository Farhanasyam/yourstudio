<?php

namespace App\Http\Controllers;

// use App\Http\Requests\RegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'name' => 'required|max:255|min:2',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|max:255',
            'role' => 'required|in:admin,kasir',
            'terms' => 'required'
        ]);
        
        // Hash password
        $attributes['password'] = bcrypt($attributes['password']);
        
        // Set default approval status
        $attributes['approval_status'] = 'pending';
        $attributes['is_active'] = true;
        
        $user = User::create($attributes);
        
        // Don't auto login, redirect to pending approval page
        return redirect('/login')->with('success', 
            'Registrasi berhasil! Akun Anda sedang menunggu persetujuan dari Super Admin. Anda akan dapat login setelah akun disetujui.'
        );
    }
}
