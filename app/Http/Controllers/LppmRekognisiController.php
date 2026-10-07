<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Http\Controllers\Concerns\NotifiesOwner;
use App\Models\Rekognisi;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LppmRekognisiController extends Controller
{
    use NotifiesOwner;
    use HandlesBulkData;

    /** Aturan validasi yang sama dipakai form (store/update) dan unggah massal. */
    private const RULES = [
        'user_id'         => ['required', 'exists:users,id'],
        'jenis'           => ['required', 'in:nasional,internasional,alumni'],
        'mitra'           => ['required', 'string', 'max:255'],
        'jabatan'         => ['nullable', 'required_if:jenis,alumni', 'string', 'max:255'],
        'tanggal_mulai'   => ['required', 'date'],
        'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
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
        $ownScope = $user->menuLevel('rekognisi') === 'biasa';

        $query = Rekognisi::with('user')->latest();
        if ($ownScope) {
            $query->where('user_id', $user->id);
        }
        $items = $query->paginate(10);

        // Semua user yang bisa punya rekognisi: dosen & mahasiswa
        $userList = $ownScope
            ? collect([$user])
            : User::whereIn('role', ['dosen', 'mahasiswa'])->orderBy('name')->get();

        $statsQuery = fn () => $ownScope
            ? Rekognisi::where('user_id', $user->id)
            : Rekognisi::query();

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'nasional'      => (clone $statsQuery())->where('jenis', 'nasional')->count(),
            'internasional' => (clone $statsQuery())->where('jenis', 'internasional')->count(),
            'alumni'        => (clone $statsQuery())->where('jenis', 'alumni')->count(),
        ];

        return view('lppm_rekognisi', compact('items', 'userList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $item = Rekognisi::create($data)->load('user');

        $this->notifyRekognisi($request, $item, 'ditambahkan');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);

        // Pemilik data tidak boleh diganti saat edit (dikunci juga di sisi server).
        $request->merge(['user_id' => $rekognisi->user_id, 'tipe_user' => $rekognisi->tipe_user]);

        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $rekognisi->update($data);
        $rekognisi->load('user');

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
                'jenis'   => ['nullable', 'in:nasional,internasional,alumni'],
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
     * jabatan wajib untuk alumni, dan otomatis dikosongkan jika jenis bukan alumni (sama seperti form).
     *
     * @return array{0: array, 1: string[]}
     */
    private function resolveBulkRow(Rekognisi $item, array $changes): array
    {
        $jenis = (string) ($changes['jenis'] ?? $item->jenis);
        $jabatan = $changes['jabatan'] ?? $item->jabatan;
        $errors = [];

        if ($jenis === 'alumni') {
            if (trim((string) $jabatan) === '') {
                $errors[] = 'Jabatan wajib diisi untuk jenis alumni.';
            }
        } else {
            if (array_key_exists('jabatan', $changes)) {
                $errors[] = 'Jabatan hanya boleh diisi jika jenis = alumni (jenis akhir: ' . $jenis . ').';
            }
            $changes['jabatan'] = null;
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
                'jenis'           => ['required' => 'Ya', 'example' => 'nasional', 'width' => 16, 'options' => ['nasional', 'internasional', 'alumni'], 'note' => 'Jenis rekognisi. Pilih dari dropdown.'],
                'mitra'           => ['required' => 'Ya', 'example' => 'PT Teknologi Nusantara', 'width' => 34, 'note' => 'Nama institusi / perusahaan pemberi rekognisi.'],
                'jabatan'         => ['required' => 'Jika jenis = alumni', 'example' => '', 'width' => 26, 'note' => 'Jabatan alumni di mitra. Untuk jenis lain kosongkan (isian diabaikan).'],
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
            ['id', 'nim_nidn', 'nama', 'jenis', 'mitra', 'jabatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan', 'bukti_tambahan'],
            [10, 18, 28, 16, 34, 26, 18, 18, 46, 46],
            Rekognisi::class,
            fn (Rekognisi $item) => [
                $item->id,
                $item->user->nim_nidn ?? '',
                $item->user->name ?? '',
                $item->jenis,
                $item->mitra,
                $item->jabatan,
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
            [$dates, $badDates] = $this->bulkParseDates($d, ['tanggal_mulai', 'tanggal_selesai'], $rowErrors);

            $payload = [
                'user_id'        => $owner?->id,
                'tipe_user'      => $owner?->role === 'dosen' ? 'dosen' : 'mahasiswa',
                'jenis'          => $jenis,
                'mitra'          => trim((string) ($d['mitra'] ?? '')),
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

            if ($rowErrors) {
                $errors[] = ['row' => $row['line'], 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            // Jabatan hanya berlaku untuk alumni (sama seperti form).
            if ($jenis !== 'alumni') {
                $payload['jabatan'] = null;
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
        $this->notifyOwner($request, $item->user_id, 'rekognisi', $item->mitra, $aksi, $item->jenis, ['rekognisi_id' => $item->id]);
    }

    private function enforceOwnUser(Request $request, array &$data): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        // Timpa user_id (dan tipe_user turunannya) jadi milik mereka sendiri,
        // berapa pun yang dikirim dari klien.
        $data['user_id'] = $actor->id;
        $data['tipe_user'] = $actor->role === 'dosen' ? 'dosen' : 'mahasiswa';
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

        // tipe_user diturunkan otomatis dari role user yang dipilih, bukan dari input klien
        $user = User::findOrFail($data['user_id']);
        $data['tipe_user'] = $user->role === 'dosen' ? 'dosen' : 'mahasiswa';

        if ($data['jenis'] !== 'alumni') {
            $data['jabatan'] = null;
        }

        return $data;
    }

    private function format(Rekognisi $item): array
    {
        return [
            'id'              => $item->id,
            'user_id'         => $item->user_id,
            'tipe_user'       => $item->tipe_user,
            'nim_nidn'        => optional($item->user)->nim_nidn ?? '-',
            'nama'            => optional($item->user)->name ?? 'Tanpa Nama',
            'jenis'           => $item->jenis,
            'mitra'           => $item->mitra,
            'jabatan'         => $item->jabatan,
            'tanggal_mulai'   => optional($item->tanggal_mulai)->format('Y-m-d'),
            'tanggal_selesai' => optional($item->tanggal_selesai)->format('Y-m-d'),
            'bukti_kegiatan'  => $item->bukti_kegiatan,
            'bukti_tambahan'  => $item->bukti_tambahan,
        ];
    }
}