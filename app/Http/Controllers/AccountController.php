<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserNotification;
use App\Support\SessionRevoker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Pengaturan akun milik user yang sedang login (menu profil kanan atas):
 * ganti password & email pemulihan. Dipakai lewat fetch() dari
 * public/js/profile-account.js, jadi semua respons berupa JSON.
 */
class AccountController extends Controller
{
    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
            'password.different'        => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Password lama salah.',
                'errors'  => ['current_password' => ['Password lama salah.']],
            ], 422);
        }

        // Ganti password + keluarkan SEMUA sesi akun ini (semua perangkat, termasuk yang sedang dipakai),
        // supaya orang lain yang sempat masuk tidak bisa terus memakai akun. Perangkat lain melihat
        // pemberitahuan di halaman login (SessionRevoker); perangkat ini diarahkan ke login oleh JS.
        DB::transaction(function () use ($user, $data) {
            $user->forceFill(['password' => Hash::make($data['password'])])->save();
            SessionRevoker::revoke($user, SessionRevoker::PASSWORD_CHANGED);
        });

        // Catatan keamanan di daftar notifikasi: terlihat setelah login ulang. Pelengkap saja.
        try {
            UserNotification::send($user->id, 'password_changed', [
                'title'       => 'Password akun diganti',
                'description' => 'Password diganti pada ' . now()->format('d/m/Y H:i') . ' dari IP ' . $request->ip()
                    . '. Semua perangkat dikeluarkan. Kalau bukan Anda, segera hubungi Admin.',
                'data'        => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        // Sesi ini juga diakhiri (baris sesinya sudah dihapus di atas; tanpa ini sesi akan tersimpan ulang di akhir request).
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('status', 'Password berhasil diganti. Semua perangkat dikeluarkan — silakan masuk dengan password baru.');

        return response()->json([
            'message'  => 'Password berhasil diganti. Semua perangkat dikeluarkan, silakan masuk lagi.',
            'logout'   => true,
            'redirect' => route('login'),
        ]);
    }

    public function updateRecoveryEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => [
                'required', 'string', 'email:rfc', 'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email baru wajib diisi.',
            'email.email'    => 'Format email tidak valid (contoh: nama@domain.com).',
            'email.unique'   => 'Email ini sudah dipakai akun lain.',
            'password.required' => 'Password wajib diisi untuk konfirmasi.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Password salah.',
                'errors'  => ['password' => ['Password salah.']],
            ], 422);
        }

        $user->forceFill(['email' => strtolower($data['email'])])->save();

        return response()->json(['message' => 'Email pemulihan berhasil diperbarui.']);
    }
}
