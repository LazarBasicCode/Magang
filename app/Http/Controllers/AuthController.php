<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            $this->logAttempt($request, null, 'locked');

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

            $this->logAttempt($request, Auth::user(), 'success');

            // Cek dulu SEBELUM session di-regenerate: kalau akun ini sudah
            // punya sesi aktif lain (di device/browser berbeda), berarti ada
            // login "kedua" yang terjadi sementara sesi pertama masih hidup.
            // Beri tahu pemiliknya lewat notifikasi — supaya kalau itu bukan
            // dia sendiri, dia langsung sadar dan bisa reset password.
            $this->notifyIfConcurrentLogin($request, Auth::user());

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

        $this->logAttempt($request, $user, 'failed');

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

    /**
     * Simpan satu baris jejak audit percobaan login. Dibungkus try/catch
     * supaya kalau tabel audit ini bermasalah (mis. migration belum
     * dijalankan), proses login utama tetap jalan seperti biasa — audit
     * log itu pelengkap, bukan syarat login berhasil/gagal.
     */
    /**
     * Deteksi apakah akun ini sudah punya sesi aktif lain saat login ini
     * terjadi. Kalau iya, kirim notifikasi ke pemilik akun berisi info
     * kapan & dari IP mana login "tambahan" ini terjadi — baik sesi lama
     * maupun sesi baru sama-sama akan melihat notifikasi ini karena
     * keduanya menuju akun yang sama.
     */
    private function notifyIfConcurrentLogin(Request $request, User $user): void
    {
        try {
            $hasOtherActiveSession = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->exists();

            if (!$hasOtherActiveSession) {
                return;
            }

            UserNotification::send($user->id, 'concurrent_login', [
                'title'       => 'Login baru terdeteksi',
                'description' => 'Akun Anda baru saja login dari perangkat/IP lain ('.$request->ip()
                    .') sementara sesi sebelumnya masih aktif. Kalau ini bukan Anda, segera ganti password.',
                'data' => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function logAttempt(Request $request, ?User $user, string $status): void
    {
        try {
            LoginAttempt::create([
                'user_id'         => $user?->id,
                'username_input'  => (string) $request->input('username'),
                'ip_address'      => (string) $request->ip(),
                'user_agent'      => Str::limit((string) $request->userAgent(), 255, ''),
                'status'          => $status,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
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
                    'password'       => Hash::make($password),
                    // Sekalian di-random ulang, supaya cookie "remember me"
                    // lama (kalau ada) juga ikut tidak berlaku lagi.
                    'remember_token' => Str::random(60),
                ])->save();

                // Paksa keluar semua sesi login yang sedang aktif untuk akun
                // ini di device/browser manapun. Tanpa ini, kalau akun sempat
                // dipakai orang lain sebelum passwordnya diganti, sesi orang
                // itu akan tetap jalan terus walau passwordnya sudah beda —
                // reset password baru menutup pintu masuk yang BARU, bukan
                // mengusir yang sudah kepalang berada di dalam.
                DB::table('sessions')->where('user_id', $user->id)->delete();
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