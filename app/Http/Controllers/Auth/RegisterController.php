<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    // Menampilkan halaman register
    public function showRegistrationForm()
    {
        return view('auth.register'); // Pastikan file ini ada di resources/views/auth/register.blade.php
    }

    // Menangani proses registrasi
    public function register(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            // Buat user baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user); // Langsung login setelah registrasi
            return redirect()->route('dashboard')->with('success', 'Registrasi berhasil, selamat datang!');

        } catch (ValidationException $e) {
            // Jika validasi gagal, kembali ke halaman register dengan pesan error
            return back()->withErrors($e->validator->errors())->withInput();
        } catch (\Exception $e) {
            // Jika terjadi error lain, kembali dengan pesan error umum
            return back()->with('error', 'Terjadi kesalahan saat registrasi, coba lagi.')->withInput();
        }
    }
}
