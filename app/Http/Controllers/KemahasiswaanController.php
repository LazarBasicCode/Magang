<?php

namespace App\Http\Controllers;

use App\Models\Kemahasiswaan;
use App\Models\Mahasiswa;
use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class KemahasiswaanController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;

    /**
     * Halaman utama Kemahasiswaan (server-rendered untuk load pertama & SEO).
     * Aksi tambah/edit/hapus selanjutnya berjalan lewat fetch() tanpa reload.
     *
     * Akses "biasa" (default mahasiswa) hanya bisa melihat & mengisi datanya
     * sendiri. Akses "penuh"/"readonly" (admin/superadmin, atau staf yang
     * sengaja diberi readonly) melihat & mengelola data semua mahasiswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('kemahasiswaan') === 'biasa';
        $mahasiswa = $user->mahasiswa;

        $query = Kemahasiswaan::with('mahasiswa.user')->latest();
        if ($ownScope) {
            $query->where('mahasiswa_id', $mahasiswa->id ?? 0);
        }
        $kegiatan = $query->paginate(10);

        $mahasiswaList = $ownScope
            ? ($mahasiswa ? collect([$mahasiswa->load('user')]) : collect())
            : Mahasiswa::with('user')->orderBy('nim')->get();

        $statsQuery = fn () => $ownScope
            ? Kemahasiswaan::where('mahasiswa_id', $mahasiswa->id ?? 0)
            : Kemahasiswaan::query();

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'nasional'      => (clone $statsQuery())->where('tingkat', 'nasional')->count(),
            'internasional' => (clone $statsQuery())->where('tingkat', 'internasional')->count(),
            'inbis'         => (clone $statsQuery())->where('jenis', 'inbis')->count(),
        ];

        return view('kemahasiswaan', compact('kegiatan', 'mahasiswaList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $item = Kemahasiswaan::create($data)->load('mahasiswa.user');

        $this->notifyKegiatan($request, $item, 'ditambahkan');

        return response()->json([
            'success' => true,
            'data'    => $this->format($item),
        ]);
    }

    public function update(Request $request, Kemahasiswaan $kemahasiswaan)
    {
        $this->authorizeOwnership($request, $kemahasiswaan);

        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).

        $request->merge(['mahasiswa_id' => $kemahasiswaan->mahasiswa_id]);


        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $kemahasiswaan->update($data);
        $kemahasiswaan->load('mahasiswa.user');

        $this->notifyKegiatan($request, $kemahasiswaan, 'diperbarui');

        return response()->json([
            'success' => true,
            'data'    => $this->format($kemahasiswaan),
        ]);
    }

    public function destroy(Request $request, Kemahasiswaan $kemahasiswaan)
    {
        $this->authorizeOwnership($request, $kemahasiswaan);

        $kemahasiswaan->load('mahasiswa.user');
        $this->notifyKegiatan($request, $kemahasiswaan, 'dihapus');

        $id = $kemahasiswaan->id;
        $kemahasiswaan->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    /** Hapus data terpilih (centang baris tabel) — dipanggil dari BulkSelectionController::hapus(). */
    public function destroyMany(Request $request)
    {
        return $this->bulkDestroySelected(
            $request,
            Kemahasiswaan::class,
            ['mahasiswa.user'],
            fn (Kemahasiswaan $item) => $this->notifyKegiatan($request, $item, 'dihapus')
        );
    }

    // =====================================================================
    // UNGGAH / UNDUH MASSAL (EXCEL .xlsx; CSV lama tetap diterima saat unggah) — khusus admin & superadmin
    // Kerangka umumnya ada di Concerns\HandlesBulkData + Support\Csv;
    // di sini hanya bagian yang khas menu ini.
    // =====================================================================

    protected function bulkMenu(): string
    {
        return 'kemahasiswaan';
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

    private const SHEET = 'Data Kemahasiswaan';

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
    }

    /** Unduh template Excel (.xlsx): sheet data + sheet petunjuk, dengan dropdown pilihan. */
    public function template(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        return $this->bulkXlsxTemplateResponse(
            'template-kemahasiswaan.xlsx',
            'PETUNJUK IMPORT / EXPORT KEMAHASISWAAN',
            self::SHEET,
            [
                'id'             => ['required' => 'Tidak',          'example' => '',                             'width' => 10, 'note' => 'KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.'],
                'nim'            => ['required' => 'Ya (data baru)', 'example' => '2210001',                      'width' => 18, 'note' => 'NIM mahasiswa yang sudah terdaftar. Pemilik data tidak bisa diganti saat edit.'],
                'jenis'          => ['required' => 'Ya',             'example' => 'kemahasiswaan',                'width' => 18, 'options' => ['inbis', 'kemahasiswaan'], 'note' => 'Kategori kegiatan. Pilih dari dropdown.'],
                'tab'            => ['required' => 'Ya',             'example' => 'akademik',                     'width' => 16, 'options' => ['akademik', 'non_akademik'], 'note' => 'Bidang kegiatan. Perhatikan non_akademik pakai garis bawah (_). Pilih dari dropdown.'],
                'tingkat'        => ['required' => 'Ya',             'example' => 'nasional',                     'width' => 16, 'options' => ['lokal', 'nasional', 'internasional'], 'note' => 'Skala/tingkat kegiatan atau prestasi. Pilih dari dropdown.'],
                'tahun'          => ['required' => 'Ya',             'example' => '2026',                         'width' => 10, 'note' => 'Tahun 4 digit, antara 2000 dan 2100.'],
                'nama_kegiatan'  => ['required' => 'Ya',             'example' => 'Juara 1 Lomba Karya Tulis',    'width' => 38, 'note' => 'Nama kegiatan atau prestasi, mis. "Juara 1 Lomba Karya Tulis".'],
                'bukti_kegiatan' => ['required' => 'Ya',             'example' => 'https://drive.google.com/...', 'width' => 46, 'note' => 'Link lengkap diawali https://'],
            ],
            [
                'Isi data di sheet "Data Kemahasiswaan", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk data baru, isi id (dari hasil Download) untuk mengedit data.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'Kolom "nama" tidak ada di template: otomatis diambil dari NIM.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh seluruh data kegiatan sebagai Excel (bisa diedit lalu diunggah ulang). */
    public function export(Request $request)
    {
        $this->bulkEnsureAccess($request, false);

        return $this->bulkXlsxExportResponse(
            'kemahasiswaan-' . now()->format('Ymd-His') . '.xlsx',
            self::SHEET,
            ['id', 'nim', 'nama', 'jenis', 'tab', 'tingkat', 'tahun', 'nama_kegiatan', 'bukti_kegiatan'],
            [10, 18, 28, 18, 16, 16, 10, 38, 46],
            Kemahasiswaan::class,
            fn (Kemahasiswaan $item) => [
                $item->id,
                optional($item->mahasiswa)->nim ?? '',
                optional(optional($item->mahasiswa)->user)->name ?? '',
                $item->jenis,
                $item->tab,
                $item->tingkat,
                $item->tahun,
                $item->nama_kegiatan,
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

        $parsed = $this->bulkParseUpload($request, ['jenis', 'tab', 'tingkat', 'tahun', 'nama_kegiatan', 'bukti_kegiatan']);
        if ($parsed instanceof JsonResponse) {
            return $parsed;
        }
        [, $rows] = $parsed;

        $existingById = $this->bulkExistingById(Kemahasiswaan::class, $rows);
        $findOwner = $this->bulkOwnerFinder();

        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $d = $row['data'];

            [$existing, $owner, $rowErrors] = $this->bulkResolveTarget($d, $existingById, $findOwner);

            $payload = [
                'mahasiswa_id'   => $owner?->id,
                'jenis'          => Csv::normalizeKey($d['jenis'] ?? ''),
                'tab'            => Csv::normalizeKey($d['tab'] ?? ''),
                'tingkat'        => Csv::normalizeKey($d['tingkat'] ?? ''),
                'tahun'          => $this->bulkYear($d['tahun'] ?? ''),
                'nama_kegiatan'  => trim((string) ($d['nama_kegiatan'] ?? '')),
                'bukti_kegiatan' => trim((string) ($d['bukti_kegiatan'] ?? '')),
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

        $result = $this->bulkSave(Kemahasiswaan::class, $plan);
        $this->bulkNotifyOwners($request, $result['perOwner'], 'kegiatan');

        return $this->bulkSuccess($result['created'], $result['updated'], $result['unchanged']);
    }

    private function notifyKegiatan(Request $request, Kemahasiswaan $item, string $aksi): void
    {
        $this->notifyOwner($request, $item->mahasiswa->user_id ?? null, 'kegiatan', $item->nama_kegiatan, $aksi, '', ['kemahasiswaan_id' => $item->id]);
    }

    /**
     * User dengan akses "biasa" tidak boleh menyimpan/memilih mahasiswa_id
     * milik orang lain, walau nilainya dipaksakan lewat request mentah —
     * mahasiswa_id selalu ditimpa jadi milik mereka sendiri.
     */
    private function enforceOwnMahasiswa(Request $request, array &$data): void
    {
        $user = $request->user();
        if ($user->menuLevel('kemahasiswaan') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(!$mahasiswa, 422, 'Profil mahasiswa kamu belum terhubung ke akun ini.');

        $data['mahasiswa_id'] = $mahasiswa->id;
    }

    /**
     * User dengan akses "biasa" hanya boleh mengubah/menghapus datanya
     * sendiri. Selain itu (penuh/readonly) boleh mengelola siapa saja.
     */
    private function authorizeOwnership(Request $request, Kemahasiswaan $kemahasiswaan): void
    {
        $user = $request->user();
        if ($user->menuLevel('kemahasiswaan') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(
            !$mahasiswa || $kemahasiswaan->mahasiswa_id !== $mahasiswa->id,
            403,
            'Kamu hanya bisa mengelola data kegiatan milikmu sendiri.'
        );
    }

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private function rules(): array
    {
        return [
            'mahasiswa_id'   => ['required', Rule::exists('mahasiswa', 'id')],
            'jenis'          => ['required', 'in:inbis,kemahasiswaan'],
            'tab'            => ['required', 'in:akademik,non_akademik'],
            'tingkat'        => ['required', 'in:lokal,nasional,internasional'],
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
            'nama_kegiatan'  => ['required', 'string', 'max:255'],
            'bukti_kegiatan' => ['required', 'url', 'max:2048'],
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate($this->rules());
    }

    /**
     * Bentuk payload JSON yang dikonsumsi JS untuk membangun/mengganti baris tabel.
     */
    private function format(Kemahasiswaan $item): array
    {
        return [
            'id'             => $item->id,
            'mahasiswa_id'   => $item->mahasiswa_id,
            'nim'            => $item->mahasiswa->nim ?? '-',
            'nama'           => optional($item->mahasiswa->user)->name ?? 'Tanpa Nama',
            'jenis'          => $item->jenis,
            'tab'            => $item->tab,
            'tingkat'        => $item->tingkat,
            'tahun'          => $item->tahun,
            'nama_kegiatan'  => $item->nama_kegiatan,
            'bukti_kegiatan' => $item->bukti_kegiatan,
        ];
    }
}
