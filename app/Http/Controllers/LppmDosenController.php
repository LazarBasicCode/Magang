<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\LppmDosen;
use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /** Unduh template CSV kosong lengkap dengan petunjuk pengisian. */
    public function template(Request $request): StreamedResponse
    {
        $this->bulkEnsureAccess($request, true);

        return $this->bulkTemplateResponse(
            'template-lppm-dosen.csv',
            [
                'id'             => ['required' => 'Tidak',                            'example' => '',                             'note' => 'KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.'],
                'nidn'           => ['required' => 'Ya (data baru)',                   'example' => '0712048901',                   'note' => 'NIDN dosen yang sudah terdaftar. Pemilik data tidak bisa diganti saat edit.'],
                'jenis'          => ['required' => 'Ya',                               'example' => 'q_internasional',              'note' => 'Pilih salah satu: q_internasional | sinta_nasional | hki | book'],
                'judul'          => ['required' => 'Ya',                               'example' => 'Judul karya',                  'note' => 'Judul publikasi / HKI / buku.'],
                'penulis'        => ['required' => 'Ya',                               'example' => 'Nama Penulis, Nama Penulis 2', 'note' => 'Daftar penulis.'],
                'nama_jurnal'    => ['required' => 'Jika jenis = q_internasional / sinta_nasional', 'example' => 'Journal of Computing', 'note' => 'Nama jurnal. Untuk jenis lain dikosongkan (isian diabaikan).'],
                'peringkat'      => ['required' => 'Jika jenis = q_internasional / sinta_nasional', 'example' => 'Q1',              'note' => 'Peringkat jurnal, mis. Q1 atau S2.'],
                'jenis_hki'      => ['required' => 'Jika jenis = hki',                 'example' => '',                             'note' => 'Pilih salah satu: hak_cipta | paten | merek'],
                'kategori_buku'  => ['required' => 'Jika jenis = book',                'example' => '',                             'note' => 'Pilih salah satu: ajar | referensi | chapter'],
                'link_doi'       => ['required' => 'Tidak',                            'example' => '',                             'note' => 'Link DOI (opsional), diawali https://'],
                'bukti_kegiatan' => ['required' => 'Ya',                               'example' => 'https://drive.google.com/...', 'note' => 'Link lengkap diawali https://'],
                'tahun'          => ['required' => 'Ya',                               'example' => '2026',                         'note' => 'Tahun 4 digit, antara 2000 dan 2100.'],
            ],
            [
                'Kolom "nama" tidak ada di template: otomatis diambil dari NIDN.',
                'Kolom yang tidak berlaku untuk jenisnya (mis. jenis_hki untuk jurnal) cukup dikosongkan.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai CSV.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh seluruh data publikasi dosen (bisa diedit lalu diunggah ulang). */
    public function export(Request $request): StreamedResponse
    {
        $this->bulkEnsureAccess($request, false);

        return $this->bulkExportResponse(
            'lppm-dosen-' . now()->format('Ymd-His') . '.csv',
            ['id', 'nidn', 'nama', 'jenis', 'judul', 'penulis', 'nama_jurnal', 'peringkat', 'jenis_hki', 'kategori_buku', 'link_doi', 'bukti_kegiatan', 'tahun'],
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
     * Unggah CSV: baris dengan id => edit data itu, tanpa id => tambah baru.
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

    private function validated(Request $request): array
    {
        return $request->validate($this->rules());
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