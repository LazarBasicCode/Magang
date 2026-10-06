<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use App\Models\KerjaSama;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KerjaSamaController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private const RULES = [
        'user_id'          => ['required', 'exists:users,id'],
        'tipe_user'        => ['required', 'in:mahasiswa,dosen'],
        'jenis'            => ['required', 'in:conference_internasional,pkl,sharing_session,keynote_session,guest_lecture,pengabdian_internasional,research_internasional,lainnya'],
        'jenis_lainnya'    => ['nullable', 'required_if:jenis,lainnya', 'string', 'max:255'],
        'arah'             => ['nullable', 'required_if:jenis,guest_lecture', 'in:inbound,outbound'],
        'mitra'            => ['required', 'string', 'max:255'],
        'judul_kegiatan'   => ['required', 'string', 'max:255'],
        'tanggal_mulai'    => ['required', 'date'],
        'tanggal_selesai'  => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        'bukti_kegiatan'   => ['required', 'url', 'max:2048'],
    ];

    /**
     * Halaman utama Kerja Sama (server-rendered untuk load pertama & SEO).
     * Aksi tambah/edit/hapus selanjutnya berjalan lewat fetch() tanpa reload.
     *
     * Akses "biasa" (default mahasiswa/dosen) hanya bisa melihat & mengisi
     * datanya sendiri. Akses "penuh"/"readonly" melihat & mengelola semua.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('kerja_sama') === 'biasa';

        $query = KerjaSama::with('user')->latest();
        if ($ownScope) {
            $query->where('user_id', $user->id);
        }
        $items = $query->paginate(10);

        $userList = $ownScope
            ? collect([$user])
            : User::whereIn('role', ['mahasiswa', 'dosen'])->orderBy('name')->get();

        $internasionalJenis = [
            'conference_internasional',
            'guest_lecture',
            'pengabdian_internasional',
            'research_internasional',
        ];

        $statsQuery = fn () => $ownScope
            ? KerjaSama::where('user_id', $user->id)
            : KerjaSama::query();

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'mahasiswa'     => (clone $statsQuery())->where('tipe_user', 'mahasiswa')->count(),
            'dosen'         => (clone $statsQuery())->where('tipe_user', 'dosen')->count(),
            'internasional' => (clone $statsQuery())->whereIn('jenis', $internasionalJenis)->count(),
        ];

        return view('kerja-sama', compact('items', 'userList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $item = KerjaSama::create($data)->load('user');

        $this->notifyKerjaSama($request, $item, 'ditambahkan');

        return response()->json([
            'success' => true,
            'data'    => $this->format($item),
        ]);
    }

    public function update(Request $request, KerjaSama $kerjaSama)
    {
        $this->authorizeOwnership($request, $kerjaSama);

        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).
        $request->merge(['user_id' => $kerjaSama->user_id, 'tipe_user' => $kerjaSama->tipe_user]);

        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $kerjaSama->update($data);
        $kerjaSama->load('user');

        $this->notifyKerjaSama($request, $kerjaSama, 'diperbarui');

        return response()->json([
            'success' => true,
            'data'    => $this->format($kerjaSama),
        ]);
    }

    public function destroy(Request $request, KerjaSama $kerjaSama)
    {
        $this->authorizeOwnership($request, $kerjaSama);

        $kerjaSama->loadMissing('user');
        $this->notifyKerjaSama($request, $kerjaSama, 'dihapus');

        $id = $kerjaSama->id;
        $kerjaSama->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    // =====================================================================
    // UNGGAH / UNDUH MASSAL (CSV) — khusus admin & superadmin
    // Kerangka umumnya ada di Concerns\HandlesBulkData + Support\Csv;
    // di sini hanya bagian yang khas Kerja Sama.
    // =====================================================================

    protected function bulkMenu(): string
    {
        return 'kerja_sama';
    }

    protected function bulkExtraAliases(): array
    {
        return ['judul' => 'judul_kegiatan'];
    }

    private const SHEET = 'Data Kerja Sama';

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
    }

    /** Unduh template Excel (.xlsx): sheet petunjuk + sheet data, dengan dropdown pilihan. */
    public function template(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        return $this->bulkXlsxTemplateResponse(
            'template-kerja-sama.xlsx',
            'PETUNJUK IMPORT / EXPORT KERJA SAMA',
            self::SHEET,
            [
                'id'              => ['required' => 'Tidak', 'example' => '', 'width' => 10, 'note' => 'KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.'],
                'nim_nidn'        => ['required' => 'Ya (data baru)', 'example' => '2210001', 'width' => 18, 'note' => 'NIM mahasiswa / NIDN dosen yang sudah terdaftar. Pemilik data tidak bisa diganti saat edit.'],
                'jenis'           => ['required' => 'Ya', 'example' => 'conference_internasional', 'width' => 28, 'options' => ['conference_internasional', 'pkl', 'sharing_session', 'keynote_session', 'guest_lecture', 'pengabdian_internasional', 'research_internasional', 'lainnya'], 'note' => 'Jenis kerja sama. Pilih dari dropdown. Mahasiswa hanya boleh: conference_internasional, pkl, sharing_session, lainnya. Dosen tidak boleh pkl.'],
                'jenis_lainnya'   => ['required' => 'Jika jenis = lainnya', 'example' => '', 'width' => 26, 'note' => 'Tulis nama jenis kerja sama lainnya. Selain jenis "lainnya" kosongkan.'],
                'arah'            => ['required' => 'Jika jenis = guest_lecture', 'example' => '', 'width' => 14, 'options' => ['inbound', 'outbound'], 'note' => 'Arah guest lecture. Selain guest_lecture kosongkan.'],
                'mitra'           => ['required' => 'Ya', 'example' => 'Universitas Tokyo', 'width' => 30, 'note' => 'Nama institusi / perusahaan / negara mitra.'],
                'judul_kegiatan'  => ['required' => 'Ya', 'example' => 'Judul kegiatan', 'width' => 36, 'note' => 'Judul kegiatan kerja sama.'],
                'tanggal_mulai'   => ['required' => 'Ya', 'example' => '2026-03-01', 'width' => 18, 'note' => 'Tanggal mulai kegiatan.'],
                'tanggal_selesai' => ['required' => 'Ya', 'example' => '2026-03-03', 'width' => 18, 'note' => 'Tanggal selesai. Tidak boleh sebelum tanggal_mulai.'],
                'bukti_kegiatan'  => ['required' => 'Ya', 'example' => 'https://drive.google.com/...', 'width' => 46, 'note' => 'Link lengkap diawali https://'],
            ],
            [
                'Isi data di sheet "Data Kerja Sama", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk data baru, isi id (dari hasil Download) untuk mengedit data.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'Mahasiswa hanya boleh jenis: conference_internasional, pkl, sharing_session, lainnya. Dosen tidak boleh pkl.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh seluruh data kerja sama (bisa diedit lalu diunggah ulang). */
    public function export(Request $request)
    {
        $this->bulkEnsureAccess($request, false);

        return $this->bulkXlsxExportResponse(
            'kerja-sama-' . now()->format('Ymd-His') . '.xlsx',
            self::SHEET,
            ['id', 'nim_nidn', 'nama', 'jenis', 'jenis_lainnya', 'arah', 'mitra', 'judul_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan'],
            [10, 18, 28, 28, 26, 14, 30, 36, 18, 18, 46],
            KerjaSama::class,
            fn (KerjaSama $item) => [
                $item->id,
                $item->user->nim_nidn ?? '',
                $item->user->name ?? '',
                $item->jenis,
                $item->jenis_lainnya,
                $item->arah,
                $item->mitra,
                $item->judul_kegiatan,
                optional($item->tanggal_mulai)->format('Y-m-d'),
                optional($item->tanggal_selesai)->format('Y-m-d'),
                $item->bukti_kegiatan,
            ]
        );
    }

    /**
     * Unggah Excel (.xlsx) atau CSV: baris dengan id => edit data itu, tanpa id => tambah baru.
     * Semua baris divalidasi dulu; kalau ada yang bermasalah, TIDAK ADA data
     * yang disimpan dan daftar error per baris dikembalikan.
     */
    public function import(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        $parsed = $this->bulkParseUpload($request, ['jenis', 'mitra', 'judul_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan']);
        if ($parsed instanceof JsonResponse) {
            return $parsed;
        }
        [, $rows] = $parsed;

        $existingById = $this->bulkExistingById(KerjaSama::class, $rows);
        $findOwner = $this->bulkOwnerFinder();

        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $d = $row['data'];

            [$existing, $owner, $rowErrors] = $this->bulkResolveTarget($d, $existingById, $findOwner);

            $jenis = Csv::normalizeKey($d['jenis'] ?? '');
            [$dates, $badDates] = $this->bulkParseDates($d, ['tanggal_mulai', 'tanggal_selesai'], $rowErrors);

            $payload = [
                'user_id'        => $owner?->id,
                // Edit memakai tipe yang tersimpan (sama seperti form edit); data baru diturunkan dari role pemilik.
                'tipe_user'      => $existing ? $existing->tipe_user : ($owner?->role === 'dosen' ? 'dosen' : 'mahasiswa'),
                'jenis'          => $jenis,
                'jenis_lainnya'  => trim((string) ($d['jenis_lainnya'] ?? '')) ?: null,
                'arah'           => Csv::normalizeKey($d['arah'] ?? '') ?: null,
                'mitra'          => trim((string) ($d['mitra'] ?? '')),
                'judul_kegiatan' => trim((string) ($d['judul_kegiatan'] ?? '')),
                'bukti_kegiatan' => trim((string) ($d['bukti_kegiatan'] ?? '')),
            ] + $dates;

            // user_id sudah ditangani pesan di bulkResolveTarget; hindari pesan ganda.
            [$messages, $failedKeys] = $this->bulkValidate(
                $payload,
                self::RULES,
                array_merge($owner ? [] : ['user_id'], $badDates)
            );
            $rowErrors = array_merge($rowErrors, $messages);

            // Batasan jenis per tipe hanya dicek untuk data baru atau saat jenisnya diubah,
            // supaya mengunggah ulang data lama yang tidak diubah tidak ikut gagal.
            $jenisBerubah = !$existing || $jenis !== $existing->jenis;
            if ($jenisBerubah && !in_array('jenis', $failedKeys, true) && $jenis !== '' && ($msg = $this->jenisRestriction($payload['tipe_user'], $jenis))) {
                $rowErrors[] = rtrim($msg, '.') . " (jenis \"{$jenis}\", pemilik terdaftar sebagai {$payload['tipe_user']}).";
            }

            if ($rowErrors) {
                $errors[] = ['row' => $row['line'], 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            if ($jenis !== 'lainnya') {
                $payload['jenis_lainnya'] = null;
            }
            if ($jenis !== 'guest_lecture') {
                $payload['arah'] = null;
            }

            $plan[] = ['existing' => $existing, 'payload' => $payload, 'owner' => $owner];
        }

        if ($errors) {
            return $this->bulkRejected($errors);
        }

        $result = $this->bulkSave(KerjaSama::class, $plan);
        $this->bulkNotifyOwners($request, $result['perOwner'], 'kerja sama');

        return $this->bulkSuccess($result['created'], $result['updated'], $result['unchanged']);
    }

    // =====================================================================
    // HELPER CRUD
    // =====================================================================

    private function notifyKerjaSama(Request $request, KerjaSama $item, string $aksi): void
    {
        $this->notifyOwner($request, $item->user_id, 'kerja sama', $item->judul_kegiatan, $aksi, $item->mitra, ['kerja_sama_id' => $item->id]);
    }

    private function enforceOwnUser(Request $request, array &$data): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('kerja_sama') !== 'biasa') {
            return;
        }

        $data['user_id'] = $actor->id;
        $data['tipe_user'] = $actor->role === 'dosen' ? 'dosen' : 'mahasiswa';
    }

    private function authorizeOwnership(Request $request, KerjaSama $kerjaSama): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('kerja_sama') !== 'biasa') {
            return;
        }

        abort_if(
            $kerjaSama->user_id !== $actor->id,
            403,
            'Kamu hanya bisa mengelola data kerja sama milikmu sendiri.'
        );
    }

    /** Pesan error kalau kombinasi tipe pengguna & jenis tidak diizinkan, atau null kalau boleh. */
    private function jenisRestriction(string $tipe, string $jenis): ?string
    {
        if ($tipe === 'mahasiswa' && !in_array($jenis, ['conference_internasional', 'pkl', 'sharing_session', 'lainnya'], true)) {
            return 'Jenis ini tidak tersedia untuk mahasiswa.';
        }
        if ($tipe === 'dosen' && $jenis === 'pkl') {
            return 'PKL tidak tersedia untuk dosen.';
        }

        return null;
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(self::RULES);

        // Batasan jenis per tipe (mahasiswa hanya jenis tertentu, dosen tidak boleh PKL).
        if ($message = $this->jenisRestriction($data['tipe_user'], $data['jenis'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['jenis' => $message]);
        }

        // Teks "lainnya" hanya disimpan kalau jenisnya memang "lainnya"
        $data['jenis_lainnya'] = $data['jenis'] === 'lainnya'
            ? trim((string) ($data['jenis_lainnya'] ?? ''))
            : null;

        return $data;
    }

    /**
     * Bentuk payload JSON yang dikonsumsi JS untuk membangun/mengganti baris tabel.
     */
    private function format(KerjaSama $item): array
    {
        return [
            'id'               => $item->id,
            'user_id'          => $item->user_id,
            'nim_nidn'         => $item->user->nim_nidn ?? '-',
            'nama'             => $item->user->name ?? 'Tanpa Nama',
            'tipe_user'        => $item->tipe_user,
            'jenis'            => $item->jenis,
            'jenis_lainnya'    => $item->jenis_lainnya,
            'arah'             => $item->arah,
            'mitra'            => $item->mitra,
            'judul_kegiatan'   => $item->judul_kegiatan,
            'tanggal_mulai'    => optional($item->tanggal_mulai)->format('Y-m-d'),
            'tanggal_selesai'  => optional($item->tanggal_selesai)->format('Y-m-d'),
            'bukti_kegiatan'   => $item->bukti_kegiatan,
        ];
    }
}