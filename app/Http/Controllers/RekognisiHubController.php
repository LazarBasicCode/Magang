<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\LppmDosen;
use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Rekognisi;
use App\Models\User;
use App\Support\RekognisiMap as Map;
use App\Support\RekognisiPayload;
use App\Support\RekognisiUnion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Halaman REKOGNISI terpadu: publikasi mahasiswa + publikasi dosen + rekognisi mitra/alumni dalam SATU
 * tabel, SATU filter, dan modal tambah yang berubah mengikuti tipe user.
 *
 * Data tetap di tiga tabel lama (lppm_mahasiswa, lppm_dosen, rekognisi). Daftar dibaca lewat query gabungan
 * (App\Support\RekognisiUnion); tambah / ubah / hapus DITERUSKAN ke controller lama sehingga alur persetujuan,
 * notifikasi, dan pembatasan "hanya data sendiri" tetap sama persis. Hak akses dicek per tabel asal:
 *   mhs -> menu lppm_mahasiswa · dsn -> menu lppm_dosen · rek -> menu rekognisi
 *
 *   GET    /lppm/rekognisi              halaman (HTML) / daftar (JSON)
 *   GET    /lppm/rekognisi/item/{key}   satu data lengkap untuk modal ubah   (key: mhs-12 | dsn-7 | rek-3)
 *   POST   /lppm/rekognisi              tambah
 *   PUT    /lppm/rekognisi/{key}        ubah
 *   DELETE /lppm/rekognisi/{key}        hapus
 */
class RekognisiHubController extends Controller
{
    private const MENU = ['mhs' => 'lppm_mahasiswa', 'dsn' => 'lppm_dosen', 'rek' => 'rekognisi'];

    /** @var array<string, string> tabel asal -> kelas model */
    private const MODEL = ['mhs' => LppmMahasiswa::class, 'dsn' => LppmDosen::class, 'rek' => Rekognisi::class];

    // ------------------------------------------------------------------ daftar

    public function index(Request $request)
    {
        $user = $request->user();
        $access = $this->access($user);
        abort_unless(in_array(true, array_map(fn ($a) => $a !== 'none', $access), true), 403, 'Tidak punya akses ke halaman ini.');

        $scope = ['access' => $access, 'viewer_id' => $user->id];
        $filters = $scope + [
            'tipe_user' => $request->query('tipe_user'),
            'jenis'     => $request->query('jenis'),
            'skala'     => $request->query('skala'),
            'tahun'     => $request->query('tahun'),
            'q'         => $request->query('q'),
        ];

        [$cSql, $cB] = RekognisiUnion::countQuery($filters);
        $total = (int) (DB::selectOne($cSql, $cB)->n ?? 0);
        $last = max(1, (int) ceil($total / RekognisiUnion::PER_PAGE));
        $page = min(max(1, (int) $request->query('page', 1)), $last);   // halaman di luar jangkauan -> mundur ke terakhir

        [$sql, $b] = RekognisiUnion::listQuery($filters, $page);
        $rows = collect(DB::select($sql, $b))->map(fn ($r) => $this->present($r))->values();

        $meta = [
            'page'  => $page,
            'last'  => $last,
            'total' => $total,
            'from'  => $total ? ($page - 1) * RekognisiUnion::PER_PAGE + 1 : 0,
            'to'    => min($page * RekognisiUnion::PER_PAGE, $total),
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['items' => $rows, 'meta' => $meta, 'stats' => $this->stats($scope)]);
        }

        $initial = [
            'tipe_user' => isset(Map::TIPE_USER[(string) $request->query('tipe_user')]) ? $request->query('tipe_user') : null,
            'jenis'     => isset(Map::JENIS[(string) $request->query('jenis')]) ? $request->query('jenis') : null,
            'skala'     => isset(Map::SKALA[(string) $request->query('skala')]) ? $request->query('skala') : null,
            'tahun'     => ctype_digit((string) $request->query('tahun')) ? (int) $request->query('tahun') : null,
            'q'         => trim((string) $request->query('q', '')) ?: null,
        ];

        return view('lppm_rekognisi', [
            'initial' => $initial,
            'items'  => $rows,
            'meta'   => $meta,
            'stats'  => $this->stats($scope),
            'years'  => $this->years($scope),
            'people' => $this->people($user),
            'can'    => $this->permissions($user),
            'cfg'    => $this->clientConfig(),
        ]);
    }

    /** Satu data lengkap (untuk mengisi modal ubah). */
    public function show(Request $request, string $key): JsonResponse
    {
        [$table, $model] = $this->resolve($key);
        $this->authorizeRead($request->user(), $table, $model);

        return response()->json(['data' => $this->form($table, $model)]);
    }

    // ------------------------------------------------------------------ tambah / ubah / hapus

    public function store(Request $request): JsonResponse
    {
        $in = $request->validate([
            'tipe_user' => ['required', 'in:mahasiswa,dosen,alumni'],
            'jenis'     => ['required', 'in:jurnal,conference,hki,buku,rekognisi'],
            'skala'     => ['nullable', 'in:nasional,internasional'],
            'user_id'   => ['required', 'exists:users,id'],
        ]);

        $target = Map::target($in['tipe_user'], $in['jenis'], $in['skala'] ?? null);
        if ($target === null) {
            return $this->invalid('Kombinasi Tipe User, Jenis, dan Skala tidak valid.');
        }
        [$table] = $target;
        $this->authorizeWrite($request->user(), $table);

        $owner = User::with(['mahasiswa', 'dosen'])->findOrFail($in['user_id']);
        $ids = $this->ownerIds($in['tipe_user'], $owner);
        if ($ids === null) {
            return $this->invalid('Nama yang dipilih tidak sesuai dengan Tipe User.');
        }

        $request->merge(RekognisiPayload::toLegacy($target, $request->all() + ['user_id' => $owner->id], $ids));
        $legacy = $this->legacy($table)->store($request);

        return $this->respond($legacy, $table);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        [$table, $model] = $this->resolve($key);
        $this->authorizeWrite($request->user(), $table);

        $in = $request->validate([
            'tipe_user' => ['required', 'in:mahasiswa,dosen,alumni'],
            'jenis'     => ['required', 'in:jurnal,conference,hki,buku,rekognisi'],
            'skala'     => ['nullable', 'in:nasional,internasional'],
        ]);

        $target = Map::target($in['tipe_user'], $in['jenis'], $in['skala'] ?? null);
        // Pemilik & tabel tidak boleh berpindah saat ubah (mis. jurnal -> rekognisi): hapus lalu buat baru.
        if ($target === null || $target[0] !== $table) {
            return $this->invalid('Jenis ini tidak bisa diubah ke tipe lain. Hapus data lalu tambahkan yang baru.');
        }

        $userId = $this->ownerUserId($table, $model);
        $owner = User::with(['mahasiswa', 'dosen'])->findOrFail($userId);
        $ids = $this->ownerIds($table === 'rek' ? ($model->jenis === 'alumni' ? 'alumni' : $model->tipe_user) : $in['tipe_user'], $owner) ?? [];
        $ids['user_role'] = $owner->role;

        $request->merge(RekognisiPayload::toLegacy($target, $request->all() + ['user_id' => $owner->id], $ids));
        $legacy = $this->legacy($table)->update($request, $model);

        return $this->respond($legacy, $table);
    }

    public function destroy(Request $request, string $key): JsonResponse
    {
        [$table, $model] = $this->resolve($key);
        $this->authorizeWrite($request->user(), $table);

        $legacy = $this->legacy($table)->destroy($request, $model);
        $data = $legacy->getData(true);

        return response()->json(['success' => true, 'key' => $key, 'id' => $data['id'] ?? $model->id]);
    }

    // ------------------------------------------------------------------ akses

    /** Level akses tampilan per tabel asal: all | own | none. */
    private function access(User $user): array
    {
        $out = [];
        foreach (self::MENU as $src => $menu) {
            $out[$src] = match ($user->menuLevel($menu)) {
                'penuh', 'readonly' => 'all',
                'biasa'             => 'own',
                default             => 'none',
            };
        }

        return $out;
    }

    private function canWrite(User $user, string $table): bool
    {
        return in_array($user->menuLevel(self::MENU[$table]), ['biasa', 'penuh'], true);
    }

    private function authorizeWrite(User $user, string $table): void
    {
        abort_unless($this->canWrite($user, $table), 403, 'Tidak punya akses untuk mengubah data ini.');
    }

    private function authorizeRead(User $user, string $table, $model): void
    {
        $level = $this->access($user)[$table];
        abort_if($level === 'none', 403, 'Tidak punya akses ke data ini.');
        abort_if($level === 'own' && $this->ownerUserId($table, $model) !== $user->id, 403, 'Kamu hanya bisa membuka data milikmu sendiri.');
    }

    /** Matriks izin tambah data untuk dropdown di modal: [tipe_user][jenis] => bool. */
    private function permissions(User $user): array
    {
        $out = [];
        foreach (Map::JENIS_BY_TIPE as $tipe => $jenisList) {
            foreach ($jenisList as $jenis) {
                $skala = in_array($jenis, Map::JENIS_BERSKALA, true) ? 'nasional' : null;
                $t = Map::target($tipe, $jenis, $skala);
                $out[$tipe][$jenis] = $t !== null && $this->canWrite($user, $t[0]);
            }
        }

        return $out;
    }

    // ------------------------------------------------------------------ data pendukung

    /** Daftar nama untuk dropdown Nama di modal, dikelompokkan per tipe user. */
    private function people(User $user): array
    {
        $ownOnly = fn (string $menu) => $user->menuLevel($menu) === 'biasa';
        $pack = fn ($u) => ['id' => $u->id, 'nama' => $u->name, 'nim_nidn' => $u->nim_nidn];

        $mhs = User::where('role', 'mahasiswa')->with('mahasiswa')->orderBy('name')->get();
        $dsn = User::where('role', 'dosen')->orderBy('name')->get();

        $list = [
            'mahasiswa' => $mhs->when($ownOnly('lppm_mahasiswa'), fn ($c) => $c->where('id', $user->id))->map($pack)->values(),
            'dosen'     => $dsn->when($ownOnly('lppm_dosen'), fn ($c) => $c->where('id', $user->id))->map($pack)->values(),
            // Alumni = mahasiswa berstatus Lulus
            'alumni'    => $mhs->filter(fn ($u) => optional($u->mahasiswa)->status === 'lulus')
                ->when($ownOnly('rekognisi'), fn ($c) => $c->where('id', $user->id))->map($pack)->values(),
        ];

        return $list;
    }

    /** Kartu statistik: total + per tipe user. */
    private function stats(array $scope): array
    {
        [$sql, $b] = RekognisiUnion::statsQuery($scope);
        $s = ['total' => 0, 'mahasiswa' => 0, 'dosen' => 0, 'alumni' => 0];
        foreach (DB::select($sql, $b) as $r) {
            $s['total'] += (int) $r->n;
            $s[$r->tipe_user] = ($s[$r->tipe_user] ?? 0) + (int) $r->n;
        }

        return $s;
    }

    private function years(array $scope): array
    {
        [$sql, $b] = RekognisiUnion::yearsQuery($scope);

        return array_map(fn ($r) => (int) $r->tahun, DB::select($sql, $b));
    }

    /** Konfigurasi opsi untuk JS modal (satu sumber kebenaran di RekognisiMap). */
    private function clientConfig(): array
    {
        return [
            'tipe'            => Map::TIPE_USER,
            'jenis'           => Map::JENIS,
            'skala'           => Map::SKALA,
            'jenisByTipe'     => Map::JENIS_BY_TIPE,
            'jenisBerskala'   => Map::JENIS_BERSKALA,
            'skalaTetap'      => Map::SKALA_TETAP,
            'labelPeringkat'  => Map::LABEL_PERINGKAT,
        ];
    }

    // ------------------------------------------------------------------ util

    /** "dsn-7" -> [tabel, model]. 404 kalau kunci/ID tidak ada. */
    private function resolve(string $key): array
    {
        $p = Map::parseRowKey($key);
        abort_if($p === null, 404);
        $class = self::MODEL[$p[0]];

        return [$p[0], $class::findOrFail($p[1])];
    }

    private function ownerUserId(string $table, $model): int
    {
        return (int) match ($table) {
            'mhs'   => Mahasiswa::whereKey($model->mahasiswa_id)->value('user_id'),
            'dsn'   => Dosen::whereKey($model->dosen_id)->value('user_id'),
            default => $model->user_id,
        };
    }

    /** id profil pemilik sesuai tipe user; null = akun tidak cocok dengan tipe itu. */
    private function ownerIds(string $tipe, User $owner): ?array
    {
        return match ($tipe) {
            'mahasiswa' => $owner->mahasiswa ? ['mahasiswa_id' => $owner->mahasiswa->id, 'user_role' => $owner->role] : null,
            'dosen'     => $owner->dosen ? ['dosen_id' => $owner->dosen->id, 'user_role' => $owner->role] : null,
            'alumni'    => $owner->role === 'mahasiswa' ? ['user_role' => $owner->role] : null,
            default     => null,
        };
    }

    private function legacy(string $table)
    {
        return app(match ($table) {
            'mhs'   => LppmMahasiswaController::class,
            'dsn'   => LppmDosenController::class,
            default => LppmRekognisiController::class,
        });
    }

    /** Ubah balasan controller lama ({success, data:{id}}) menjadi baris terpadu. */
    private function respond($legacy, string $table): JsonResponse
    {
        $id = (int) (($legacy->getData(true)['data']['id'] ?? 0));
        [$sql, $b] = RekognisiUnion::oneQuery($table, $id);
        $row = DB::selectOne($sql, $b);

        return response()->json(['success' => true, 'data' => $row ? $this->present($row) : null]);
    }

    private function invalid(string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => ['jenis' => [$message]]], 422);
    }

    /** Baris hasil query gabungan -> bentuk untuk JS. */
    private function present(object $r): array
    {
        return [
            'key'             => Map::rowKey($r->source, $r->id),
            'source'          => $r->source,
            'id'              => (int) $r->id,
            'user_id'         => (int) $r->user_id,
            'nama'            => $r->nama,
            'nim_nidn'        => $r->nim_nidn,
            'tipe_user'       => $r->tipe_user,
            'jenis'           => $r->jenis,
            'skala'           => $r->skala,
            'label'           => Map::label($r->jenis, $r->skala),
            'judul'           => $r->judul,
            'penulis'         => $r->penulis,
            'detail'          => $r->detail,
            'tahun'           => $r->tahun ? (int) $r->tahun : null,
            'bukti'           => $r->bukti,
            'approval_status' => $r->approval_status,
            'approval_note'   => $r->approval_note,
            'approval_label'  => \App\Support\Approval::STATUS[$r->approval_status]['label'] ?? ucfirst((string) $r->approval_status),
            'foto'            => $r->foto,
        ];
    }

    /** Isian lengkap untuk modal ubah. */
    private function form(string $table, $model): array
    {
        return RekognisiPayload::fromLegacy($table, $model->getAttributes(), $this->ownerUserId($table, $model));
    }
}
