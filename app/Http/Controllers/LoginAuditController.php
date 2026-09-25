<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class LoginAuditController extends Controller
{
    /**
     * Halaman ini sekarang ikut sistem hak-akses per-menu (menu "log"):
     * superadmin selalu bisa (bypass, lihat User::menuLevel()), admin cuma
     * bisa kalau superadmin memberi akses lewat halaman Hak Akses, dan
     * mahasiswa/dosen tidak pernah punya opsi ini sama sekali. Middleware
     * route sudah menolak sebelum sampai sini; pengecekan ini cuma
     * jaring pengaman kedua untuk endpoint JSON yang dipanggil lewat fetch().
     */
    private function ensureLogAccess(Request $request): void
    {
        if (! $request->user()->canAccessMenu('log', 'readonly')) {
            abort(403, 'Kamu tidak punya akses ke Log Aktivitas.');
        }
    }

    /**
     * Kunjungan biasa (bukan AJAX) menampilkan shell halaman kosong; data
     * tabelnya sendiri diisi belakangan lewat fetch() ke data() supaya
     * filter (pencarian/status) bisa langsung jalan tanpa reload halaman.
     */
    public function index(Request $request)
    {
        $this->ensureLogAccess($request);

        return view('login-audit');
    }

    /**
     * Endpoint JSON yang dipanggil lewat fetch() oleh halaman login-audit,
     * dipanggil ulang tiap kali kolom pencarian/filter status berubah.
     */
    public function data(Request $request)
    {
        $this->ensureLogAccess($request);

        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $attempts = LoginAttempt::with('user:id,name,nim_nidn,email,role')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('username_input', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->latest('created_at')
            ->paginate(20, ['*'], 'page', $page);

        $last24h = LoginAttempt::where('created_at', '>=', now()->subDay());
        $summary = [
            'success'   => (clone $last24h)->where('status', 'success')->count(),
            'failed'    => (clone $last24h)->where('status', 'failed')->count(),
            'locked'    => (clone $last24h)->where('status', 'locked')->count(),
            'unique_ip' => (clone $last24h)->distinct('ip_address')->count('ip_address'),
        ];

        return response()->json([
            'summary' => $summary,
            'pagination' => [
                'current_page' => $attempts->currentPage(),
                'last_page'    => $attempts->lastPage(),
                'total'        => $attempts->total(),
                'per_page'     => $attempts->perPage(),
            ],
            'data' => $attempts->getCollection()->map(fn (LoginAttempt $a) => $this->transformRow($a)),
        ]);
    }

    /**
     * Detail satu percobaan login untuk ditampilkan di modal, dilengkapi
     * konteks tambahan: percobaan lain dari IP yang sama, dan percobaan
     * lain untuk akun/username yang sama — supaya superadmin bisa langsung
     * menilai apakah ini pola yang mencurigakan atau bukan, tanpa harus
     * cari manual satu-satu.
     */
    public function show(Request $request, LoginAttempt $attempt)
    {
        $this->ensureLogAccess($request);

        $attempt->load('user:id,name,nim_nidn,email,role,created_at');

        $fromSameIp = LoginAttempt::with('user:id,name,nim_nidn')
            ->where('ip_address', $attempt->ip_address)
            ->where('id', '!=', $attempt->id)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (LoginAttempt $a) => $this->transformRow($a));

        $sameUsername = LoginAttempt::with('user:id,name,nim_nidn')
            ->where('username_input', $attempt->username_input)
            ->where('id', '!=', $attempt->id)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (LoginAttempt $a) => $this->transformRow($a));

        // Sedikit konteks agregat dari IP ini sepanjang waktu, bukan cuma
        // 5 baris terakhir — supaya kelihatan misal "IP ini sudah 40x gagal
        // login" walau baris yang ditampilkan cuma sebagian.
        $ipStats = LoginAttempt::where('ip_address', $attempt->ip_address)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'attempt' => [
                ...$this->transformRow($attempt),
                'user_detail' => $attempt->user ? [
                    'id'          => $attempt->user->id,
                    'name'        => $attempt->user->name,
                    'identifier'  => $attempt->user->nim_nidn,
                    'email'       => $attempt->user->email,
                    'role'        => $attempt->user->role,
                    'akun_dibuat' => optional($attempt->user->created_at)->format('d M Y'),
                ] : null,
                'user_agent_full' => $attempt->user_agent,
            ],
            'ip_stats' => [
                'success' => (int) ($ipStats['success'] ?? 0),
                'failed'  => (int) ($ipStats['failed'] ?? 0),
                'locked'  => (int) ($ipStats['locked'] ?? 0),
            ],
            'from_same_ip'  => $fromSameIp,
            'same_username' => $sameUsername,
        ]);
    }

    private function transformRow(LoginAttempt $a): array
    {
        return [
            'id'             => $a->id,
            'created_at'     => $a->created_at->toIso8601String(),
            'created_at_fmt' => $a->created_at->format('d M Y, H:i:s'),
            'created_at_rel' => $a->created_at->diffForHumans(),
            'username_input' => $a->username_input,
            'user_name'      => $a->user->name ?? null,
            'user_role'      => $a->user->role ?? null,
            'ip_address'     => $a->ip_address,
            'status'         => $a->status,
            'user_agent'     => $a->user_agent,
        ];
    }
}
