<?php

namespace App\Http\Controllers;

use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;

class LppmMahasiswaController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;

    /**
     * Akses "biasa" (default mahasiswa) hanya bisa melihat & mengisi
     * publikasinya sendiri. Akses "penuh"/"readonly" melihat & mengelola
     * data semua mahasiswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('lppm_mahasiswa') === 'biasa';
        $mahasiswa = $user->mahasiswa;

        $query = LppmMahasiswa::with('mahasiswa.user')->latest();
        if ($ownScope) {
            $query->where('mahasiswa_id', $mahasiswa->id ?? 0);
        }
        $items = $query->paginate(10);

        $mahasiswaList = $ownScope
            ? ($mahasiswa ? collect([$mahasiswa->load('user')]) : collect())
            : Mahasiswa::with('user')->orderBy('nim')->get();

        $statsQuery = fn () => $ownScope
            ? LppmMahasiswa::where('mahasiswa_id', $mahasiswa->id ?? 0)
            : LppmMahasiswa::query();

        $stats = [
            'total'      => (clone $statsQuery())->count(),
            'sinta'      => (clone $statsQuery())->where('jenis', 'sinta_nasional')->count(),
            'conference' => (clone $statsQuery())->where('jenis', 'conference_internasional')->count(),
            'jurnal'     => (clone $statsQuery())->where('jenis', 'jurnal_internasional')->count(),
        ];

        return view('lppm_mahasiswa', compact('items', 'mahasiswaList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $item = LppmMahasiswa::create($data)->load('mahasiswa.user');

        $this->notifyPublikasi($request, $item, 'ditambahkan');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, LppmMahasiswa $lppmMahasiswa)
    {
        $this->authorizeOwnership($request, $lppmMahasiswa);

        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).

        $request->merge(['mahasiswa_id' => $lppmMahasiswa->mahasiswa_id]);


        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $lppmMahasiswa->update($data);
        $lppmMahasiswa->load('mahasiswa.user');

        $this->notifyPublikasi($request, $lppmMahasiswa, 'diperbarui');

        return response()->json(['success' => true, 'data' => $this->format($lppmMahasiswa)]);
    }

    public function destroy(Request $request, LppmMahasiswa $lppmMahasiswa)
    {
        $this->authorizeOwnership($request, $lppmMahasiswa);

        $lppmMahasiswa->loadMissing('mahasiswa.user');
        $this->notifyPublikasi($request, $lppmMahasiswa, 'dihapus');

        $id = $lppmMahasiswa->id;
        $lppmMahasiswa->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    /** Hapus data terpilih (centang baris tabel) — dipanggil dari BulkSelectionController::hapus(). */
    public function destroyMany(Request $request)
    {
        return $this->bulkDestroySelected(
            $request,
            LppmMahasiswa::class,
            ['mahasiswa.user'],
            fn (LppmMahasiswa $item) => $this->notifyPublikasi($request, $item, 'dihapus')
        );
    }

    /**
     * Update massal data terpilih — dipanggil dari BulkSelectionController::ubah().
     * Kolom yang khas per baris (NIM, mahasiswa, bukti) sengaja TIDAK bisa diubah massal.
     * Kolom yang dikosongkan tidak diubah.
     */
    public function updateMany(Request $request)
    {
        return $this->bulkUpdateSelected(
            $request,
            LppmMahasiswa::class,
            ['mahasiswa.user'],
            [
                'jenis'      => ['nullable', 'in:sinta_nasional,conference_internasional,jurnal_internasional'],
                'judul'      => ['nullable', 'string', 'max:255'],
                'penulis'    => ['nullable', 'string', 'max:255'],
                'nama_jurnal'=> ['nullable', 'string', 'max:255'],
                'peringkat'  => ['nullable', 'string', 'max:50'],
                'tahun'      => ['nullable', 'integer', 'min:2000', 'max:2100'],
            ],
            fn (LppmMahasiswa $item) => $this->notifyPublikasi($request, $item, 'diperbarui'),
            fn (LppmMahasiswa $item) => $this->format($item),
            fn (LppmMahasiswa $item, array $changes) => $this->resolveBulkRow($item, $changes)
        );
    }

    /**
     * Validasi silang satu baris untuk update massal (nilai akhir = data lama + kolom yang diisi).
     * Kolom yang tidak berlaku untuk jenis akhirnya dikosongkan, kolom yang wajib dicek terisi.
     *
     * @return array{0: array, 1: string[]}
     */
    private function resolveBulkRow(LppmMahasiswa $item, array $changes): array
    {
        $final = $this->normalizeByJenis(array_merge($item->only(['jenis', 'nama_jurnal', 'peringkat']), $changes));
        $errors = [];

        if (in_array($final['jenis'], self::JENIS_JURNAL, true)) {
            foreach (['nama_jurnal', 'peringkat'] as $col) {
                if (trim((string) ($final[$col] ?? '')) === '') {
                    $errors[] = "{$col} wajib diisi untuk jenis {$final['jenis']}.";
                }
            }
        }

        return [array_merge($changes, [
            'nama_jurnal' => $final['nama_jurnal'] ?? null,
            'peringkat'   => $final['peringkat'] ?? null,
        ]), $errors];
    }

    // =====================================================================
    // UNGGAH / UNDUH MASSAL (CSV) — khusus admin & superadmin
    // Kerangka umumnya ada di Concerns\HandlesBulkData + Support\Csv;
    // di sini hanya bagian yang khas menu ini.
    // =====================================================================

    protected function bulkMenu(): string
    {
        return 'lppm_mahasiswa';
    }

    // Pemilik data lewat tabel profil mahasiswa, bukan langsung user_id.
    protected function bulkOwnerRelation(): string
    {
        return 'mahasiswa';
    }

    protected function bulkOwnerWith(): string
    {
        return 'mahasiswa.user';
    }

    protected function bulkOwnerKey(): string
    {
        return 'mahasiswa_id';
    }

    protected function bulkIdColumn(): string
    {
        return 'nim';
    }

    protected function bulkOwnerLabel(): string
    {
        return 'mahasiswa';
    }

    protected function bulkOwnerUserId(?object $owner): ?int
    {
        return $owner?->user_id;
    }

    protected function bulkOwnerFinder(): \Closure
    {
        return $this->bulkMakeFinder(Mahasiswa::all(), 'nim');
    }

    private const SHEET = 'Data LPPM Mahasiswa';

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
    }

    /** Unduh template Excel (.xlsx): sheet petunjuk + sheet data, dengan dropdown pilihan. */
    public function template(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        return $this->bulkXlsxTemplateResponse(
            'template-lppm-mahasiswa.xlsx',
            'PETUNJUK IMPORT / EXPORT LPPM MAHASISWA',
            self::SHEET,
            [
                'id'              => ['required' => 'Tidak', 'example' => '', 'width' => 10, 'note' => 'KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.'],
                'nim'             => ['required' => 'Ya (data baru)', 'example' => '2210001', 'width' => 18, 'note' => 'NIM mahasiswa yang sudah terdaftar. Pemilik data tidak bisa diganti saat edit.'],
                'jenis'           => ['required' => 'Ya', 'example' => 'sinta_nasional', 'width' => 24, 'options' => ['sinta_nasional', 'conference_internasional', 'jurnal_internasional'], 'note' => 'Jenis publikasi. Pilih dari dropdown.'],
                'judul'           => ['required' => 'Ya', 'example' => 'Judul karya', 'width' => 40, 'note' => 'Judul publikasi.'],
                'penulis'         => ['required' => 'Ya', 'example' => 'Nama Penulis, Nama Penulis 2', 'width' => 36, 'note' => 'Daftar penulis.'],
                'nama_jurnal'     => ['required' => 'Jika jenis = sinta_nasional / jurnal_internasional', 'example' => 'Jurnal Informatika', 'width' => 30, 'note' => 'Nama jurnal. Untuk conference kosongkan (isian diabaikan).'],
                'peringkat'       => ['required' => 'Jika jenis = sinta_nasional / jurnal_internasional', 'example' => 'S2', 'width' => 12, 'note' => 'Peringkat jurnal. Untuk conference kosongkan.'],
                'link_doi'        => ['required' => 'Tidak', 'example' => '', 'width' => 40, 'note' => 'Link DOI (opsional), diawali https://'],
                'bukti_kegiatan'  => ['required' => 'Ya', 'example' => 'https://drive.google.com/...', 'width' => 46, 'note' => 'Link lengkap diawali https://'],
                'tahun'           => ['required' => 'Ya', 'example' => '2026', 'width' => 10, 'note' => 'Tahun 4 digit, antara 2000 dan 2100.'],
            ],
            [
                'Isi data di sheet "Data LPPM Mahasiswa", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk data baru, isi id (dari hasil Download) untuk mengedit data.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'Kolom "nama" tidak ada di template: otomatis diambil dari NIM.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh seluruh data publikasi mahasiswa (bisa diedit lalu diunggah ulang). */
    public function export(Request $request)
    {
        $this->bulkEnsureAccess($request, false);

        return $this->bulkXlsxExportResponse(
            'lppm-mahasiswa-' . now()->format('Ymd-His') . '.xlsx',
            self::SHEET,
            ['id', 'nim', 'nama', 'jenis', 'judul', 'penulis', 'nama_jurnal', 'peringkat', 'link_doi', 'bukti_kegiatan', 'tahun'],
            [10, 18, 28, 24, 40, 36, 30, 12, 36, 46, 10],
            LppmMahasiswa::class,
            fn (LppmMahasiswa $item) => [
                $item->id,
                optional($item->mahasiswa)->nim ?? '',
                optional(optional($item->mahasiswa)->user)->name ?? '',
                $item->jenis,
                $item->judul,
                $item->penulis,
                $item->nama_jurnal,
                $item->peringkat,
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

        $existingById = $this->bulkExistingById(LppmMahasiswa::class, $rows);
        $findOwner = $this->bulkOwnerFinder();

        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $d = $row['data'];

            [$existing, $owner, $rowErrors] = $this->bulkResolveTarget($d, $existingById, $findOwner);

            $jenis = Csv::normalizeKey($d['jenis'] ?? '');
            $isJurnal = in_array($jenis, ['sinta_nasional', 'jurnal_internasional'], true);

            // Kolom yang tidak berlaku untuk jenisnya dikosongkan sejak awal.
            $payload = [
                'mahasiswa_id'   => $owner?->id,
                'jenis'          => $jenis,
                'judul'          => trim((string) ($d['judul'] ?? '')),
                'penulis'        => trim((string) ($d['penulis'] ?? '')),
                'nama_jurnal'    => $isJurnal ? (trim((string) ($d['nama_jurnal'] ?? '')) ?: null) : null,
                'peringkat'      => $isJurnal ? (trim((string) ($d['peringkat'] ?? '')) ?: null) : null,
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

        $result = $this->bulkSave(LppmMahasiswa::class, $plan);
        $this->bulkNotifyOwners($request, $result['perOwner'], 'publikasi');

        return $this->bulkSuccess($result['created'], $result['updated'], $result['unchanged']);
    }

    private function notifyPublikasi(Request $request, LppmMahasiswa $item, string $aksi): void
    {
        $this->notifyOwner($request, $item->mahasiswa->user_id ?? null, 'publikasi', $item->judul, $aksi, str_replace('_', ' ', $item->jenis), ['lppm_mahasiswa_id' => $item->id]);
    }

    private function enforceOwnMahasiswa(Request $request, array &$data): void
    {
        $user = $request->user();
        if ($user->menuLevel('lppm_mahasiswa') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(!$mahasiswa, 422, 'Profil mahasiswa kamu belum terhubung ke akun ini.');

        $data['mahasiswa_id'] = $mahasiswa->id;
    }

    private function authorizeOwnership(Request $request, LppmMahasiswa $lppmMahasiswa): void
    {
        $user = $request->user();
        if ($user->menuLevel('lppm_mahasiswa') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(
            !$mahasiswa || $lppmMahasiswa->mahasiswa_id !== $mahasiswa->id,
            403,
            'Kamu hanya bisa mengelola data publikasi milikmu sendiri.'
        );
    }

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private function rules(): array
    {
        return [
            'mahasiswa_id'   => ['required', 'exists:mahasiswa,id'],
            'jenis'          => ['required', 'in:sinta_nasional,conference_internasional,jurnal_internasional'],
            'judul'          => ['required', 'string', 'max:255'],
            'penulis'        => ['required', 'string', 'max:255'],
            'nama_jurnal'    => ['nullable', 'required_if:jenis,sinta_nasional', 'required_if:jenis,jurnal_internasional', 'string', 'max:255'],
            'peringkat'      => ['nullable', 'required_if:jenis,sinta_nasional', 'required_if:jenis,jurnal_internasional', 'string', 'max:50'],
            'link_doi'       => ['nullable', 'url', 'max:2048'],
            'bukti_kegiatan' => ['required', 'url', 'max:2048'],
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    /** Jenis yang memakai nama_jurnal & peringkat. */
    private const JENIS_JURNAL = ['sinta_nasional', 'jurnal_internasional'];

    /** Kosongkan kolom yang tidak relevan untuk jenisnya (dipakai form, update massal; import sudah setara). */
    private function normalizeByJenis(array $data): array
    {
        if (!in_array($data['jenis'] ?? null, self::JENIS_JURNAL, true)) {
            $data['nama_jurnal'] = null;
            $data['peringkat'] = null;
        }

        return $data;
    }

    private function validated(Request $request): array
    {
        return $this->normalizeByJenis($request->validate($this->rules()));
    }

    private function format(LppmMahasiswa $item): array
    {
        return [
            'id'             => $item->id,
            'mahasiswa_id'   => $item->mahasiswa_id,
            'nim'            => $item->mahasiswa->nim ?? '-',
            'nama'           => optional($item->mahasiswa->user)->name ?? 'Tanpa Nama',
            'jenis'          => $item->jenis,
            'judul'          => $item->judul,
            'penulis'        => $item->penulis,
            'nama_jurnal'    => $item->nama_jurnal,
            'peringkat'      => $item->peringkat,
            'link_doi'       => $item->link_doi,
            'bukti_kegiatan' => $item->bukti_kegiatan,
            'tahun'          => $item->tahun,
        ];
    }
}