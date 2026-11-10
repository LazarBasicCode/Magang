<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\MahasiswaView;
use App\Http\Controllers\Concerns\NotifiesOwner;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Rekognisi;
use App\Models\User;
use App\Support\DuplicateDetector;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use App\Support\Approval;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LppmRekognisiController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;
    use MahasiswaView;

    /** Jumlah baris per halaman (disamakan dengan Data Master Pengguna). */
    private const PER_PAGE = 50;

    /** Jenis karya yang memakai Skala (Nasional / Internasional). */
    private const SCALABLE_KINDS = ['jurnal', 'sinta', 'conference', 'prosiding', 'paten'];

    /** Label pilihan dropdown (value => label). Dipakai semua modal & filter; selalu diurutkan A-Z lewat sortedOptions(). */
    private const JENIS_LABELS = [
        'jurnal' => 'Jurnal', 'hki' => 'HKI', 'book' => 'Buku', 'sinta' => 'Publikasi SINTA',
        'conference' => 'Conference', 'prosiding' => 'Prosiding', 'paten' => 'Paten',
    ];
    private const SKALA_LABELS = ['nasional' => 'Nasional', 'internasional' => 'Internasional'];
    private const STATUS_PATEN_LABELS = ['terdaftar' => 'Terdaftar (Filed)', 'granted' => 'Granted (Bersertifikat)'];
    private const JENIS_HKI_LABELS = ['hak_cipta' => 'Hak Cipta', 'paten' => 'Paten', 'merek' => 'Merek'];
    private const KATEGORI_BUKU_LABELS = ['ajar' => 'Buku Ajar', 'referensi' => 'Referensi', 'chapter' => 'Chapter'];

    /** Urutkan pilihan dropdown berdasarkan label A-Z (tanpa peduli huruf besar/kecil), kunci tetap. */
    private function sortedOptions(array $options): array
    {
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);
        return $options;
    }

    /** Metadata khusus jenis yang BOLEH tersimpan per jenis (sisanya dikosongkan saat jenis berganti). */
    private const KIND_FIELDS = [
        'jurnal'     => ['nama_jurnal', 'peringkat', 'link_doi'],
        'sinta'      => ['nama_jurnal', 'peringkat', 'link_doi'],
        'conference' => [],
        'hki'        => ['jenis_hki'],
        'book'       => ['kategori_buku'],
        'prosiding'  => ['link_doi', 'nama_konferensi', 'nomor_issn_isbn'],
        'paten'      => ['status_paten', 'nomor_paten'],
    ];

    /** Label kolom metadata khusus jenis (untuk pesan error). */
    private const FIELD_LABELS = [
        'nama_jurnal' => 'Nama jurnal', 'peringkat' => 'Peringkat', 'link_doi' => 'Link DOI',
        'jenis_hki' => 'Jenis HKI', 'kategori_buku' => 'Kategori buku',
        'nama_konferensi' => 'Nama konferensi', 'nomor_issn_isbn' => 'Nomor ISSN / ISBN',
        'status_paten' => 'Status paten', 'nomor_paten' => 'Nomor permohonan / paten',
    ];

    /** Metadata yang WAJIB ada per jenis (sama dengan RULES). */
    private const KIND_REQUIRED = [
        'jurnal'    => ['nama_jurnal' => 'Nama jurnal', 'peringkat' => 'Peringkat'],
        'sinta'     => ['nama_jurnal' => 'Nama jurnal', 'peringkat' => 'Peringkat'],
        'hki'       => ['jenis_hki' => 'Jenis HKI'],
        'book'      => ['kategori_buku' => 'Kategori buku'],
        'prosiding' => ['link_doi' => 'Link DOI', 'nama_konferensi' => 'Nama konferensi', 'nomor_issn_isbn' => 'Nomor ISSN / ISBN'],
        'paten'     => ['status_paten' => 'Status paten', 'nomor_paten' => 'Nomor permohonan / paten'],
    ];

    /** Satu sumber jenis karya yang tersedia untuk validasi dan update massal. */
    public const JENIS_BY_TIPE = [
        'mahasiswa' => ['sinta', 'conference', 'jurnal'],
        'alumni'    => ['sinta', 'conference', 'jurnal'],
        'dosen'     => ['jurnal', 'hki', 'book', 'prosiding', 'paten'],
    ];

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private const RULES = [
        'jenis'           => ['required', 'in:jurnal,hki,book,sinta,conference,prosiding,paten'],
        'skala'           => ['nullable', 'required_if:jenis,jurnal,sinta,conference,prosiding,paten', 'in:nasional,internasional'],
        'mitra'           => ['nullable', 'string', 'max:255'],
        'judul'           => ['required', 'string', 'max:255'],
        'penulis'         => ['required', 'string', 'max:255'],
        'nama_jurnal'     => ['nullable', 'required_if:jenis,jurnal', 'required_if:jenis,sinta', 'string', 'max:255'],
        'peringkat'       => ['nullable', 'required_if:jenis,jurnal', 'required_if:jenis,sinta', 'string', 'max:100'],
        'jenis_hki'       => ['nullable', 'required_if:jenis,hki', 'in:hak_cipta,paten,merek'],
        'kategori_buku'   => ['nullable', 'required_if:jenis,book', 'in:ajar,referensi,chapter'],
        'link_doi'        => ['nullable', 'required_if:jenis,prosiding', 'url', 'max:2048'],
        'nama_konferensi' => ['nullable', 'required_if:jenis,prosiding', 'string', 'max:255'],
        'nomor_issn_isbn' => ['nullable', 'required_if:jenis,prosiding', 'string', 'max:100'],
        'status_paten'    => ['nullable', 'required_if:jenis,paten', 'in:terdaftar,granted'],
        'nomor_paten'     => ['nullable', 'required_if:jenis,paten', 'string', 'max:100'],
        'jabatan'         => ['nullable', 'string', 'max:255'],
        'tanggal_mulai'   => ['required', 'date'],
        'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        'tahun'           => ['required', 'integer', 'between:2000,2100'],
        'bukti_kegiatan'  => ['required', 'url', 'max:2048'],
        'bukti_tambahan'   => ['nullable', 'array', 'max:10'],
        'bukti_tambahan.*'      => ['array'],
        'bukti_tambahan.*.nama' => ['nullable', 'string', 'max:100'],
        'bukti_tambahan.*.url'  => ['required', 'url', 'max:2048'],
    ];

    /**
     * Akses "biasa" (default mahasiswa/dosen) hanya bisa melihat & mengisi
     * rekognisinya sendiri. Akses "penuh"/"readonly" melihat & mengelola
     * data semua orang.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing(['mahasiswa', 'dosen']);
        $ownScope = $user->menuLevel('rekognisi') === 'biasa';
        // Mahasiswa/alumni/dosen dengan akses "biasa" mendapat tampilan sederhana "Rekognisi Saya".
        // Hanya data mahasiswa/alumni yang melewati antrean verifikasi Prodi (dosen langsung disetujui).
        $selfView = $this->isSelfServiceView($request, $ownScope);
        $verify = $this->needsVerification($request);
        $status = $request->query('status');

        $tipe_user = $request->query('tipe_user');
        $jenis = $request->query('jenis');
        $skala = $request->query('skala');
        $term = trim((string) $request->query('q', ''));
        $focusId = $selfView ? $this->requestedFocusId($request) : null;
        if ($focusId) {
            $status = null;
            $term = '';
        }

        $listQuery = function () use ($ownScope, $user, $tipe_user, $jenis, $skala, $term, $status) {
            $query = Rekognisi::with(['mahasiswa.user', 'dosen.user'])->latest()->orderByDesc('id');
            if ($ownScope) {
                $this->scopeToUserProfile($query, $user);
            }
            if ($tipe_user && $tipe_user !== 'semua') $this->applyUserTypeFilter($query, $tipe_user);
            if ($jenis && $jenis !== 'semua') $query->where('jenis', $jenis);
            if ($skala && $skala !== 'semua') $query->where('skala', $skala);
            $this->applyStatusFilter($query, $status);
            if ($term !== '') {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(function ($w) use ($like) {
                    $w->where('judul', 'like', $like)
                        ->orWhere('penulis', 'like', $like)
                        ->orWhere('nama_jurnal', 'like', $like)
                        ->orWhere('nama_konferensi', 'like', $like)
                        ->orWhere('nomor_issn_isbn', 'like', $like)
                        ->orWhere('nomor_paten', 'like', $like)
                        ->orWhere('mitra', 'like', $like)
                        ->orWhere('jabatan', 'like', $like)
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nim', 'like', $like)
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)))
                        ->orWhereHas('dosen', fn ($d) => $d->where('nidn', 'like', $like)
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)));
                });
            }
            return $query;
        };

        $focusPage = $focusId ? $this->focusPage($listQuery(), $focusId, self::PER_PAGE, 'created_at') : null;
        $page = $focusPage ?? max(1, (int) $request->query('page', 1));
        $items = $listQuery()->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();
        // Halaman di luar jangkauan (mis. setelah menghapus data terakhir di halaman itu): mundur ke halaman terakhir.
        if ($items->lastPage() > 0 && $items->currentPage() > $items->lastPage()) {
            $items = $listQuery()->paginate(self::PER_PAGE, ['*'], 'page', $items->lastPage())->withQueryString();
        }

        $payload = [
            'items' => $items->getCollection()->map(fn ($i) => $this->format($i))->values(),
            'meta'  => [
                'page'  => $items->currentPage(),
                'last'  => max(1, $items->lastPage()),
                'total' => $items->total(),
                'from'  => $items->firstItem() ?? 0,
                'to'    => $items->lastItem() ?? 0,
            ],
        ];
        if ($selfView && $verify) {
            // Ringkasan per status (kartu filter di tampilan mahasiswa).
            $summaryQuery = Rekognisi::query();
            $this->scopeToUserProfile($summaryQuery, $user);
            $payload['summary'] = $this->statusSummary($summaryQuery);
        }

        // Request AJAX (tombol halaman / filter / cari): balas JSON data baris + meta halaman.
        // Baris digambar JS (buildRowHTML / MhsView) di blade, jadi tidak perlu partial.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($payload);
        }

        if ($selfView) {
            return view('mahasiswa.rekognisi', [
                'initial'   => $payload,
                'summary'   => $payload['summary'] ?? null,
                'ownerType' => $this->userTypeFor($user), // mahasiswa | alumni | dosen
                'verify'    => $verify,
            ]);
        }

        $userList = $ownScope ? $this->ownerOptionsFor($user) : $this->ownerOptionsForAll();

        $statsQuery = fn () => $ownScope
            ? tap(Rekognisi::query(), fn (Builder $query) => $this->scopeToUserProfile($query, $user))
            : Rekognisi::query();

        $alumniStatsQuery = clone $statsQuery();
        $this->applyUserTypeFilter($alumniStatsQuery, 'alumni');

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'nasional'      => (clone $statsQuery())->where('skala', 'nasional')->count(),
            'internasional' => (clone $statsQuery())->where('skala', 'internasional')->count(),
            'alumni'        => $alumniStatsQuery->count(),
        ];

        return view('lppm_rekognisi', compact('items', 'userList', 'stats') + [
            'jenisByTipe' => self::JENIS_BY_TIPE,
            'jenisOptions'        => $this->sortedOptions(self::JENIS_LABELS),
            'skalaOptions'        => $this->sortedOptions(self::SKALA_LABELS),
            'statusPatenOptions'  => $this->sortedOptions(self::STATUS_PATEN_LABELS),
            'jenisHkiOptions'     => $this->sortedOptions(self::JENIS_HKI_LABELS),
            'kategoriBukuOptions' => $this->sortedOptions(self::KATEGORI_BUKU_LABELS),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $duplicates = DuplicateDetector::for('rekognisi')->check($data);
        if ($blocked = DuplicateDetector::guard($request, $duplicates)) {
            return $blocked;
        }

        // Rekognisi milik mahasiswa yang diisi mahasiswa sendiri -> antrean persetujuan dosen wali.
        $owner = $data['mahasiswa_id'] ? Mahasiswa::find($data['mahasiswa_id']) : null;
        $data += Approval::attributesFor($request->user(), $owner, true);

        $item = Rekognisi::create($data)->load(['mahasiswa.user', 'dosen.user']);

        Approval::afterSubmit('rekognisi', $item, $request->user(), null);
        $this->notifyRekognisi($request, $item, 'ditambahkan');

        return response()->json(['success' => true, 'data' => $this->format($item), ...DuplicateDetector::warnings($duplicates)]);
    }

    public function update(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);

        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).
        $request->merge(['owner_ref' => $rekognisi->owner_ref, 'user_id' => null]);

        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $duplicates = DuplicateDetector::for('rekognisi')->check($data, $rekognisi->id);
        if ($blocked = DuplicateDetector::guard($request, $duplicates)) {
            return $blocked;
        }

        // Edit oleh mahasiswa mengembalikan data ke antrean persetujuan (admin/dosen tidak mengubah status).
        $previous = $rekognisi->approval_status;
        $data += Approval::attributesFor($request->user(), $rekognisi->mahasiswa, false);

        $rekognisi->update($data);
        $rekognisi->load(['mahasiswa.user', 'dosen.user']);

        Approval::afterSubmit('rekognisi', $rekognisi, $request->user(), $previous);
        $this->notifyRekognisi($request, $rekognisi, 'diperbarui');

        return response()->json(['success' => true, 'data' => $this->format($rekognisi), ...DuplicateDetector::warnings($duplicates)]);
    }

    public function destroy(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);

        $rekognisi->loadMissing(['mahasiswa.user', 'dosen.user']);
        $this->notifyRekognisi($request, $rekognisi, 'dihapus');

        $id = $rekognisi->id;
        $rekognisi->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    /** Hapus data terpilih (centang baris tabel) — dipanggil dari BulkSelectionController::hapus(). */
    public function destroyMany(Request $request)
    {
        return $this->bulkDestroySelected(
            $request,
            Rekognisi::class,
            ['mahasiswa.user', 'dosen.user'],
            fn (Rekognisi $item) => $this->notifyRekognisi($request, $item, 'dihapus')
        );
    }

    /**
     * Update massal data terpilih — dipanggil dari BulkSelectionController::ubah().
     * Modal menampilkan kolom metadata sesuai Jenis Karya yang dipilih, jadi jenis bisa diganti massal
     * dengan mengisi kolom yang dibutuhkan jenis baru. Kolom khas per baris (NIM/NIDN, tanggal, bukti, penulis)
     * tidak ikut. Kolom kosong = tidak diubah.
     */
    public function updateMany(Request $request)
    {
        return $this->bulkUpdateSelected(
            $request,
            Rekognisi::class,
            ['mahasiswa.user', 'dosen.user'],
            [
                'judul'           => ['nullable', 'string', 'max:255'],
                'jenis'           => ['nullable', 'in:jurnal,hki,book,sinta,conference,prosiding,paten'],
                'skala'           => ['nullable', 'in:nasional,internasional'],
                'mitra'           => ['nullable', 'string', 'max:255'],
                'jabatan'         => ['nullable', 'string', 'max:255'],
                'nama_jurnal'     => ['nullable', 'string', 'max:255'],
                'peringkat'       => ['nullable', 'string', 'max:100'],
                'link_doi'        => ['nullable', 'url', 'max:2048'],
                'jenis_hki'       => ['nullable', 'in:hak_cipta,paten,merek'],
                'kategori_buku'   => ['nullable', 'in:ajar,referensi,chapter'],
                'nama_konferensi' => ['nullable', 'string', 'max:255'],
                'nomor_issn_isbn' => ['nullable', 'string', 'max:100'],
                'status_paten'    => ['nullable', 'in:terdaftar,granted'],
                'nomor_paten'     => ['nullable', 'string', 'max:100'],
            ],
            fn (Rekognisi $item) => $this->notifyRekognisi($request, $item, 'diperbarui'),
            fn (Rekognisi $item) => $this->format($item),
            fn (Rekognisi $item, array $changes) => $this->resolveBulkRow($item, $changes)
        );
    }

    /**
     * Validasi silang satu baris: nilai akhir = data lama + kolom yang diisi, aturan sama dengan validated().
     * Bila jenis diganti, metadata wajib jenis baru boleh berasal dari isian modal ATAU data lama baris itu.
     *
     * @return array{0: array, 1: string[]}  [atribut yang disimpan, pesan error]
     */
    private function resolveBulkRow(Rekognisi $item, array $changes): array
    {
        $type = $item->tipe_user_efektif;
        $kindChanged = isset($changes['jenis']) && $changes['jenis'] !== $item->jenis;
        if (!$kindChanged) unset($changes['jenis']);
        $jenis = (string) ($changes['jenis'] ?? $item->jenis);
        $usesScale = in_array($jenis, self::SCALABLE_KINDS, true);
        $kindFields = self::KIND_FIELDS[$jenis] ?? [];
        $allKindFields = array_unique(array_merge(...array_values(self::KIND_FIELDS)));
        $final = array_merge($item->only(array_merge(['skala', 'mitra', 'jabatan'], $allKindFields)), $changes);
        $errors = [];

        if ($kindChanged && !in_array($jenis, self::JENIS_BY_TIPE[$type] ?? [], true)) {
            $errors[] = 'Jenis ' . $jenis . ' tidak tersedia untuk tipe ' . $type . '.';
        }

        // skala: hanya untuk jenis yang memakai skala, dan wajib di sana
        if (array_key_exists('skala', $changes) && !$usesScale) {
            $errors[] = 'Skala tidak berlaku untuk jenis ' . $jenis . '.';
        }
        if ($kindChanged && $usesScale && blank($final['skala'])) {
            $errors[] = 'Skala wajib diisi untuk jenis ' . $jenis . '.';
        }

        if (array_key_exists('mitra', $changes) && $type === 'mahasiswa') {
            $errors[] = 'Mitra hanya untuk Dosen dan Alumni.';
        }
        if (array_key_exists('jabatan', $changes) && $type !== 'alumni') {
            $errors[] = 'Jabatan hanya boleh diisi untuk tipe Alumni.';
        }

        // kolom metadata yang diisi harus berlaku untuk jenis akhir baris ini
        foreach (array_intersect($allKindFields, array_keys($changes)) as $field) {
            if (!in_array($field, $kindFields, true)) {
                $errors[] = self::FIELD_LABELS[$field] . ' tidak berlaku untuk jenis ' . $jenis . '.';
            }
        }

        if ($kindChanged) {
            foreach (self::KIND_REQUIRED[$jenis] ?? [] as $field => $label) {
                if (blank($final[$field] ?? null)) {
                    $errors[] = $label . ' wajib diisi untuk jenis ' . $jenis . '.';
                }
            }
            // kosongkan metadata jenis lama yang tidak berlaku (sama seperti validated())
            foreach ($allKindFields as $field) {
                if (!in_array($field, $kindFields, true)) $changes[$field] = null;
            }
            if (!$usesScale) $changes['skala'] = null;
        }

        return [$changes, $errors];
    }

    // =====================================================================
    // UNGGAH / UNDUH MASSAL (CSV) — khusus admin & superadmin
    // Kerangka umumnya ada di Concerns\HandlesBulkData + Support\Csv;
    // di sini hanya bagian yang khas Rekognisi.
    // =====================================================================

    protected function bulkMenu(): string
    {
        return 'rekognisi';
    }

    protected function bulkOwnerWith(): array|string
    {
        return ['mahasiswa.user', 'dosen.user'];
    }

    private const SHEET = 'Data Rekognisi';

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
    }

    /** Alias judul kolom yang dikenali saat unggah (selain nama kolom persis di template). */
    protected function bulkExtraAliases(): array
    {
        return [
            'konferensi'              => 'nama_konferensi',
            'nama_seminar'            => 'nama_konferensi',
            'nama_konferensi_seminar' => 'nama_konferensi',
            'issn'                    => 'nomor_issn_isbn',
            'isbn'                    => 'nomor_issn_isbn',
            'issn_isbn'               => 'nomor_issn_isbn',
            'doi'                     => 'link_doi',
            'nomor_permohonan'        => 'nomor_paten',
            'nomor_permohonan_paten'  => 'nomor_paten',
            'no_paten'                => 'nomor_paten',
            'inventor'                => 'penulis',
            'inventor_penulis'        => 'penulis',
        ];
    }

    /**
     * Samakan isian Status Paten dari Excel/CSV ke nilai database.
     * Terima: terdaftar, filed, "Terdaftar (Filed)" -> terdaftar; granted, bersertifikat, "Granted (Bersertifikat)" -> granted.
     * Nilai lain dikembalikan apa adanya supaya ditolak validasi dengan pesan yang jelas.
     */
    private function normalizeStatusPaten(mixed $value): ?string
    {
        $key = Csv::normalizeKey($value);
        if ($key === '') return null;

        return match (true) {
            str_contains($key, 'granted'), str_contains($key, 'bersertifikat') => 'granted',
            str_contains($key, 'terdaftar'), str_contains($key, 'filed')       => 'terdaftar',
            default => $key,
        };
    }

    /** Unduh template Excel (.xlsx): sheet petunjuk + sheet data, dengan dropdown pilihan. */
    public function template(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        return $this->bulkXlsxTemplateResponse(
            'template-rekognisi.xlsx',
            'PETUNJUK IMPORT / EXPORT REKOGNISI',
            self::SHEET,
            [
                'id'              => ['required' => 'Tidak', 'example' => '', 'width' => 10, 'note' => 'KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.'],
                'nim_nidn'        => ['required' => 'Ya (data baru)', 'example' => '2210001', 'width' => 18, 'note' => 'NIM mahasiswa / NIDN dosen. Profil dapat dibuat otomatis tanpa akun. Pemilik data tidak bisa diganti saat edit.'],
                'tipe_user'       => ['required' => 'Jika profil belum ada', 'example' => 'mahasiswa', 'width' => 16, 'options' => ['mahasiswa', 'dosen', 'alumni'], 'note' => 'Jenis pemilik, wajib untuk NIM/NIDN yang belum terdaftar.'],
                'jenis'           => ['required' => 'Ya', 'example' => 'jurnal', 'width' => 16, 'options' => ['jurnal', 'hki', 'book', 'sinta', 'conference', 'prosiding', 'paten'], 'note' => 'Jenis karya, terpisah dari skala. prosiding & paten hanya untuk Dosen.'],
                'skala'           => ['required' => 'Untuk publikasi', 'example' => 'internasional', 'width' => 18, 'options' => ['nasional', 'internasional'], 'note' => 'Skala publikasi.'],
                'judul'           => ['required' => 'Dosen/Mahasiswa', 'example' => 'Judul karya', 'width' => 34, 'note' => 'Judul karya atau publikasi.'],
                'penulis'         => ['required' => 'Dosen/Mahasiswa', 'example' => 'Nama penulis', 'width' => 28, 'note' => 'Nama penulis.'],
                'nama_jurnal'     => ['required' => 'Tidak', 'example' => '', 'width' => 28, 'note' => 'Diisi untuk publikasi jurnal.'],
                'peringkat'       => ['required' => 'Tidak', 'example' => '', 'width' => 14, 'note' => 'Contoh: Q2 atau S3.'],
                'jenis_hki'       => ['required' => 'Jika jenis = hki', 'example' => 'hak_cipta', 'width' => 18, 'options' => ['hak_cipta', 'paten', 'merek'], 'note' => 'Jenis HKI.'],
                'kategori_buku'   => ['required' => 'Jika jenis = book', 'example' => 'ajar', 'width' => 18, 'options' => ['ajar', 'referensi', 'chapter'], 'note' => 'Kategori buku.'],
                'link_doi'        => ['required' => 'Tidak', 'example' => '', 'width' => 46, 'note' => 'Link DOI. Wajib untuk prosiding, opsional untuk jurnal/SINTA.'],
                'nama_konferensi' => ['required' => 'Jika jenis = prosiding', 'example' => '', 'width' => 34, 'note' => 'Nama konferensi / seminar.'],
                'nomor_issn_isbn' => ['required' => 'Jika jenis = prosiding', 'example' => '', 'width' => 22, 'note' => 'Nomor ISSN / ISBN prosiding. Boleh pakai tanda hubung (mis. 2345-6789).'],
                'status_paten'    => ['required' => 'Jika jenis = paten', 'example' => '', 'width' => 18, 'options' => ['terdaftar', 'granted'], 'note' => 'terdaftar = Terdaftar (Filed), granted = Granted (Bersertifikat). Tulisan "Filed" / "Bersertifikat" juga diterima.'],
                'nomor_paten'     => ['required' => 'Jika jenis = paten', 'example' => '', 'width' => 26, 'note' => 'Nomor permohonan / paten.'],
                'tahun'           => ['required' => 'Dosen/Mahasiswa', 'example' => 2026, 'width' => 12, 'note' => 'Tahun publikasi/karya.'],
                'mitra'           => ['required' => 'Dosen/Alumni', 'example' => 'PT Teknologi Nusantara', 'width' => 34, 'note' => 'Wajib untuk Dosen dan Alumni, opsional untuk Mahasiswa.'],
                'jabatan'         => ['required' => 'Alumni', 'example' => '', 'width' => 26, 'note' => 'Jabatan alumni di mitra.'],
                'tanggal_mulai'   => ['required' => 'Ya', 'example' => '2026-03-01', 'width' => 18, 'note' => 'Tanggal mulai kegiatan / rekognisi.'],
                'tanggal_selesai' => ['required' => 'Ya', 'example' => '2026-03-03', 'width' => 18, 'note' => 'Tanggal selesai. Tidak boleh sebelum tanggal_mulai.'],
                'bukti_kegiatan'  => ['required' => 'Ya', 'example' => 'https://drive.google.com/...', 'width' => 46, 'note' => 'Link lengkap diawali https://'],
                'bukti_tambahan'  => ['required' => 'Tidak', 'example' => '', 'width' => 46, 'note' => 'Link bukti tambahan (opsional). Satu bukti per baris (Alt+Enter di sel yang sama), format: Nama bukti | https://link  (nama boleh dikosongkan, cukup link).'],
            ],
            [
                'Isi data di sheet "Data Rekognisi", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk data baru, isi id (dari hasil Download) untuk mengedit data.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'Format tanggal: 2026-09-30 atau 30/09/2026. Kolom "nama" otomatis diambil dari akun bila tersedia.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh seluruh data rekognisi (bisa diedit lalu diunggah ulang). */
    public function export(Request $request)
    {
        $this->bulkEnsureAccess($request, false);

        return $this->bulkXlsxExportResponse(
            'rekognisi-' . now()->format('Ymd-His') . '.xlsx',
            self::SHEET,
            ['id', 'nim_nidn', 'nama', 'tipe_user', 'jenis', 'skala', 'judul', 'penulis', 'nama_jurnal', 'peringkat', 'jenis_hki', 'kategori_buku', 'link_doi', 'nama_konferensi', 'nomor_issn_isbn', 'status_paten', 'nomor_paten', 'tahun', 'mitra', 'jabatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan', 'bukti_tambahan'],
            [10, 18, 28, 16, 16, 18, 34, 28, 28, 14, 18, 18, 46, 34, 22, 18, 26, 12, 34, 26, 18, 18, 46, 46],
            Rekognisi::class,
            fn (Rekognisi $item) => [
                $item->id,
                $item->owner_identifier ?? '',
                $item->owner_name,
                $item->tipe_user_efektif,
                $item->jenis,
                $item->skala,
                $item->judul,
                $item->penulis,
                $item->nama_jurnal,
                $item->peringkat,
                $item->jenis_hki,
                $item->kategori_buku,
                $item->link_doi,
                $item->nama_konferensi,
                $item->nomor_issn_isbn,
                $item->status_paten,
                $item->nomor_paten,
                $item->tahun,
                $item->mitra,
                $item->jabatan_efektif,
                optional($item->tanggal_mulai)->format('Y-m-d'),
                optional($item->tanggal_selesai)->format('Y-m-d'),
                $item->bukti_kegiatan,
                Rekognisi::formatBuktiTambahan($item->bukti_tambahan),
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

        $parsed = $this->bulkParseUpload($request, ['nim_nidn', 'jenis', 'judul', 'penulis', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan']);
        if ($parsed instanceof JsonResponse) {
            return $parsed;
        }
        [, $rows] = $parsed;

        $existingById = $this->bulkExistingById(Rekognisi::class, $rows);
        $profiles = Mahasiswa::with('user')->get()->concat(Dosen::with('user')->get());
        $newOwners = [];
        $newOwnerTokens = [];
        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $d = $row['data'];
            $rowErrors = [];
            $id = trim((string) ($d['id'] ?? ''));
            $existing = $id !== '' && ctype_digit($id) ? $existingById->get((int) $id) : null;
            if ($id !== '' && !$existing) {
                $rowErrors[] = "id \"{$id}\" tidak ditemukan.";
            }

            $identifier = trim((string) ($d['nim_nidn'] ?? ''));
            $typeHint = Csv::normalizeKey($d['tipe_user'] ?? '');
            $owner = null;
            if ($existing) {
                $owner = $existing->owner_profile;
                if (!$owner) {
                    $rowErrors[] = "Pemilik Rekognisi #{$existing->id} tidak memiliki profil yang valid.";
                } elseif ($identifier !== '' && $identifier !== $existing->owner_identifier) {
                    $rowErrors[] = 'Pemilik data tidak bisa diganti saat edit (NIM/NIDN berbeda dari data aslinya).';
                }
            } elseif ($identifier === '') {
                $rowErrors[] = 'nim_nidn wajib diisi untuk data baru.';
            } else {
                [$owner, $ambiguous] = $this->findImportOwner($profiles, $identifier, $typeHint);
                if ($ambiguous) {
                    $rowErrors[] = "NIM/NIDN \"{$identifier}\" cocok dengan lebih dari satu profil; isi kolom tipe_user.";
                } elseif (!$owner) {
                    if (!in_array($typeHint, ['mahasiswa', 'alumni', 'dosen'], true)) {
                        $rowErrors[] = "Profil untuk NIM/NIDN \"{$identifier}\" belum terdaftar; isi tipe_user agar profil dapat dibuat.";
                    } else {
                        $ownerKey = $typeHint . ':' . $identifier;
                        $owner = $newOwners[$ownerKey] ??= $this->newImportOwner($identifier, $typeHint);
                    }
                }
            }

            if ($owner && $typeHint !== '') {
                $actualType = $this->typeForProfile($owner);
                if ($typeHint !== $actualType && !($typeHint === 'mahasiswa' && $actualType === 'alumni')) {
                    $rowErrors[] = "tipe_user tidak sesuai dengan profil {$identifier}.";
                }
            }

            $jenis = Csv::normalizeKey($d['jenis'] ?? '');
            $skala = Csv::normalizeKey($d['skala'] ?? '');
            $tipeUser = $owner ? $this->typeForProfile($owner) : ($existing?->tipe_user_efektif ?? '');
            [$dates, $badDates] = $this->bulkParseDates($d, ['tanggal_mulai', 'tanggal_selesai'], $rowErrors);

            $payload = [
                'mahasiswa_id'   => $owner instanceof Mahasiswa ? ($owner->exists ? $owner->id : null) : null,
                'dosen_id'       => $owner instanceof Dosen ? ($owner->exists ? $owner->id : null) : null,
                'tipe_user'      => $tipeUser,
                'jenis'          => $jenis,
                'skala'          => $skala ?: null,
                'judul'          => trim((string) ($d['judul'] ?? '')) ?: null,
                'penulis'        => trim((string) ($d['penulis'] ?? '')) ?: null,
                'nama_jurnal'    => trim((string) ($d['nama_jurnal'] ?? '')) ?: null,
                'peringkat'      => trim((string) ($d['peringkat'] ?? '')) ?: null,
                'jenis_hki'      => Csv::normalizeKey($d['jenis_hki'] ?? '') ?: null,
                'kategori_buku'  => Csv::normalizeKey($d['kategori_buku'] ?? '') ?: null,
                'link_doi'       => trim((string) ($d['link_doi'] ?? '')) ?: null,
                'nama_konferensi' => trim((string) ($d['nama_konferensi'] ?? '')) ?: null,
                'nomor_issn_isbn' => trim((string) ($d['nomor_issn_isbn'] ?? '')) ?: null,
                'status_paten'   => $this->normalizeStatusPaten($d['status_paten'] ?? ''),
                'nomor_paten'    => trim((string) ($d['nomor_paten'] ?? '')) ?: null,
                'tahun'          => trim((string) ($d['tahun'] ?? '')) ?: null,
                'mitra'          => trim((string) ($d['mitra'] ?? '')) ?: null,
                'jabatan'        => trim((string) ($d['jabatan'] ?? '')) ?: null,
                'bukti_kegiatan' => trim((string) ($d['bukti_kegiatan'] ?? '')),
                'bukti_tambahan' => Rekognisi::parseBuktiTambahan((string) ($d['bukti_tambahan'] ?? '')),
            ] + $dates;

            [$messages] = $this->bulkValidate($payload, self::RULES, $badDates);
            $rowErrors = array_merge($rowErrors, $messages);

            $allowedKinds = self::JENIS_BY_TIPE[$tipeUser] ?? [];
            if ($tipeUser === 'mahasiswa') $payload['mitra'] = null; // mahasiswa tidak punya Mitra
            // Kolom khusus jenis dikosongkan bila tidak relevan (sama seperti validated()).
            if (!in_array($jenis, ['jurnal', 'sinta', 'prosiding'], true)) $payload['link_doi'] = null;
            if ($jenis !== 'prosiding') { $payload['nama_konferensi'] = null; $payload['nomor_issn_isbn'] = null; }
            if ($jenis !== 'paten') { $payload['status_paten'] = null; $payload['nomor_paten'] = null; }
            if (!in_array($jenis, $allowedKinds, true)) {
                $rowErrors[] = 'Jenis karya tidak sesuai dengan tipe pengguna pada baris ini.';
            }
            if (in_array($jenis, self::SCALABLE_KINDS, true) && !$payload['skala']) {
                $rowErrors[] = 'Skala wajib diisi untuk jenis karya ini pada baris ini.';
            }
            if (in_array($tipeUser, ['dosen', 'alumni'], true) && blank($payload['mitra'])) {
                $rowErrors[] = 'Mitra wajib diisi untuk Dosen dan Alumni.';
            }
            if ($tipeUser === 'alumni' && blank($payload['jabatan'])) {
                $rowErrors[] = 'Jabatan wajib diisi untuk Alumni.';
            }

            if ($rowErrors) {
                $errors[] = ['row' => $row['line'], 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            if ($tipeUser !== 'alumni') {
                unset($payload['jabatan']);
            }
            if ($owner && !$owner->exists) {
                $ownerKey = ($owner instanceof Dosen ? 'dosen:' : 'mahasiswa:') . $this->profileIdentifier($owner);
                $newOwnerTokens[$ownerKey] ??= -count($newOwnerTokens) - 1;
                $payload[$owner instanceof Dosen ? 'dosen_id' : 'mahasiswa_id'] = $newOwnerTokens[$ownerKey];
            }

            $plan[] = ['existing' => $existing, 'payload' => $payload, 'owner' => $owner, 'row' => $row['line']];
        }

        if ($errors) {
            return $this->bulkRejected($errors);
        }

        $duplicates = DuplicateDetector::for('rekognisi')->checkBatch(array_map(static fn ($entry) => [
            'data' => $entry['payload'],
            'row' => $entry['row'],
            'ignore_id' => $entry['existing']?->id,
        ], $plan));
        if ($blocked = DuplicateDetector::guard($request, $duplicates)) {
            return $blocked;
        }

        $result = $this->saveImportedRows($plan);
        $this->bulkNotifyOwners($request, $result['perOwner'], 'rekognisi');

        return $this->bulkSuccess(
            $result['created'],
            $result['updated'],
            $result['unchanged'],
            $result['newOwners'],
            'profil pemilik',
            $duplicates['similar']
        );
    }

    // =====================================================================
    // HELPER CRUD
    // =====================================================================

    private function notifyRekognisi(Request $request, Rekognisi $item, string $aksi): void
    {
        $this->notifyOwner($request, $item->owner_user?->id, 'rekognisi', $item->judul ?: ($item->mitra ?: 'Rekognisi'), $aksi, $item->jenis, ['rekognisi_id' => $item->id]);
    }

    private function enforceOwnUser(Request $request, array &$data): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        $actor->loadMissing(['mahasiswa', 'dosen']);
        $profile = $actor->role === 'dosen' ? $actor->dosen : $actor->mahasiswa;
        abort_if(!$profile, 422, 'Profil kamu belum terhubung ke akun ini.');
        $data = array_merge($data, $this->ownerAttributes($profile));
    }

    private function authorizeOwnership(Request $request, Rekognisi $rekognisi): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        abort_if(
            ($actor->role === 'dosen'
                ? $rekognisi->dosen_id !== $actor->dosen?->id
                : $rekognisi->mahasiswa_id !== $actor->mahasiswa?->id),
            403,
            'Kamu hanya bisa mengelola data rekognisi milikmu sendiri.'
        );
    }

    private function validated(Request $request): array
    {
        $selector = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'owner_ref' => ['nullable', 'string', 'regex:/^(mahasiswa|dosen):[1-9][0-9]*$/'],
        ]);
        if (filled($selector['user_id'] ?? null) === filled($selector['owner_ref'] ?? null)) {
            throw ValidationException::withMessages(['owner_ref' => 'Pilih satu pemilik Rekognisi yang valid.']);
        }
        $profile = $this->resolveOwner($selector['owner_ref'] ?? null, $selector['user_id'] ?? null);
        $data = $request->validate(self::RULES);
        $data = array_merge($data, $this->ownerAttributes($profile));

        $allowedKinds = self::JENIS_BY_TIPE[$data['tipe_user']] ?? [];
        if (!in_array($data['jenis'], $allowedKinds, true)) {
            throw ValidationException::withMessages(['jenis' => 'Jenis karya tidak sesuai dengan tipe pengguna.']);
        }

        $scaleRequired = in_array($data['jenis'], self::SCALABLE_KINDS, true);
        if ($scaleRequired && blank($data['skala'])) {
            throw ValidationException::withMessages(['skala' => 'Skala wajib dipilih.']);
        }
        if (!$scaleRequired) {
            $data['skala'] = null;
        }
        if (!in_array($data['jenis'], ['jurnal', 'sinta'], true)) {
            $data['nama_jurnal'] = null;
            $data['peringkat'] = null;
        }
        // Link DOI dipakai jurnal, SINTA, dan prosiding.
        if (!in_array($data['jenis'], ['jurnal', 'sinta', 'prosiding'], true)) {
            $data['link_doi'] = null;
        }
        if ($data['jenis'] !== 'prosiding') {
            $data['nama_konferensi'] = null;
            $data['nomor_issn_isbn'] = null;
        }
        if ($data['jenis'] !== 'paten') {
            $data['status_paten'] = null;
            $data['nomor_paten'] = null;
        }
        // Mahasiswa tidak mengisi Mitra / Instansi.
        if ($data['tipe_user'] === 'mahasiswa') $data['mitra'] = null;
        if ($data['jenis'] !== 'hki') $data['jenis_hki'] = null;
        if ($data['jenis'] !== 'book') $data['kategori_buku'] = null;

        $conditionalErrors = [];
        if (in_array($data['tipe_user'], ['dosen', 'alumni'], true) && blank($data['mitra'] ?? null)) {
            $conditionalErrors['mitra'][] = 'Mitra wajib diisi untuk Dosen dan Alumni.';
        }
        if ($data['tipe_user'] === 'alumni' && blank($data['jabatan'] ?? null)) {
            $conditionalErrors['jabatan'][] = 'Jabatan wajib diisi untuk Alumni.';
        }
        if ($conditionalErrors) throw ValidationException::withMessages($conditionalErrors);
        if ($data['tipe_user'] !== 'alumni') {
            unset($data['jabatan']);
        }

        return $data;
    }

    private function format(Rekognisi $item): array
    {
        return [
            'id'              => $item->id,
            'user_id'         => $item->user_id,
            'owner_ref'       => $item->owner_ref,
            'tipe_user'       => $item->tipe_user_efektif,
            'nim_nidn'        => $item->owner_identifier ?? '-',
            'nama'            => $item->owner_name,
            'avatar_url'      => $item->owner_user?->avatar_url,
            'jenis'           => $item->jenis,
            'skala'           => $item->skala,
            'mitra'           => $item->mitra,
            'judul'           => $item->judul,
            'penulis'         => $item->penulis,
            'nama_jurnal'     => $item->nama_jurnal,
            'peringkat'       => $item->peringkat,
            'jenis_hki'       => $item->jenis_hki,
            'kategori_buku'   => $item->kategori_buku,
            'link_doi'        => $item->link_doi,
            'nama_konferensi' => $item->nama_konferensi,
            'nomor_issn_isbn' => $item->nomor_issn_isbn,
            'status_paten'    => $item->status_paten,
            'nomor_paten'     => $item->nomor_paten,
            'jabatan'         => $item->jabatan,          // nilai tersimpan (dipakai form edit untuk memulihkan isian)
            'jabatan_tampil'  => $item->jabatan_efektif,  // nilai yang ditampilkan di tabel (null bila bukan alumni)
            'tanggal_mulai'   => optional($item->tanggal_mulai)->format('Y-m-d'),
            'tanggal_selesai' => optional($item->tanggal_selesai)->format('Y-m-d'),
            'tahun'           => $item->tahun,
            'bukti_kegiatan'  => $item->bukti_kegiatan,
            'bukti_tambahan'  => $item->bukti_tambahan,
            'approval_status' => $item->approval_status,
            'approval_label'  => $item->approvalLabel(),
            'approval_badge'  => $item->approvalBadge(),
            'approval_note'   => $item->approval_note,
            'diajukan'        => optional($item->created_at)->format('d M Y'),
        ];
    }

    private function userTypeFor(User $user): string
    {
        if ($user->role === 'dosen') return 'dosen';
        if ($user->role === 'mahasiswa' && $user->mahasiswa?->status === 'lulus') return 'alumni';

        return 'mahasiswa';
    }

    private function applyUserTypeFilter(Builder $query, string $type): void
    {
        if ($type === 'dosen') {
            $query->whereNotNull('dosen_id');
            return;
        }

        if ($type === 'mahasiswa') {
            $query->whereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa->where('status', '!=', 'lulus'));
            return;
        }

        if ($type === 'alumni') {
            $query->whereHas('mahasiswa', fn ($mahasiswa) => $mahasiswa->where('status', 'lulus'));
        }
    }

    private function scopeToUserProfile(Builder $query, User $user): void
    {
        $user->loadMissing(['mahasiswa', 'dosen']);
        $profileId = $user->role === 'dosen' ? $user->dosen?->id : $user->mahasiswa?->id;
        $column = $user->role === 'dosen' ? 'dosen_id' : 'mahasiswa_id';

        $profileId
            ? $query->where($column, $profileId)
            : $query->whereRaw('1 = 0');
    }

    private function ownerOptionsForAll(): \Illuminate\Support\Collection
    {
        return Mahasiswa::with('user')->orderBy('nim')->get()
            ->map(fn (Mahasiswa $m) => $this->ownerOption($m))
            ->concat(Dosen::with('user')->orderBy('nidn')->get()->map(fn (Dosen $d) => $this->ownerOption($d)))
            ->values();
    }

    private function ownerOptionsFor(User $user): \Illuminate\Support\Collection
    {
        $profile = $user->role === 'dosen' ? $user->dosen : $user->mahasiswa;

        return $profile ? collect([$this->ownerOption($profile)]) : collect();
    }

    private function ownerOption(Mahasiswa|Dosen $profile): object
    {
        $isDosen = $profile instanceof Dosen;
        $role = $isDosen ? 'dosen' : 'mahasiswa';
        $status = $isDosen ? '' : ($profile->status ?? '');

        return (object) [
            'owner_ref' => $role . ':' . $profile->id,
            'nim_nidn' => $isDosen ? $profile->nidn : $profile->nim,
            'name' => $profile->user?->name ?? 'Belum ada akun',
            'role' => $role,
            'status' => $status,
        ];
    }

    private function findImportOwner(\Illuminate\Support\Collection $profiles, string $identifier, string $typeHint): array
    {
        $candidates = $profiles->filter(function (Mahasiswa|Dosen $profile) use ($identifier, $typeHint) {
            $isDosen = $profile instanceof Dosen;
            if ($typeHint === 'dosen' && !$isDosen) return false;
            if (in_array($typeHint, ['mahasiswa', 'alumni'], true) && $isDosen) return false;

            $stored = $this->profileIdentifier($profile);

            return $stored === $identifier
                || ltrim($stored, '0') === ltrim($identifier, '0');
        })->values();

        return [$candidates->count() === 1 ? $candidates->first() : null, $candidates->count() > 1];
    }

    private function newImportOwner(string $identifier, string $type): Mahasiswa|Dosen
    {
        if ($type === 'dosen') {
            return new Dosen(['nidn' => $identifier, 'status' => 'aktif']);
        }

        return new Mahasiswa([
            'nim' => $identifier,
            'status' => $type === 'alumni' ? 'lulus' : 'aktif',
        ]);
    }

    private function profileIdentifier(Mahasiswa|Dosen $profile): string
    {
        return $profile instanceof Dosen ? (string) $profile->nidn : (string) $profile->nim;
    }

    private function typeForProfile(Mahasiswa|Dosen $profile): string
    {
        return $profile instanceof Dosen
            ? 'dosen'
            : ($profile->status === 'lulus' ? 'alumni' : 'mahasiswa');
    }

    private function saveImportedRows(array $plan): array
    {
        $created = $updated = $unchanged = $newOwners = 0;
        $perOwner = [];
        $savedOwners = [];

        DB::transaction(function () use ($plan, &$created, &$updated, &$unchanged, &$newOwners, &$perOwner, &$savedOwners) {
            foreach ($plan as $entry) {
                /** @var Mahasiswa|Dosen $owner */
                $owner = $entry['owner'];
                if (!$owner->exists) {
                    $key = spl_object_id($owner);
                    if (!isset($savedOwners[$key])) {
                        $owner->save();
                        $savedOwners[$key] = true;
                        $newOwners++;
                    }
                }

                $payload = $entry['payload'];
                $ownerColumn = $owner instanceof Dosen ? 'dosen_id' : 'mahasiswa_id';
                $payload[$ownerColumn] = $owner->id;

                if ($entry['existing']) {
                    unset($payload['mahasiswa_id'], $payload['dosen_id'], $payload['tipe_user']);
                    $entry['existing']->fill($payload);
                    if ($entry['existing']->isDirty()) {
                        $entry['existing']->save();
                        $updated++;
                        if ($owner->user_id) {
                            $perOwner[$owner->user_id]['updated'] = ($perOwner[$owner->user_id]['updated'] ?? 0) + 1;
                        }
                    } else {
                        $unchanged++;
                    }
                } else {
                    Rekognisi::create($payload);
                    $created++;
                    if ($owner->user_id) {
                        $perOwner[$owner->user_id]['created'] = ($perOwner[$owner->user_id]['created'] ?? 0) + 1;
                    }
                }
            }
        });

        return compact('created', 'updated', 'unchanged', 'newOwners', 'perOwner');
    }

    private function resolveOwner(?string $ownerRef, ?int $userId): Mahasiswa|Dosen
    {
        if ($ownerRef !== null) {
            [$type, $id] = explode(':', $ownerRef, 2);
            $profile = $type === 'dosen'
                ? Dosen::with('user')->find($id)
                : Mahasiswa::with('user')->find($id);
            if ($profile) return $profile;
        } elseif ($userId !== null) {
            $user = User::with(['mahasiswa', 'dosen'])->findOrFail($userId);
            $profile = $user->role === 'dosen' ? $user->dosen : ($user->role === 'mahasiswa' ? $user->mahasiswa : null);
            if ($profile) return $profile;
        }

        throw ValidationException::withMessages([
            'owner_ref' => 'Pemilik harus memiliki profil mahasiswa atau dosen yang terdaftar.',
        ]);
    }

    private function ownerAttributes(Mahasiswa|Dosen $profile): array
    {
        if ($profile instanceof Dosen) {
            return ['mahasiswa_id' => null, 'dosen_id' => $profile->id, 'tipe_user' => 'dosen'];
        }

        return [
            'mahasiswa_id' => $profile->id,
            'dosen_id' => null,
            'tipe_user' => $profile->status === 'lulus' ? 'alumni' : 'mahasiswa',
        ];
    }
}