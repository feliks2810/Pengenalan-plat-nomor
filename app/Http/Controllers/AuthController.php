<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function handleLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/dashboard');
        }
        return back()->withErrors(['email' => 'Login gagal, periksa email dan password.']);
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function handleRegister(Request $request)
    {
        // Logika registrasi sederhana (tambahkan validasi)
        // Contoh: User::create([...]);
        return redirect('/login')->with('success', 'Registrasi berhasil, silakan login.');
    }
}