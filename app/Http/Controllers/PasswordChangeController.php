<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\PasswordChangeConfirmationNotification;
use App\Support\ActivityLog;
use App\Support\DefaultPassword;
use App\Support\PasswordChangeVerification;
use App\Support\SessionRevoker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Ganti password dari menu profil: butuh persetujuan lewat email (atau kembali ke password default).
 * Antarmuka: public/js/password-change.js. Penyimpanan password barunya tetap di AccountController::changePassword,
 * yang hanya menerima bila sesi ini sudah terverifikasi (PasswordChangeVerification).
 */
class PasswordChangeController extends Controller
{
    /** POST /account/password/verify — kirim email konfirmasi "Ya, ini saya". */
    public function request(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! filled($user->email)) {
            return response()->json([
                'message'     => 'Akun ini belum punya email pemulihan. Tambahkan lewat menu "Email Pemulihan" terlebih dahulu.',
                'needs_email' => true,
            ], 422);
        }

        $wait = PasswordChangeVerification::secondsUntilResend($user);
        if ($wait > 0) {
            return response()->json([
                'message'     => "Email baru saja dikirim. Kirim ulang dalam {$wait} detik.",
                'retry_after' => $wait,
            ], 429);
        }

        $token = PasswordChangeVerification::start($user);
        $url = URL::temporarySignedRoute(
            'account.password.confirm',
            now()->addMinutes(PasswordChangeVerification::LINK_MINUTES),
            ['user' => $user->id, 'token' => $token]
        );

        try {
            Notification::route('mail', $user->email)->notify(
                new PasswordChangeConfirmationNotification($user->name, $url, PasswordChangeVerification::LINK_MINUTES)
            );
        } catch (\Throwable $e) {
            report($e);
            PasswordChangeVerification::forget($user);

            return response()->json([
                'message' => 'Email konfirmasi gagal dikirim. Periksa pengaturan email server, lalu coba lagi.',
            ], 502);
        }

        ActivityLog::record(
            'password.change_request',
            'meminta konfirmasi email untuk mengganti password',
            ['email' => PasswordChangeVerification::maskEmail($user->email)]
        );

        return response()->json([
            'success'      => true,
            'email'        => PasswordChangeVerification::maskEmail($user->email),
            'expires_in'   => PasswordChangeVerification::LINK_MINUTES * 60,
            'resend_after' => PasswordChangeVerification::RESEND_SECONDS,
        ]);
    }

    /** GET /account/password/verify/status — dipanggil berkala oleh pop-up "menunggu konfirmasi". */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status'     => PasswordChangeVerification::status($user),
            'resend_in'  => PasswordChangeVerification::secondsUntilResend($user),
            'expires_in' => PasswordChangeVerification::secondsUntilExpiry($user),
            'email'      => PasswordChangeVerification::maskEmail($user->email),
        ]);
    }

    /**
     * GET /account/password/confirm/{user}/{token} — tautan di email (bertanda tangan). Tanpa login: emailnya bisa
     * dibuka di HP sementara SIDA terbuka di komputer.
     */
    public function confirm(Request $request, User $user, string $token)
    {
        $ok = PasswordChangeVerification::confirm($user, $token);

        if ($ok) {
            ActivityLog::record(
                'password.change_confirm',
                'mengonfirmasi lewat email untuk mengganti password',
                [],
                'info',
                $user
            );
        }

        return response()->view('password-change-confirmed', ['ok' => $ok, 'name' => $user->name], $ok ? 200 : 410);
    }

    /**
     * POST /account/password/default — kembalikan password sendiri ke default (NIM/NIDN), khusus mahasiswa & dosen.
     * Wajib mengetik ulang NIM/NIDN. Semua sesi dikeluarkan; saat masuk berikutnya pop-up wajib ganti password muncul.
     */
    public function resetToDefault(Request $request): JsonResponse
    {
        $data = $request->validate([
            'confirm_identity' => ['required', 'string', 'max:255'],
        ], [
            'confirm_identity.required' => 'Ketik ulang NIM/NIDN untuk konfirmasi.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! DefaultPassword::appliesTo($user->role) || ! filled($user->nim_nidn)) {
            return response()->json([
                'message' => 'Kembalikan ke default hanya untuk akun Mahasiswa dan Dosen.',
            ], 422);
        }

        $label = $user->role === 'dosen' ? 'NIDN' : 'NIM';
        if (! hash_equals(trim((string) $user->nim_nidn), trim($data['confirm_identity']))) {
            $message = "{$label} tidak sama. Ketik {$label} akun Anda dengan benar.";

            return response()->json([
                'message' => $message,
                'errors'  => ['confirm_identity' => [$message]],
            ], 422);
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
            'mengembalikan password ke default (' . $label . ') dari menu Ganti Password',
            ['akun_id' => $user->id],
            'info',
            $user
        );

        try {
            UserNotification::send($user->id, 'password_changed', [
                'title'       => 'Password dikembalikan ke default',
                'description' => 'Password akun dikembalikan ke ' . $label . ' pada ' . now()->format('d/m/Y H:i')
                    . ' dari IP ' . $request->ip() . '. Segera buat password baru. Kalau bukan Anda, hubungi Admin.',
                'data'        => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        $message = "Password dikembalikan ke {$label}. Silakan masuk, lalu buat password baru.";

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('status', $message);

        return response()->json([
            'message'  => $message,
            'logout'   => true,
            'redirect' => route('login'),
        ]);
    }
}
