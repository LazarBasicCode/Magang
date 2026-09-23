<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Maksimal percobaan login gagal (per kombinasi username + IP) sebelum
     * akun tsb dikunci sementara.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lama penguncian dalam detik setelah percobaan gagal melebihi batas.
     */
    private const LOGIN_LOCKOUT_SECONDS = 60;

    public function loginProcess(Request $request)
    {
        // Validasi input dari index.blade.php
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Kunci throttle unik per kombinasi username + IP, supaya:
        // - satu akun yang diserang brute-force dari IP yang sama akan
        //   terkunci meski penyerang mengganti-ganti password;
        // - IP yang sama masih bisa login ke akun LAIN tanpa ikut terkunci
        //   gara-gara akun lain sedang diserang.
        $throttleKey = Str::lower($request->input('username')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

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
            // Login berhasil: hapus riwayat percobaan gagal untuk kombinasi ini
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            
            // Redirect berdasarkan role dari tabel users
            $role = Auth::user()->role;
            
            if ($role === 'mahasiswa') {
                return redirect('/dashboard');
            } elseif ($role === 'dosen') {
                return redirect('/lppm/dosen');
            } elseif ($role === 'admin' || $role === 'superadmin') {
                return redirect('/kemahasiswaan');
            }
            
            return redirect('/');
        }

        // Login gagal: catat percobaan ini. Setelah MAX_LOGIN_ATTEMPTS kali
        // gagal berturut-turut, kombinasi ini dikunci selama LOGIN_LOCKOUT_SECONDS.
        RateLimiter::hit($throttleKey, self::LOGIN_LOCKOUT_SECONDS);

        // Jika percobaan ini yang membuatnya melewati batas, langsung beri
        // tahu sisa waktu tunggu alih-alih pesan generik biasa.
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
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

    /**
     * Terima input dari form "Lupa password" (bisa berupa NIM, NIDN, nama
     * user, atau email) lalu kirim tautan reset ke email yang terdaftar
     * pada akun tsb.
     *
     * Responnya sengaja dibuat generik (pesan sukses yang sama) baik akun
     * ditemukan maupun tidak, supaya orang luar tidak bisa dipakai untuk
     * menebak-nebak NIM/NIDN mana saja yang terdaftar di sistem.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identity' => ['required', 'string', 'max:255'],
        ]);

        $identity = trim($request->input('identity'));

        $user = User::where('email', $identity)
            ->orWhere('nim_nidn', $identity)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($identity)])
            ->first();

        $generic = 'Jika akun dengan data tersebut terdaftar dan memiliki email, '
            .'tautan reset password sudah kami kirim ke email tersebut.';

        if (!$user || !$user->email) {
            // Tidak bocorkan apakah akunnya ada atau tidak — hanya beri tahu
            // kalau memang tidak ada email yang bisa dituju, supaya user tahu
            // harus menghubungi admin untuk reset manual.
            return response()->json([
                'success' => true,
                'message' => $user && !$user->email
                    ? 'Akun ini belum memiliki email terdaftar. Silakan hubungi Admin/Super Admin untuk mengatur ulang password Anda.'
                    : $generic,
            ]);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => $status === Password::RESET_LINK_SENT
                ? $generic
                : 'Permintaan reset baru saja dikirim untuk akun ini. Silakan cek email Anda atau coba lagi dalam beberapa saat.',
        ]);
    }

    /**
     * Tampilkan halaman untuk memasukkan password baru (dibuka dari tautan
     * di email).
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Proses submit password baru dari halaman reset-password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Password berhasil diperbarui. Silakan masuk dengan password baru Anda.');
        }

        throw ValidationException::withMessages([
            'email' => [$this->translateResetStatus($status)],
        ]);
    }

    private function translateResetStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Tautan reset password ini tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            Password::INVALID_USER  => 'Kami tidak menemukan akun dengan email tersebut.',
            default                  => 'Gagal mengatur ulang password. Silakan coba lagi.',
        };
    }
}