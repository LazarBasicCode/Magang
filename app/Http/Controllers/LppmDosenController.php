<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\LppmDosen;
use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;

class LppmDosenController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;

    /**
     * Akses "biasa" (default dosen) hanya bisa melihat & mengisi
     * publikasinya sendiri. Akses "penuh"/"readonly" melihat & mengelola
     * data semua dosen.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('lppm_dosen') === 'biasa';
        $dosen = $user->dosen;

        $query = LppmDosen::with('dosen.user')->latest();
        if ($ownScope) {
            $query->where('dosen_id', $dosen->id ?? 0);
        }
        $items = $query->paginate(10);

        $dosenList = $ownScope
            ? ($dosen ? collect([$dosen->load('user')]) : collect())
            : Dosen::with('user')->orderBy('nidn')->get();

        $statsQuery = fn () => $ownScope
            ? LppmDosen::where('dosen_id', $dosen->id ?? 0)
            : LppmDosen::query();

        $stats = [
            'total'    => (clone $statsQuery())->count(),
            'jurnal'   => (clone $statsQuery())->where('jenis', 'q_internasional')->count(),
            'sinta'    => (clone $statsQuery())->where('jenis', 'sinta_nasional')->count(),
            'hki_buku' => (clone $statsQuery())->whereIn('jenis', ['hki', 'book'])->count(),
        ];

        return view('lppm_dosen', compact('items', 'dosenList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnDosen($request, $data);

        $item = LppmDosen::create($data)->load('dosen.user');

        $this->notifyPublikasi($request, $item, 'ditambahkan');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, LppmDosen $lppmDosen)
    {
        $this->authorizeOwnership($request, $lppmDosen);

        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).

        $request->merge(['dosen_id' => $lppmDosen->dosen_id]);


        $data = $this->validated($request);
        $this->enforceOwnDosen($request, $data);

        $lppmDosen->update($data);
        $lppmDosen->load('dosen.user');

        $this->notifyPublikasi($request, $lppmDosen, 'diperbarui');

        return response()->json(['success' => true, 'data' => $this->format($lppmDosen)]);
    }

    public function destroy(Request $request, LppmDosen $lppmDosen)
    {
        $this->authorizeOwnership($request, $lppmDosen);

        $lppmDosen->loadMissing('dosen.user');
        $this->notifyPublikasi($request, $lppmDosen, 'dihapus');

        $id = $lppmDosen->id;
        $lppmDosen->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    /** Hapus data terpilih (centang baris tabel) — dipanggil dari BulkSelectionController::hapus(). */
    public function destroyMany(Request $request)
    {
        return $this->bulkDestroySelected(
            $request,
            LppmDosen::class,
            ['dosen.user'],
            fn (LppmDosen $item) => $this->notifyPublikasi($request, $item, 'dihapus')
        );
    }

    /**
     * Update massal data terpilih — dipanggil dari BulkSelectionController::ubah().
     * Kolom yang khas per baris (NIDN, dosen, bukti, link_doi) sengaja TIDAK bisa diubah massal.
     * Kolom yang dikosongkan tidak diubah.
     */
    public function updateMany(Request $request)
    {
        return $this->bulkUpdateSelected(
            $request,
            LppmDosen::class,
            ['dosen.user'],
            [
                'jenis'         => ['nullable', 'in:q_internasional,sinta_nasional,hki,book'],
                'judul'         => ['nullable', 'string', 'max:255'],
                'penulis'       => ['nullable', 'string', 'max:255'],
                'nama_jurnal'   => ['nullable', 'string', 'max:255'],
                'peringkat'     => ['nullable', 'string', 'max:50'],
                'jenis_hki'     => ['nullable', 'in:hak_cipta,paten,merek'],
                'kategori_buku' => ['nullable', 'in:ajar,referensi,chapter'],
                'tahun'         => ['nullable', 'integer', 'min:2000', 'max:2100'],
            ],
            fn (LppmDosen $item) => $this->notifyPublikasi($request, $item, 'diperbarui'),
            fn (LppmDosen $item) => $this->format($item),
            fn (LppmDosen $item, array $changes) => $this->resolveBulkRow($item, $changes)
        );
    }

    /**
     * Validasi silang satu baris untuk update massal (nilai akhir = data lama + kolom yang diisi).
     * Kolom yang tidak berlaku untuk jenis akhirnya dikosongkan, kolom yang wajib dicek terisi.
     *
     * @return array{0: array, 1: string[]}
     */
    private function resolveBulkRow(LppmDosen $item, array $changes): array
    {
        $final = $this->normalizeByJenis(array_merge(
            $item->only(['jenis', 'nama_jurnal', 'peringkat', 'jenis_hki', 'kategori_buku']),
            $changes
        ));

        $required = match (true) {
            in_array($final['jenis'], self::JENIS_JURNAL, true) => ['nama_jurnal', 'peringkat'],
            $final['jenis'] === 'hki'  => ['jenis_hki'],
            $final['jenis'] === 'book' => ['kategori_buku'],
            default => [],
        };
        $errors = [];
        foreach ($required as $col) {
            if (trim((string) ($final[$col] ?? '')) === '') {
                $errors[] = "{$col} wajib diisi untuk jenis {$final['jenis']}.";
            }
        }

        return [array_merge($changes, [
            'nama_jurnal'   => $final['nama_jurnal'] ?? null,
            'peringkat'     => $final['peringkat'] ?? null,
            'jenis_hki'     => $final['jenis_hki'] ?? null,
            'kategori_buku' => $final['kategori_buku'] ?? null,
        ]), $errors];
    }

    // =====================================================================
    // UNGGAH / UNDUH MASSAL (CSV) — khusus admin & superadmin
    // Kerangka umumnya ada di Concerns\HandlesBulkData + Support\Csv;
    // di sini hanya bagian yang khas menu ini.
    // =====================================================================

    protected function bulkMenu(): string
    {
        return 'lppm_dosen';
    }

    // Pemilik data lewat tabel profil dosen, bukan langsung user_id.
    protected function bulkOwnerRelation(): string
    {
        return 'dosen';
    }

    protected function bulkOwnerWith(): string
    {
        return 'dosen.user';
    }

    protected function bulkOwnerKey(): string
    {
        return 'dosen_id';
    }

    protected function bulkIdColumn(): string
    {
        return 'nidn';
    }

    protected function bulkOwnerLabel(): string
    {
        return 'dosen';
    }

    protected function bulkOwnerUserId(?object $owner): ?int
    {
        return $owner?->user_id;
    }

    protected function bulkOwnerFinder(): \Closure
    {
        return $this->bulkMakeFinder(Dosen::all(), 'nidn');
    }

    private const SHEET = 'Data LPPM Dosen';

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
    }

    /** Unduh template Excel (.xlsx): sheet petunjuk + sheet data, dengan dropdown pilihan. */
    public function template(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        return $this->bulkXlsxTemplateResponse(
            'template-lppm-dosen.xlsx',
            'PETUNJUK IMPORT / EXPORT LPPM DOSEN',
            self::SHEET,
            [
                'id'              => ['required' => 'Tidak', 'example' => '', 'width' => 10, 'note' => 'KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.'],
                'nidn'            => ['required' => 'Ya (data baru)', 'example' => '0712048901', 'width' => 18, 'note' => 'NIDN dosen yang sudah terdaftar. Pemilik data tidak bisa diganti saat edit.'],
                'jenis'           => ['required' => 'Ya', 'example' => 'q_internasional', 'width' => 20, 'options' => ['q_internasional', 'sinta_nasional', 'hki', 'book'], 'note' => 'Jenis karya. Pilih dari dropdown. Jenis ini menentukan kolom mana yang wajib diisi (lihat kolom lain).'],
                'judul'           => ['required' => 'Ya', 'example' => 'Judul karya', 'width' => 40, 'note' => 'Judul publikasi / HKI / buku.'],
                'penulis'         => ['required' => 'Ya', 'example' => 'Nama Penulis, Nama Penulis 2', 'width' => 36, 'note' => 'Daftar penulis.'],
                'nama_jurnal'     => ['required' => 'Jika jenis = q_internasional / sinta_nasional', 'example' => 'Journal of Computing', 'width' => 30, 'note' => 'Nama jurnal. Untuk jenis hki / book kosongkan (isian diabaikan).'],
                'peringkat'       => ['required' => 'Jika jenis = q_internasional / sinta_nasional', 'example' => 'Q1', 'width' => 12, 'note' => 'Peringkat jurnal. Untuk jenis hki / book kosongkan.'],
                'jenis_hki'       => ['required' => 'Jika jenis = hki', 'example' => '', 'width' => 16, 'options' => ['hak_cipta', 'paten', 'merek'], 'note' => 'Jenis HKI. Hanya diisi kalau jenis = hki; selain itu kosongkan.'],
                'kategori_buku'   => ['required' => 'Jika jenis = book', 'example' => '', 'width' => 16, 'options' => ['ajar', 'referensi', 'chapter'], 'note' => 'Kategori buku. Hanya diisi kalau jenis = book; selain itu kosongkan.'],
                'link_doi'        => ['required' => 'Tidak', 'example' => '', 'width' => 40, 'note' => 'Link DOI (opsional), diawali https://'],
                'bukti_kegiatan'  => ['required' => 'Ya', 'example' => 'https://drive.google.com/...', 'width' => 46, 'note' => 'Link lengkap diawali https://'],
                'tahun'           => ['required' => 'Ya', 'example' => '2026', 'width' => 10, 'note' => 'Tahun 4 digit, antara 2000 dan 2100.'],
            ],
            [
                'Isi data di sheet "Data LPPM Dosen", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk data baru, isi id (dari hasil Download) untuk mengedit data.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'Kolom yang tidak berlaku untuk jenisnya (mis. jenis_hki untuk jurnal) cukup dikosongkan.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh seluruh data publikasi dosen (bisa diedit lalu diunggah ulang). */
    public function export(Request $request)
    {
        $this->bulkEnsureAccess($request, false);

        return $this->bulkXlsxExportResponse(
            'lppm-dosen-' . now()->format('Ymd-His') . '.xlsx',
            self::SHEET,
            ['id', 'nidn', 'nama', 'jenis', 'judul', 'penulis', 'nama_jurnal', 'peringkat', 'jenis_hki', 'kategori_buku', 'link_doi', 'bukti_kegiatan', 'tahun'],
            [10, 18, 28, 18, 40, 36, 30, 12, 16, 16, 36, 46, 10],
            LppmDosen::class,
            fn (LppmDosen $item) => [
                $item->id,
                optional($item->dosen)->nidn ?? '',
                optional(optional($item->dosen)->user)->name ?? '',
                $item->jenis,
                $item->judul,
                $item->penulis,
                $item->nama_jurnal,
                $item->peringkat,
                $item->jenis_hki,
                $item->kategori_buku,
                $item->link_doi,
                $item->bukti_kegiatan,
                $item->tahun,
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

        $parsed = $this->bulkParseUpload($request, ['jenis', 'judul', 'penulis', 'bukti_kegiatan', 'tahun']);
        if ($parsed instanceof JsonResponse) {
            return $parsed;
        }
        [, $rows] = $parsed;

        $existingById = $this->bulkExistingById(LppmDosen::class, $rows);
        $findOwner = $this->bulkOwnerFinder();

        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $d = $row['data'];

            [$existing, $owner, $rowErrors] = $this->bulkResolveTarget($d, $existingById, $findOwner);

            $jenis = Csv::normalizeKey($d['jenis'] ?? '');
            $isJurnal = in_array($jenis, ['q_internasional', 'sinta_nasional'], true);

            // Kolom yang tidak berlaku untuk jenisnya dikosongkan sejak awal.
            $payload = [
                'dosen_id'       => $owner?->id,
                'jenis'          => $jenis,
                'judul'          => trim((string) ($d['judul'] ?? '')),
                'penulis'        => trim((string) ($d['penulis'] ?? '')),
                'nama_jurnal'    => $isJurnal ? (trim((string) ($d['nama_jurnal'] ?? '')) ?: null) : null,
                'peringkat'      => $isJurnal ? (trim((string) ($d['peringkat'] ?? '')) ?: null) : null,
                'jenis_hki'      => $jenis === 'hki' ? (Csv::normalizeKey($d['jenis_hki'] ?? '') ?: null) : null,
                'kategori_buku'  => $jenis === 'book' ? (Csv::normalizeKey($d['kategori_buku'] ?? '') ?: null) : null,
                'link_doi'       => trim((string) ($d['link_doi'] ?? '')) ?: null,
                'bukti_kegiatan' => trim((string) ($d['bukti_kegiatan'] ?? '')),
                'tahun'          => $this->bulkYear($d['tahun'] ?? ''),
            ];

            // Pemilik sudah ditangani pesan di bulkResolveTarget; hindari pesan ganda.
            [$messages] = $this->bulkValidate($payload, $this->rules(), $owner ? [] : [$this->bulkOwnerKey()]);
            $rowErrors = array_merge($rowErrors, $messages);

            if ($rowErrors) {
                $errors[] = ['row' => $row['line'], 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            $plan[] = ['existing' => $existing, 'payload' => $payload, 'owner' => $owner];
        }

        if ($errors) {
            return $this->bulkRejected($errors);
        }

        $result = $this->bulkSave(LppmDosen::class, $plan);
        $this->bulkNotifyOwners($request, $result['perOwner'], 'publikasi');

        return $this->bulkSuccess($result['created'], $result['updated'], $result['unchanged']);
    }

    private function notifyPublikasi(Request $request, LppmDosen $item, string $aksi): void
    {
        $this->notifyOwner($request, $item->dosen->user_id ?? null, 'publikasi', $item->judul, $aksi, str_replace('_', ' ', $item->jenis), ['lppm_dosen_id' => $item->id]);
    }

    private function enforceOwnDosen(Request $request, array &$data): void
    {
        $user = $request->user();
        if ($user->menuLevel('lppm_dosen') !== 'biasa') {
            return;
        }

        $dosen = $user->dosen;
        abort_if(!$dosen, 422, 'Profil dosen kamu belum terhubung ke akun ini.');

        $data['dosen_id'] = $dosen->id;
    }

    private function authorizeOwnership(Request $request, LppmDosen $lppmDosen): void
    {
        $user = $request->user();
        if ($user->menuLevel('lppm_dosen') !== 'biasa') {
            return;
        }

        $dosen = $user->dosen;
        abort_if(
            !$dosen || $lppmDosen->dosen_id !== $dosen->id,
            403,
            'Kamu hanya bisa mengelola data publikasi milikmu sendiri.'
        );
    }

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private function rules(): array
    {
        return [
            'dosen_id'       => ['required', 'exists:dosen,id'],
            'jenis'          => ['required', 'in:q_internasional,sinta_nasional,hki,book'],
            'judul'          => ['required', 'string', 'max:255'],
            'penulis'        => ['required', 'string', 'max:255'],
            'nama_jurnal'    => ['nullable', 'required_if:jenis,q_internasional', 'required_if:jenis,sinta_nasional', 'string', 'max:255'],
            'peringkat'      => ['nullable', 'required_if:jenis,q_internasional', 'required_if:jenis,sinta_nasional', 'string', 'max:50'],
            'jenis_hki'      => ['nullable', 'required_if:jenis,hki', 'in:hak_cipta,paten,merek'],
            'kategori_buku'  => ['nullable', 'required_if:jenis,book', 'in:ajar,referensi,chapter'],
            'link_doi'       => ['nullable', 'url', 'max:2048'],
            'bukti_kegiatan' => ['required', 'url', 'max:2048'],
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    /** Jenis yang memakai nama_jurnal & peringkat. */
    private const JENIS_JURNAL = ['q_internasional', 'sinta_nasional'];

    /** Kosongkan kolom yang tidak relevan untuk jenisnya (dipakai form, update massal; import sudah setara). */
    private function normalizeByJenis(array $data): array
    {
        $jenis = $data['jenis'] ?? null;

        if (!in_array($jenis, self::JENIS_JURNAL, true)) {
            $data['nama_jurnal'] = null;
            $data['peringkat'] = null;
        }
        if ($jenis !== 'hki') {
            $data['jenis_hki'] = null;
        }
        if ($jenis !== 'book') {
            $data['kategori_buku'] = null;
        }

        return $data;
    }

    private function validated(Request $request): array
    {
        return $this->normalizeByJenis($request->validate($this->rules()));
    }

    private function format(LppmDosen $item): array
    {
        return [
            'id'             => $item->id,
            'dosen_id'       => $item->dosen_id,
            'nidn'           => $item->dosen->nidn ?? '-',
            'nama'           => optional($item->dosen->user)->name ?? 'Tanpa Nama',
            'jenis'          => $item->jenis,
            'judul'          => $item->judul,
            'penulis'        => $item->penulis,
            'nama_jurnal'    => $item->nama_jurnal,
            'peringkat'      => $item->peringkat,
            'jenis_hki'      => $item->jenis_hki,
            'kategori_buku'  => $item->kategori_buku,
            'link_doi'       => $item->link_doi,
            'bukti_kegiatan' => $item->bukti_kegiatan,
            'tahun'          => $item->tahun,
        ];
    }
}