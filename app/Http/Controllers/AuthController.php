<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\ActivityDescriber;
use App\Support\ActivityLog;
use App\Support\DefaultPassword;
use App\Support\SessionRevoker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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

    /**
     * Halaman login. Kalau pengunjung ini baru saja dikeluarkan paksa karena password akunnya
     * diubah/direset (lihat SessionRevoker), tampilkan pemberitahuan kenapa ia terpental.
     */
    public function showLogin(Request $request)
    {
        $prefill = $request->session()->pull('loginPrefill');

        return view('index', [
            'sessionNotice' => SessionRevoker::pullNotice($request->session()->getId()),
            'loginPrefill' => $prefill
                ? json_decode(Crypt::decryptString($prefill), true, flags: JSON_THROW_ON_ERROR)
                : null,
        ]);
    }

    public function loginProcess(Request $request)
    {
        // Lapis kedua di luar middleware 'guest': kalau karena alasan apa pun
        // request sampai ke sini dengan sesi yang sudah login, jangan
        // pernah menimpa sesi itu dengan akun lain.
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

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
        // Field "username" berisi username admin/superadmin atau NIM/NIDN.
        $identity = trim((string) $request->input('username'));
        $throttleKey = Str::lower($identity).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->logAttempt($request, null, 'locked');

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

        // Mahasiswa & dosen HANYA bisa login dengan NIM / NIDN (kolom nim_nidn) + password:
        // nama lengkap / username tidak lagi dikenali untuk dua role ini.
        // Admin & superadmin tetap boleh memakai nim_nidn-nya ATAU nama akunnya
        // (case-insensitive), karena identitas mereka bukan NIM/NIDN.
        $user = User::where('nim_nidn', $identity)->first()
            ?? User::whereIn('role', ['admin', 'superadmin'])
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($identity)])
                ->first();

        // Cari akun berdasarkan identitas yang diizinkan, lalu autentikasi
        // berdasarkan ID agar akun admin tanpa nim_nidn tetap bisa login.
        // Password tetap diverifikasi oleh provider Laravel.
        $authenticated = $user && Auth::attempt([
            'id' => $user->getAuthIdentifier(),
            'password' => $request->password,
        ]);

        if ($authenticated) {
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
                return redirect('/dashboard');
            } elseif ($role === 'admin' || $role === 'superadmin') {
                return redirect('/dashboard');
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
            'username' => 'Username/NIM/NIDN atau password salah.',
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

        $this->logActivity($request, $user, $status);
    }

    /**
     * Catat percobaan login ke log aktivitas (lihat ActivityLog). Teks yang
     * diketik di kolom username TIDAK ikut ditulis — orang kadang salah
     * mengetik password di kolom itu — hanya nama akun bila memang ditemukan.
     */
    private function logActivity(Request $request, ?User $user, string $status): void
    {
        $device = ActivityDescriber::clean((string) $request->userAgent(), 120);

        match ($status) {
            'success' => ActivityLog::record('login', 'berhasil login', ['perangkat' => $device], 'info', $user),
            'failed'  => ActivityLog::record(
                'login.failed',
                $user
                    ? 'gagal login ke akun ' . ActivityLog::who($user)
                    : 'gagal login (akun tidak ditemukan)',
                ['akun_id' => $user?->id, 'perangkat' => $device],
                'warning',
                null
            ),
            'locked'  => ActivityLog::record(
                'login.locked',
                'ditolak login sementara karena terlalu banyak percobaan gagal',
                ['perangkat' => $device],
                'warning',
                null
            ),
            default   => null,
        };
    }

    public function logout(Request $request)
    {
        // Dicatat SEBELUM logout: setelah itu user sudah tidak dikenali.
        ActivityLog::record('logout', 'logout', [], 'info', $request->user());

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

        ActivityLog::record(
            'password.reset_request',
            'meminta tautan reset password untuk akun ' . ActivityLog::who($user),
            [
                'akun_id'  => $user->id,
                'terkirim' => $status === Password::RESET_LINK_SENT ? 'ya' : 'tidak (permintaan terlalu sering)',
            ],
            'info',
            null
        );

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => $status === Password::RESET_LINK_SENT
                ? $generic
                : 'Permintaan reset baru saja dikirim untuk akun ini. Silakan cek email Anda atau coba lagi dalam beberapa saat.',
        ]);
    }

    /**
     * "Lupa password" -> kembalikan ke password default (NIM/NIDN).
     *
     * Khusus mahasiswa & dosen. Pengguna WAJIB mengonfirmasi dengan mengetik ulang NIM/NIDN-nya
     * (field `confirm_identity`) sebagai tanda memang berniat mereset. Setelah reset, akun
     * ditandai must_change_password sehingga pop-up wajib ganti password muncul saat login,
     * semua sesi lama dikeluarkan, dan pemilik akun dicatat di notifikasi + log aktivitas.
     *
     * Respons sengaja generik bila NIM/NIDN tidak ditemukan, agar tidak bisa dipakai menebak
     * daftar akun yang terdaftar.
     */
    public function resetToDefault(Request $request)
    {
        $data = $request->validate([
            'identity'         => ['required', 'string', 'max:255'],
            'confirm_identity' => ['required', 'string', 'max:255'],
        ], [
            'identity.required'         => 'NIM/NIDN wajib diisi.',
            'confirm_identity.required' => 'Ketik ulang NIM/NIDN untuk konfirmasi reset.',
        ]);

        $identity = trim($data['identity']);

        if (!hash_equals($identity, trim($data['confirm_identity']))) {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi NIM/NIDN tidak sama. Ketik ulang NIM/NIDN yang sama persis.',
            ], 422);
        }

        $generic = 'Jika NIM/NIDN tersebut terdaftar sebagai mahasiswa/dosen, password sudah dikembalikan ke default '
            .'(sama dengan NIM/NIDN). Silakan masuk, lalu buat password baru.';

        $user = User::where('nim_nidn', $identity)
            ->whereIn('role', DefaultPassword::ROLES)
            ->first();

        if (!$user) {
            return response()->json(['success' => true, 'message' => $generic]);
        }

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'password'             => Hash::make(DefaultPassword::forIdentifier((string) $user->nim_nidn)),
                'must_change_password' => true,
                'remember_token'       => Str::random(60),
            ])->save();

            SessionRevoker::revoke($user, SessionRevoker::PASSWORD_RESET);
        });

        ActivityLog::record(
            'password.reset_default',
            'mengembalikan password ke default (NIM/NIDN) lewat Lupa password',
            ['akun_id' => $user->id],
            'info',
            $user
        );

        try {
            UserNotification::send($user->id, 'password_changed', [
                'title'       => 'Password dikembalikan ke default',
                'description' => 'Password akun dikembalikan ke NIM/NIDN lewat menu Lupa password pada '
                    .now()->format('d/m/Y H:i').' dari IP '.$request->ip()
                    .'. Segera buat password baru. Kalau bukan Anda, hubungi Admin.',
                'data'        => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['success' => true, 'message' => $generic]);
    }

    /**
     * Tampilkan halaman untuk memasukkan password baru (dibuka dari tautan
     * di email).
     */
    public function showResetForm(Request $request, string $token)
    {
        $email = (string) $request->query('email', '');
        $user = $email !== '' ? User::where('email', $email)->first() : null;

        // Tautan dari email = konfirmasi "Ya, ini saya". Token diperiksa di server (tanpa dihabiskan);
        // kalau valid, pop-up pembuatan password langsung dibuka. Kalau tidak, kembali ke login.
        if (!$user || !Password::broker()->tokenExists($user, $token)) {
            return redirect()->route('login')->withErrors([
                'username' => 'Tautan reset password tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            ]);
        }

        return view('reset-password', [
            'token'     => $token,
            'email'     => $user->email,
            'userName'  => $user->name,
            'isDefault' => DefaultPassword::appliesTo($user->role),
        ]);
    }

    /**
     * Password acak (6 karakter) untuk pop-up reset. Hanya untuk pemegang token reset yang valid.
     */
    public function generateResetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Password::broker()->tokenExists($user, $data['token'])) {
            return response()->json(['message' => 'Tautan reset tidak valid atau sudah kedaluwarsa.'], 403);
        }

        return response()->json(['password' => \App\Support\RandomAccountPassword::generateUnique()]);
    }

    /**
     * Proses submit password baru dari halaman reset-password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'max:64', 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.max'       => 'Password maksimal 64 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama dengan password baru.',
        ]);

        $target = User::where('email', $request->input('email'))->first();
        if ($target && filled($target->nim_nidn) && hash_equals((string) $target->nim_nidn, (string) $request->input('password'))) {
            throw ValidationException::withMessages(['password' => 'Password tidak boleh sama dengan NIM/NIDN Anda.']);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    // Password dipilih sendiri lewat tautan reset -> bukan password default lagi.
                    'must_change_password' => false,
                    // Sekalian di-random ulang, supaya cookie "remember me"
                    // lama (kalau ada) juga ikut tidak berlaku lagi.
                    'remember_token' => Str::random(60),
                ])->save();

                // Paksa keluar semua sesi login yang sedang aktif untuk akun ini di
                // device/browser manapun (kalau akun sempat dipakai orang lain, sesinya
                // ikut terputus). Perangkat yang terpental melihat pemberitahuan di
                // halaman login — lihat SessionRevoker.
                SessionRevoker::revoke($user, SessionRevoker::PASSWORD_RESET);

                ActivityLog::record('password.reset', 'mengganti password lewat tautan reset', [], 'info', $user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $message = 'Password berhasil disimpan. Silakan masuk dengan password baru Anda.';
            if ($request->expectsJson()) {
                $request->session()->flash('status', $message);

                return response()->json(['success' => true, 'message' => $message, 'redirect' => route('login')]);
            }

            return redirect()->route('login')->with('status', $message);
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