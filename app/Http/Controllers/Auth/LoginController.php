<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        return view('auth.login'); // Pastikan file view ini ada di resources/views/auth/login.blade.php
    }

    // Proses login dengan error handling
    public function login(Request $request)
    {
        $this->checkLoginAttempts($request); // Cek batas percobaan login

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Mencegah session fixation
            RateLimiter::clear($this->throttleKey($request)); // Reset hitungan batas percobaan login
            return redirect()->route('dashboard'); // Arahkan ke dashboard setelah login
        }

        $this->incrementLoginAttempts($request); // Tambah hitungan percobaan login

        throw ValidationException::withMessages([
            'email' => 'Email or Password is wrong.',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Fungsi untuk membatasi percobaan login
    private function checkLoginAttempts(Request $request)
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.',
            ]);
        }
    }

    // Tambah hitungan percobaan login
    private function incrementLoginAttempts(Request $request)
    {
        RateLimiter::hit($this->throttleKey($request), 60); // Reset setelah 60 detik
    }

    // Kunci unik untuk membatasi percobaan login berdasarkan email dan IP
    private function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
