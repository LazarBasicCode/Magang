<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginProcess(Request $request)
    {
        // Validasi input dari index.blade.php
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Cari user berdasarkan nama ATAU nim_nidn (case-insensitive untuk nama)
        $user = User::where('nim_nidn', $request->username)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($request->username)])
            ->first();

        // Login pakai kolom unik user tsb (nim_nidn) + password yang diinput,
        // supaya proses verifikasi password tetap lewat Auth::attempt (hashing aman)
        $credentials = [
            'nim_nidn' => $user->nim_nidn ?? $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Redirect berdasarkan role dari tabel users
            $role = Auth::user()->role;
            
            if ($role === 'mahasiswa') {
                return redirect('/lppm/mahasiswa');
            } elseif ($role === 'dosen') {
                return redirect('/lppm/dosen');
            } elseif ($role === 'admin' || $role === 'superadmin') {
                return redirect('/kemahasiswaan');
            }
            
            return redirect('/');
        }

        // Jika gagal, kembalikan ke halaman index dengan pesan error
        return back()->withErrors([
            'username' => 'User, NIM, atau Password tidak ditemukan.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}