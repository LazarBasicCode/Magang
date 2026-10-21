<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\NotificationPrefs;
use App\Support\SessionRevoker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    // =====================================================================
    // Halaman Profil: Kontak Cadangan, Riwayat Login, Preferensi Notifikasi
    // =====================================================================

    /** Email sekunder + nomor telepon cadangan (minimal salah satu). */
    public function updateContact(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->merge(['phone' => preg_replace('/[\s\-]/', '', (string) $request->input('phone'))]);

        $data = $request->validate([
            'secondary_email' => ['nullable', 'email:rfc', 'max:255'],
            'phone'           => ['nullable', 'regex:/^(\+62|62|0)8\d{7,12}$/'],
        ], [
            'secondary_email.email' => 'Format email tidak valid.',
            'phone.regex'           => 'Gunakan nomor seluler Indonesia, mis. 0812 3456 7890.',
        ]);

        $email = isset($data['secondary_email']) ? mb_strtolower(trim($data['secondary_email'])) : null;
        $phone = $data['phone'] ?? null;

        if (!$email && !$phone) {
            throw ValidationException::withMessages(['secondary_email' => 'Isi minimal salah satu: email atau nomor telepon.']);
        }
        if ($email && $user->email && $email === mb_strtolower($user->email)) {
            throw ValidationException::withMessages(['secondary_email' => 'Harus berbeda dari email utama.']);
        }
        if ($phone) {
            $phone = '0' . preg_replace('/^(\+62|62|0)/', '', $phone); // seragamkan ke 08xxxxxxxxxx
        }

        $user->forceFill(['secondary_email' => $email, 'phone' => $phone])->save();

        return response()->json([
            'message'         => 'Kontak cadangan berhasil disimpan.',
            'secondary_email' => $email,
            'phone'           => $phone,
        ]);
    }

    /** Perangkat yang sedang login + 10 aktivitas masuk terakhir. */
    public function loginHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $current = $request->session()->getId();

        $sessions = collect();
        if (config('session.driver') === 'database') {
            $sessions = DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->get()
                ->map(fn ($s) => [
                    'ref'     => $this->sessionRef($s->id),
                    'current' => $s->id === $current,
                    'device'  => $this->deviceLabel($s->user_agent),
                    'ip'      => $s->ip_address,
                    'last'    => Carbon::createFromTimestamp($s->last_activity)->translatedFormat('d M Y, H:i'),
                    'ago'     => Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                ])->values();
        }

        $attempts = LoginAttempt::where('user_id', $user->id)->latest('created_at')->limit(10)->get()
            ->map(fn ($a) => [
                'status' => $a->status,
                'device' => $this->deviceLabel($a->user_agent),
                'ip'     => $a->ip_address,
                'at'     => optional($a->created_at)->translatedFormat('d M Y, H:i'),
            ])->values();

        return response()->json(['sessions' => $sessions, 'attempts' => $attempts]);
    }

    /** Keluarkan satu perangkat lain (bukan sesi yang sedang dipakai). */
    public function revokeSession(Request $request, string $ref): JsonResponse
    {
        $user = $request->user();
        $current = $request->session()->getId();

        $target = DB::table('sessions')->where('user_id', $user->id)->get()
            ->first(fn ($s) => hash_equals($this->sessionRef($s->id), $ref));

        if (!$target || $target->id === $current) {
            return response()->json(['message' => 'Perangkat tidak ditemukan atau sedang Anda pakai.'], 404);
        }

        DB::table('sessions')->where('id', $target->id)->delete();

        return response()->json(['message' => 'Perangkat dikeluarkan.']);
    }

    /** Keluarkan semua perangkat lain; sesi ini tetap aktif. */
    public function revokeOtherSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = DB::table('sessions')->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())->delete();
        $user->forceFill(['remember_token' => Str::random(60)])->save(); // cookie "ingat saya" lama ikut hangus

        return response()->json(['message' => $count ? "{$count} perangkat lain dikeluarkan." : 'Tidak ada perangkat lain yang sedang login.', 'count' => $count]);
    }

    /** Simpan preferensi notifikasi (kategori yang bisa dimatikan saja). */
    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        $rules = collect(NotificationPrefs::CATEGORIES)->mapWithKeys(fn ($c, $key) => [$key => ['required', 'boolean']])->all();
        $data = $request->validate($rules);

        $prefs = collect($data)->map(fn ($v) => (bool) $v)->all();
        $request->user()->forceFill(['notification_prefs' => $prefs])->save();

        return response()->json(['message' => 'Preferensi notifikasi disimpan.', 'prefs' => $prefs]);
    }

    /** Pengenal sesi untuk klien: hash pendek, supaya id sesi asli tidak pernah dikirim ke browser. */
    private function sessionRef(string $sessionId): string
    {
        return substr(hash_hmac('sha256', $sessionId, (string) config('app.key')), 0, 20);
    }

    private function deviceLabel(?string $ua): string
    {
        $ua = (string) $ua;
        $browser = match (true) {
            str_contains($ua, 'Edg/')                              => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/')                          => 'Firefox',
            str_contains($ua, 'Chrome/')                           => 'Chrome',
            str_contains($ua, 'Safari/')                           => 'Safari',
            default                                                => 'Peramban',
        };
        $os = match (true) {
            str_contains($ua, 'Android')                                  => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')      => 'iOS',
            str_contains($ua, 'Windows')                                  => 'Windows',
            str_contains($ua, 'Mac OS')                                   => 'macOS',
            str_contains($ua, 'Linux')                                    => 'Linux',
            default                                                       => 'perangkat tak dikenal',
        };

        return "{$browser} di {$os}";
    }
}
