<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use App\Models\Rekognisi;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use App\Support\Approval;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class LppmRekognisiController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;

    /** Jumlah baris per halaman (disamakan dengan Data Master Pengguna). */
    private const PER_PAGE = 50;

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private const RULES = [
        'user_id'         => ['required', 'exists:users,id'],
        'jenis'           => ['required', 'in:jurnal,hki,book,sinta,conference'],
        'skala'           => ['nullable', 'required_if:jenis,jurnal,sinta,conference', 'in:nasional,internasional'],
        'mitra'           => ['nullable', 'string', 'max:255'],
        'judul'           => ['required', 'string', 'max:255'],
        'penulis'         => ['required', 'string', 'max:255'],
        'nama_jurnal'     => ['nullable', 'required_if:jenis,jurnal', 'required_if:jenis,sinta', 'string', 'max:255'],
        'peringkat'       => ['nullable', 'required_if:jenis,jurnal', 'required_if:jenis,sinta', 'string', 'max:100'],
        'jenis_hki'       => ['nullable', 'required_if:jenis,hki', 'in:hak_cipta,paten,merek'],
        'kategori_buku'   => ['nullable', 'required_if:jenis,book', 'in:ajar,referensi,chapter'],
        'link_doi'        => ['nullable', 'url', 'max:2048'],
        'jabatan'         => ['nullable', 'string', 'max:255'],
        'tanggal_mulai'   => ['required', 'date'],
        'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        'tahun'           => ['required', 'integer', 'between:2000,2100'],
        'bukti_kegiatan'  => ['required', 'url', 'max:2048'],
        'bukti_tambahan'  => ['nullable', 'url', 'max:2048'],
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

        $tipe_user = $request->query('tipe_user');
        $requestedJenis = $request->query('jenis');
        $jenis = in_array($requestedJenis, ['jurnal', 'hki', 'book', 'sinta', 'conference'], true)
            ? $requestedJenis
            : null;
        $skala = $request->query('skala');
        $term = trim((string) $request->query('q', ''));

        $listQuery = function () use ($ownScope, $user, $tipe_user, $jenis, $skala, $term) {
            $query = Rekognisi::with('user.mahasiswa')->latest()->orderByDesc('id');
            if ($ownScope) {
                $query->where('user_id', $user->id);
            }
            if ($tipe_user && $tipe_user !== 'semua') $this->applyUserTypeFilter($query, $tipe_user);
            if ($jenis && $jenis !== 'semua') $query->where('jenis', $jenis);
            if ($skala && $skala !== 'semua') $query->where('skala', $skala);
            if ($term !== '') {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(function ($w) use ($like) {
                    $w->where('judul', 'like', $like)
                        ->orWhere('penulis', 'like', $like)
                        ->orWhere('nama_jurnal', 'like', $like)
                        ->orWhere('mitra', 'like', $like)
                        ->orWhere('jabatan', 'like', $like)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('nim_nidn', 'like', $like));
                });
            }
            return $query;
        };

        $items = $listQuery()->paginate(self::PER_PAGE)->withQueryString();
        // Halaman di luar jangkauan (mis. setelah menghapus data terakhir di halaman itu): mundur ke halaman terakhir.
        if ($items->lastPage() > 0 && $items->currentPage() > $items->lastPage()) {
            $items = $listQuery()->paginate(self::PER_PAGE, ['*'], 'page', $items->lastPage())->withQueryString();
        }

        // Request AJAX (tombol halaman / filter / cari): balas JSON data baris + meta halaman.
        // Baris digambar JS (buildRowHTML) di blade, jadi tidak perlu partial.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'items' => $items->getCollection()->map(fn ($i) => $this->format($i))->values(),
                'meta'  => [
                    'page'  => $items->currentPage(),
                    'last'  => max(1, $items->lastPage()),
                    'total' => $items->total(),
                    'from'  => $items->firstItem() ?? 0,
                    'to'    => $items->lastItem() ?? 0,
                ],
            ]);
        }

        $userList = $ownScope
            ? collect([$user])
            : User::with(['mahasiswa', 'dosen'])->whereIn('role', ['dosen', 'mahasiswa'])->orderBy('name')->get();

        $statsQuery = fn () => $ownScope
            ? Rekognisi::where('user_id', $user->id)
            : Rekognisi::query();

        $alumniStatsQuery = clone $statsQuery();
        $this->applyUserTypeFilter($alumniStatsQuery, 'alumni');

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'nasional'      => (clone $statsQuery())->where('skala', 'nasional')->count(),
            'internasional' => (clone $statsQuery())->where('skala', 'internasional')->count(),
            'alumni'        => $alumniStatsQuery->count(),
        ];

        return view('lppm_rekognisi', compact('items', 'userList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        // Rekognisi milik mahasiswa yang diisi mahasiswa sendiri -> antrean persetujuan dosen wali.
        $owner = ($data['tipe_user'] ?? null) === 'mahasiswa' ? User::find($data['user_id'])?->mahasiswa : null;
        $data += Approval::attributesFor($request->user(), $owner, true);

        $item = Rekognisi::create($data)->load('user.mahasiswa');

        Approval::afterSubmit('rekognisi', $item, $request->user(), null);
        $this->notifyRekognisi($request, $item, 'ditambahkan');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);
        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).
        $request->merge(['user_id' => $rekognisi->user_id]);

        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        // Edit oleh mahasiswa mengembalikan data ke antrean persetujuan (admin/dosen tidak mengubah status).
        $previous = $rekognisi->approval_status;
        $data += Approval::attributesFor($request->user(), $rekognisi->user?->mahasiswa, false);

        $rekognisi->update($data);
        $rekognisi->load('user.mahasiswa');

        Approval::afterSubmit('rekognisi', $rekognisi, $request->user(), $previous);
        $this->notifyRekognisi($request, $rekognisi, 'diperbarui');

        return response()->json(['success' => true, 'data' => $this->format($rekognisi)]);
    }

    public function destroy(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);

        $rekognisi->loadMissing('user');
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
            ['user'],
            fn (Rekognisi $item) => $this->notifyRekognisi($request, $item, 'dihapus')
        );
    }

    /**
     * Update massal data terpilih — dipanggil dari BulkSelectionController::ubah().
     * Kolom yang khas per baris (NIM/NIDN, user, tanggal, bukti) sengaja TIDAK bisa diubah massal.
     * Kolom yang dikosongkan tidak diubah.
     */
    public function updateMany(Request $request)
    {
        return $this->bulkUpdateSelected(
            $request,
            Rekognisi::class,
            ['user'],
            [
                'jenis'   => ['nullable', 'in:jurnal,hki,book,sinta,conference'],
                'skala'   => ['nullable', 'in:nasional,internasional'],
                'mitra'   => ['nullable', 'string', 'max:255'],
                'jabatan' => ['nullable', 'string', 'max:255'],
            ],
            fn (Rekognisi $item) => $this->notifyRekognisi($request, $item, 'diperbarui'),
            fn (Rekognisi $item) => $this->format($item),
            fn (Rekognisi $item, array $changes) => $this->resolveBulkRow($item, $changes)
        );
    }

    /**
     * Validasi silang satu baris untuk update massal (nilai akhir = data lama + kolom yang diisi):
    * Jabatan hanya dapat diperbarui untuk tipe Alumni.
     *
     * @return array{0: array, 1: string[]}
     */
    private function resolveBulkRow(Rekognisi $item, array $changes): array
    {
        $jabatan = $changes['jabatan'] ?? $item->jabatan;
        $errors = [];

        $allowedKinds = match ($item->tipe_user_efektif) {
            'dosen' => ['jurnal', 'hki', 'book'],
            'mahasiswa' => ['sinta', 'conference', 'jurnal'],
            'alumni' => ['sinta', 'conference', 'jurnal'],
            default => [],
        };
        $jenis = (string) ($changes['jenis'] ?? $item->jenis);
        if (array_key_exists('jenis', $changes) && !in_array($jenis, $allowedKinds, true)) {
            $errors[] = 'Jenis karya tidak sesuai dengan tipe pengguna ' . $item->tipe_user . '.';
        }

        if ($item->tipe_user_efektif === 'alumni') {
            if (trim((string) $jabatan) === '') {
                $errors[] = 'Jabatan wajib diisi untuk tipe Alumni.';
            }
        } else {
            if (array_key_exists('jabatan', $changes)) {
                $errors[] = 'Jabatan hanya boleh diisi untuk tipe Alumni.';
            }
        }

        if (array_key_exists('skala', $changes) && !in_array($jenis, ['jurnal', 'sinta', 'conference'], true)) {
            $errors[] = 'Skala hanya berlaku untuk karya jurnal, SINTA, atau conference.';
        }
        $finalScale = $changes['skala'] ?? $item->skala;
        if (in_array($jenis, ['jurnal', 'sinta', 'conference'], true) && blank($finalScale)) {
            $errors[] = 'Skala wajib diisi untuk jenis karya ini.';
        }
        if (in_array($jenis, ['jurnal', 'sinta'], true)) {
            foreach (['nama_jurnal', 'peringkat'] as $field) {
                if (blank($item->{$field})) $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi untuk jenis jurnal.';
            }
        }
        if ($jenis === 'hki' && blank($item->jenis_hki)) $errors[] = 'Jenis HKI wajib diisi.';
        if ($jenis === 'book' && blank($item->kategori_buku)) $errors[] = 'Kategori buku wajib diisi.';
        if (in_array($item->tipe_user_efektif, ['dosen', 'alumni'], true)
            && array_key_exists('mitra', $changes) && blank($changes['mitra'])) {
            $errors[] = 'Mitra wajib diisi untuk Dosen dan Alumni.';
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

    private const SHEET = 'Data Rekognisi';

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
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
                'nim_nidn'        => ['required' => 'Ya (data baru)', 'example' => '2210001', 'width' => 18, 'note' => 'NIM mahasiswa / NIDN dosen yang sudah terdaftar. Pemilik data tidak bisa diganti saat edit.'],
                'jenis'           => ['required' => 'Ya', 'example' => 'jurnal', 'width' => 16, 'options' => ['jurnal', 'hki', 'book', 'sinta', 'conference'], 'note' => 'Jenis karya, terpisah dari skala.'],
                'skala'           => ['required' => 'Untuk publikasi', 'example' => 'internasional', 'width' => 18, 'options' => ['nasional', 'internasional'], 'note' => 'Skala publikasi.'],
                'judul'           => ['required' => 'Dosen/Mahasiswa', 'example' => 'Judul karya', 'width' => 34, 'note' => 'Judul karya atau publikasi.'],
                'penulis'         => ['required' => 'Dosen/Mahasiswa', 'example' => 'Nama penulis', 'width' => 28, 'note' => 'Nama penulis.'],
                'nama_jurnal'     => ['required' => 'Tidak', 'example' => '', 'width' => 28, 'note' => 'Diisi untuk publikasi jurnal.'],
                'peringkat'       => ['required' => 'Tidak', 'example' => '', 'width' => 14, 'note' => 'Contoh: Q2 atau S3.'],
                'jenis_hki'       => ['required' => 'Jika jenis = hki', 'example' => 'hak_cipta', 'width' => 18, 'options' => ['hak_cipta', 'paten', 'merek'], 'note' => 'Jenis HKI.'],
                'kategori_buku'   => ['required' => 'Jika jenis = book', 'example' => 'ajar', 'width' => 18, 'options' => ['ajar', 'referensi', 'chapter'], 'note' => 'Kategori buku.'],
                'link_doi'        => ['required' => 'Tidak', 'example' => '', 'width' => 46, 'note' => 'Link DOI (opsional).'],
                'tahun'           => ['required' => 'Dosen/Mahasiswa', 'example' => 2026, 'width' => 12, 'note' => 'Tahun publikasi/karya.'],
                'mitra'           => ['required' => 'Dosen/Alumni', 'example' => 'PT Teknologi Nusantara', 'width' => 34, 'note' => 'Wajib untuk Dosen dan Alumni, opsional untuk Mahasiswa.'],
                'jabatan'         => ['required' => 'Alumni', 'example' => '', 'width' => 26, 'note' => 'Jabatan alumni di mitra.'],
                'tanggal_mulai'   => ['required' => 'Ya', 'example' => '2026-03-01', 'width' => 18, 'note' => 'Tanggal mulai kegiatan / rekognisi.'],
                'tanggal_selesai' => ['required' => 'Ya', 'example' => '2026-03-03', 'width' => 18, 'note' => 'Tanggal selesai. Tidak boleh sebelum tanggal_mulai.'],
                'bukti_kegiatan'  => ['required' => 'Ya', 'example' => 'https://drive.google.com/...', 'width' => 46, 'note' => 'Link lengkap diawali https://'],
                'bukti_tambahan'  => ['required' => 'Tidak', 'example' => '', 'width' => 46, 'note' => 'Link bukti tambahan (opsional).'],
            ],
            [
                'Isi data di sheet "Data Rekognisi", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk data baru, isi id (dari hasil Download) untuk mengedit data.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'Format tanggal: 2026-09-30 atau 30/09/2026. Kolom "nama" otomatis diambil dari NIM/NIDN.',
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
            ['id', 'nim_nidn', 'nama', 'tipe_user', 'jenis', 'skala', 'judul', 'penulis', 'nama_jurnal', 'peringkat', 'jenis_hki', 'kategori_buku', 'link_doi', 'tahun', 'mitra', 'jabatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan', 'bukti_tambahan'],
            [10, 18, 28, 16, 16, 18, 34, 28, 28, 14, 18, 18, 46, 12, 34, 26, 18, 18, 46, 46],
            Rekognisi::class,
            fn (Rekognisi $item) => [
                $item->id,
                $item->user->nim_nidn ?? '',
                $item->user->name ?? '',
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
                $item->tahun,
                $item->mitra,
                $item->jabatan_efektif,
                optional($item->tanggal_mulai)->format('Y-m-d'),
                optional($item->tanggal_selesai)->format('Y-m-d'),
                $item->bukti_kegiatan,
                $item->bukti_tambahan,
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

        $parsed = $this->bulkParseUpload($request, ['jenis', 'mitra', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan']);
        if ($parsed instanceof JsonResponse) {
            return $parsed;
        }
        [, $rows] = $parsed;

        $existingById = $this->bulkExistingById(Rekognisi::class, $rows);
        $findOwner = $this->bulkOwnerFinder();

        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $d = $row['data'];

            [$existing, $owner, $rowErrors] = $this->bulkResolveTarget($d, $existingById, $findOwner);

            $jenis = Csv::normalizeKey($d['jenis'] ?? '');
            $skala = Csv::normalizeKey($d['skala'] ?? '');
            $owner?->loadMissing('mahasiswa');
            $tipeUser = $owner
                ? $this->userTypeFor($owner)
                : ($existing?->tipe_user_efektif ?? 'mahasiswa');

            [$dates, $badDates] = $this->bulkParseDates($d, ['tanggal_mulai', 'tanggal_selesai'], $rowErrors);

            $payload = [
                'user_id'        => $owner?->id,
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
                'tahun'          => trim((string) ($d['tahun'] ?? '')) ?: null,
                'mitra'          => trim((string) ($d['mitra'] ?? '')) ?: null,
                'jabatan'        => trim((string) ($d['jabatan'] ?? '')) ?: null,
                'bukti_kegiatan' => trim((string) ($d['bukti_kegiatan'] ?? '')),
                'bukti_tambahan' => trim((string) ($d['bukti_tambahan'] ?? '')) ?: null,
            ] + $dates;

            // user_id sudah ditangani pesan di bulkResolveTarget; hindari pesan ganda.
            [$messages] = $this->bulkValidate(
                $payload,
                self::RULES,
                array_merge($owner ? [] : ['user_id'], $badDates)
            );
            $rowErrors = array_merge($rowErrors, $messages);

            $allowedKinds = match ($tipeUser) {
                'dosen' => ['jurnal', 'hki', 'book'],
                'mahasiswa' => ['sinta', 'conference', 'jurnal'],
                'alumni' => ['sinta', 'conference', 'jurnal'],
                default => [],
            };
            if (!in_array($jenis, $allowedKinds, true)) {
                $rowErrors[] = 'Jenis karya tidak sesuai dengan tipe pengguna pada baris ini.';
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

            $plan[] = ['existing' => $existing, 'payload' => $payload, 'owner' => $owner];
        }

        if ($errors) {
            return $this->bulkRejected($errors);
        }

        $result = $this->bulkSave(Rekognisi::class, $plan);
        $this->bulkNotifyOwners($request, $result['perOwner'], 'rekognisi');

        return $this->bulkSuccess($result['created'], $result['updated'], $result['unchanged']);
    }

    // =====================================================================
    // HELPER CRUD
    // =====================================================================

    private function notifyRekognisi(Request $request, Rekognisi $item, string $aksi): void
    {
        $this->notifyOwner($request, $item->user_id, 'rekognisi', $item->judul ?: ($item->mitra ?: 'Rekognisi'), $aksi, $item->jenis, ['rekognisi_id' => $item->id]);
    }

    private function enforceOwnUser(Request $request, array &$data): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        // Timpa user_id (dan tipe_user turunannya) jadi milik mereka sendiri,
        // berapa pun yang dikirim dari klien.
        $actor->loadMissing('mahasiswa');
        $data['user_id'] = $actor->id;
        $data['tipe_user'] = $this->userTypeFor($actor);
    }

    private function authorizeOwnership(Request $request, Rekognisi $rekognisi): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        abort_if(
            $rekognisi->user_id !== $actor->id,
            403,
            'Kamu hanya bisa mengelola data rekognisi milikmu sendiri.'
        );
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(self::RULES);

        $user = User::with('mahasiswa')->findOrFail($data['user_id']);
        $data['tipe_user'] = $this->userTypeFor($user);

        $allowedKinds = match ($data['tipe_user']) {
            'dosen' => ['jurnal', 'hki', 'book'],
            'mahasiswa' => ['sinta', 'conference', 'jurnal'],
            'alumni' => ['sinta', 'conference', 'jurnal'],
        };
        if (!in_array($data['jenis'], $allowedKinds, true)) {
            throw ValidationException::withMessages(['jenis' => 'Jenis karya tidak sesuai dengan tipe pengguna.']);
        }

        $scaleRequired = in_array($data['jenis'], ['jurnal', 'sinta', 'conference'], true);
        if ($scaleRequired && blank($data['skala'])) {
            throw ValidationException::withMessages(['skala' => 'Skala wajib dipilih.']);
        }
        if (!$scaleRequired) {
            $data['skala'] = null;
        }
        if (!in_array($data['jenis'], ['jurnal', 'sinta'], true)) {
            $data['nama_jurnal'] = null;
            $data['peringkat'] = null;
            $data['link_doi'] = null;
        }
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
            'tipe_user'       => $item->user ? $this->userTypeFor($item->user) : $item->tipe_user,
            'nim_nidn'        => optional($item->user)->nim_nidn ?? '-',
            'nama'            => optional($item->user)->name ?? 'Tanpa Nama',
            'avatar_url'      => optional($item->user)->avatar_url,
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
            $query->whereHas('user', fn ($user) => $user->where('role', 'dosen'));
            return;
        }

        if ($type === 'mahasiswa') {
            $query->whereHas('user', fn ($user) => $user->where('role', 'mahasiswa'))
                ->whereDoesntHave('user.mahasiswa', fn ($mahasiswa) => $mahasiswa->where('status', 'lulus'));
            return;
        }

        if ($type === 'alumni') {
            $query->whereHas('user.mahasiswa', fn ($mahasiswa) => $mahasiswa->where('status', 'lulus'));
        }
    }
}