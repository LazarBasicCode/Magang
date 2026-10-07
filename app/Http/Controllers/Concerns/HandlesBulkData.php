<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Support\Csv;
use App\Support\SelectedIds;
use App\Support\Xlsx;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kerangka unggah/unduh massal (CSV) yang dipakai ulang oleh tiap menu.
 *
 * Controller yang memakai trait ini WAJIB:
 *  - use NotifiesOwner (dipakai bulkNotifyOwners)
 *  - mengimplementasikan bulkMenu()
 *
 * Bagian yang khas per menu (aturan validasi, bentuk payload, kolom CSV)
 * tetap ditulis di controller masing-masing.
 */
trait HandlesBulkData
{
    /** Kunci menu di sistem hak akses, mis. 'kerja_sama' atau 'rekognisi'. */
    abstract protected function bulkMenu(): string;

    /** Urutan tanggal bergaris miring di file yang sedang diunggah ('dmy' atau 'mdy'). */
    protected string $bulkDateOrder = 'dmy';

    protected function bulkMaxRows(): int
    {
        return 1000;
    }

    // ---- Pengaturan pemilik data (default: kolom user_id langsung ke tabel users).
    // ---- Menu yang pemiliknya lewat tabel profil (mahasiswa/dosen) meng-override method ini.

    /** Nama relasi di model data menuju pemiliknya (mis. 'user', 'mahasiswa', 'dosen'). */
    protected function bulkOwnerRelation(): string
    {
        return 'user';
    }

    /** Relasi yang di-eager-load (boleh bertingkat, mis. 'mahasiswa.user'). */
    protected function bulkOwnerWith(): string
    {
        return $this->bulkOwnerRelation();
    }

    /** Kolom foreign key pemilik di tabel data. */
    protected function bulkOwnerKey(): string
    {
        return 'user_id';
    }

    /** Nama kolom CSV pengenal pemilik: 'nim_nidn', 'nim', atau 'nidn'. */
    protected function bulkIdColumn(): string
    {
        return 'nim_nidn';
    }

    /** Sebutan pemilik di pesan error. */
    protected function bulkOwnerLabel(): string
    {
        return 'mahasiswa/dosen';
    }

    /** ID user (untuk notifikasi) dari objek pemilik. Default: pemiliknya memang User. */
    protected function bulkOwnerUserId(?object $owner): ?int
    {
        return $owner?->id;
    }

    /** Alias khusus menu (judul kolom lain -> nama resmi). */
    protected function bulkExtraAliases(): array
    {
        return [];
    }

    /** Semua alias judul kolom yang dikenali saat membaca file. */
    protected function bulkAliases(): array
    {
        $id = $this->bulkIdColumn();

        return array_merge([
            'nim' => $id, 'nidn' => $id, 'nim_nidn' => $id,
            'link_bukti' => 'bukti_kegiatan', 'bukti' => 'bukti_kegiatan',
            'tgl_mulai' => 'tanggal_mulai', 'tgl_selesai' => 'tanggal_selesai',
        ], $this->bulkExtraAliases());
    }

    // =====================================================================
    // AKSES
    // =====================================================================

    /** Hanya admin/superadmin. Unggah & template butuh akses penuh; unduh cukup readonly ke atas. */
    protected function bulkEnsureAccess(Request $request, bool $needFull): void
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'superadmin'], true), 403, 'Fitur ini hanya untuk admin dan superadmin.');

        $level = $user->menuLevel($this->bulkMenu());
        $allowed = $needFull ? $level === 'penuh' : in_array($level, ['readonly', 'penuh'], true);
        abort_unless($allowed, 403, 'Kamu tidak punya akses untuk fitur ini.');
    }

    // =====================================================================
    // UNDUH: TEMPLATE & EKSPOR
    // =====================================================================

    /**
     * Template CSV: petunjuk disusun sebagai TABEL (satu kolom per baris ke bawah),
     * lalu judul kolom data + satu baris contoh. Semua baris petunjuk diawali "#"
     * sehingga otomatis diabaikan saat diunggah.
     *
     * @param  array<string,array{required:string,example:string,note:string}>  $columns  urutan = urutan kolom data (kolom pertama harus "id")
     * @param  string[]  $notes  catatan umum (satu kalimat pendek per elemen)
     */
    protected function bulkTemplateResponse(string $filename, array $columns, array $notes = []): StreamedResponse
    {
        return Csv::download($filename, function ($out) use ($columns, $notes) {
            Csv::line($out, ['# PETUNJUK PENGISIAN (baris yang diawali tanda # tidak ikut diunggah)']);
            foreach ($notes as $note) {
                Csv::line($out, ['# ' . $note]);
            }
            Csv::line($out, ['#']);

            // Tabel panduan kolom: satu kolom data = satu baris.
            Csv::line($out, ['# KOLOM', 'WAJIB DIISI', 'CONTOH', 'KETERANGAN']);
            foreach ($columns as $key => $col) {
                Csv::line($out, ['# ' . $key, $col['required'], $col['example'], $col['note']]);
            }
            Csv::line($out, ['#']);

            // Area isi data.
            Csv::line($out, ['# ISI DATA MULAI DARI BARIS DI BAWAH JUDUL KOLOM INI. Baris "# CONTOH" boleh dihapus.']);
            Csv::line($out, array_keys($columns));

            $example = [];
            foreach ($columns as $col) {
                $example[] = $col['example'];
            }
            $example[0] = '# CONTOH';
            Csv::line($out, $example);
        });
    }

    /** Nama sheet data pada file Excel (menu yang memakai template XLSX meng-override ini). */
    protected function bulkSheetName(): ?string
    {
        return null;
    }

    /**
     * Template Excel (.xlsx): sheet "Petunjuk" (catatan singkat + tabel panduan kolom) + sheet data berisi judul kolom,
     * satu baris contoh (diawali "# CONTOH", diabaikan saat unggah), dan dropdown pilihan.
     * Setiap nilai berada di selnya sendiri, jadi tidak bergantung pada pemisah CSV.
     *
     * @param  array<string,array{required:string,example:string,note:string,width?:int,options?:string[]}>  $columns
     * @param  string[]  $notes
     */
    protected function bulkXlsxTemplateResponse(string $filename, string $title, string $sheetName, array $columns, array $notes = [])
    {
        $cell = fn ($v, $style = Xlsx::STYLE_CELL) => ['value' => (string) $v, 'style' => $style];
        $head = fn (array $labels) => array_map(fn ($l) => $cell($l, Xlsx::STYLE_HEADER), $labels);
        $keys = array_keys($columns);

        // Petunjuk singkat + tabel panduan kolom (satu kolom data = satu baris).
        $guide = [[$cell('PETUNJUK PENGISIAN', Xlsx::STYLE_TITLE)]];
        foreach ($notes as $note) {
            $guide[] = [$cell('• ' . $note, Xlsx::STYLE_PLAIN)];
        }
        $guide[] = [];
        $guide[] = $head(['KOLOM', 'WAJIB DIISI', 'CONTOH', 'KETERANGAN']);
        foreach ($columns as $key => $col) {
            $guide[] = [$cell($key), $cell($col['required']), $cell($col['example']), $cell($col['note'])];
        }

        $example = [];
        foreach ($columns as $col) {
            $example[] = $cell($col['example'], Xlsx::STYLE_PLAIN);
        }
        $example[0] = $cell('# CONTOH', Xlsx::STYLE_PLAIN);

        $validations = [];
        foreach (array_values($keys) as $i => $key) {
            if (!empty($columns[$key]['options'])) {
                $col = Xlsx::columnName($i + 1);
                $validations[] = [
                    'range' => "{$col}2:{$col}" . ($this->bulkMaxRows() + 1),
                    'list'  => $columns[$key]['options'],
                    'error' => 'Pilih salah satu: ' . implode(' | ', $columns[$key]['options']),
                ];
            }
        }

        return Xlsx::download($filename, [
            [
                'name'   => $sheetName,
                'widths' => array_map(fn ($c) => $c['width'] ?? 22, array_values($columns)),
                'rows'   => [$head($keys), $example],
                'validations' => $validations,
                'freeze' => true,
                'autoborder' => ['cols' => count($keys), 'from' => 2, 'to' => $this->bulkMaxRows() + 1],
            ],
            ['name' => 'Petunjuk', 'widths' => [22, 32, 30, 70], 'rows' => $guide],
        ]);
    }

    /**
     * Hapus banyak data sekaligus (data terpilih / centang baris tabel; lihat BulkSelectionController).
     * Akses: admin/superadmin dengan akses PENUH pada menu ini. Semua-atau-tidak-sama-sekali (satu transaksi);
     * id yang sudah tidak ada dilewati dan dihitung di "missing".
     *
     * @param  string[]  $with    relasi yang di-load (dipakai untuk notifikasi)
     * @param  callable  $notify  fn($item): void — notifikasi ke pemilik data, sama seperti hapus satuan
     */
    protected function bulkDestroySelected(Request $request, string $modelClass, array $with, callable $notify): JsonResponse
    {
        $this->bulkEnsureAccess($request, true);

        $ids = SelectedIds::from($request);
        abort_if(!$ids, 422, 'Belum ada data yang dipilih.');

        $items = $modelClass::with($with)->whereKey($ids)->get();

        $deleted = [];
        DB::transaction(function () use ($items, &$deleted) {
            foreach ($items as $item) {
                $deleted[] = $item->getKey();
                $item->delete();
            }
        });

        // Notifikasi setelah commit, supaya penghapusan yang gagal tidak meninggalkan notifikasi palsu.
        foreach ($items as $item) {
            $notify($item);
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'missing' => count($ids) - count($deleted),
        ]);
    }

    /**
     * Update massal data terpilih (centang baris). Hanya kolom yang DIISI yang diubah;
     * kolom yang dikosongkan tetap memakai nilai lama tiap baris.
     *
     * @param  array<string,mixed>  $rules   aturan validasi tiap kolom (semua harus 'nullable')
     * @param  callable(object):void  $notify  dipanggil per data setelah commit
     * @param  callable(object):array  $format payload baris untuk JS (sama dengan format() menu itu)
     */
    protected function bulkUpdateSelected(Request $request, string $modelClass, array $with, array $rules, callable $notify, callable $format): JsonResponse
    {
        $this->bulkEnsureAccess($request, true);

        $ids = SelectedIds::from($request);
        abort_if(!$ids, 422, 'Belum ada data yang dipilih.');

        $changes = collect($request->validate($rules))
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();
        abort_if(!$changes, 422, 'Isi minimal satu kolom yang ingin diubah.');

        $items = $modelClass::with($with)->whereKey($ids)->get();

        DB::transaction(function () use ($items, $changes) {
            foreach ($items as $item) {
                $item->update($changes);
            }
        });

        foreach ($items as $item) {
            $item->loadMissing($with);
            $notify($item);
        }

        return response()->json([
            'success' => true,
            'updated' => $items->pluck($items->first()?->getKeyName() ?? 'id')->all(),
            'missing' => count($ids) - $items->count(),
            'rows'    => $items->map(fn ($item) => $format($item))->values()->all(),
        ]);
    }

    /** Ekspor seluruh data model ke Excel (.xlsx), sheet pertama = data, siap diedit & diunggah ulang. */
    protected function bulkXlsxExportResponse(string $filename, string $sheetName, array $header, array $widths, string $modelClass, callable $mapRow)
    {
        $cell = fn ($v, $style = Xlsx::STYLE_CELL) => ['value' => (string) $v, 'style' => $style];

        // Data terpilih (centang baris): kalau ada ids, hanya baris itu yang diekspor (lihat BulkSelectionController).
        $ids = SelectedIds::from(request());
        if ($ids) {
            $filename = preg_replace('/\.xlsx$/', '-terpilih.xlsx', $filename);
        }

        $rows = [array_map(fn ($h) => $cell($h, Xlsx::STYLE_HEADER), $header)];
        $modelClass::with($this->bulkOwnerWith())->when($ids, fn ($q) => $q->whereKey($ids))->chunkById(500, function ($items) use (&$rows, $mapRow, $cell) {
            foreach ($items as $item) {
                $rows[] = array_map(fn ($v) => $cell(Csv::safeCell($v)), $mapRow($item));
            }
        });

        return Xlsx::download($filename, [[
            'name' => $sheetName, 'widths' => $widths, 'rows' => $rows, 'freeze' => true, 'autofilter' => true,
            'autoborder' => ['cols' => count($header), 'from' => 2, 'to' => max(count($rows) + 1000, 2000)],
        ]]);
    }

    /**
     * Ekspor seluruh data model ke CSV.
     *
     * @param  string[]  $header
     * @param  class-string  $modelClass  model data (relasi pemilik: lihat bulkOwnerWith())
     * @param  callable(object):array  $mapRow  mengubah satu model jadi array sel
     */
    protected function bulkExportResponse(string $filename, array $header, string $modelClass, callable $mapRow): StreamedResponse
    {
        return Csv::download($filename, function ($out) use ($header, $modelClass, $mapRow) {
            Csv::line($out, $header);

            $modelClass::with($this->bulkOwnerWith())->chunkById(500, function ($rows) use ($out, $mapRow) {
                foreach ($rows as $item) {
                    Csv::line($out, array_map(fn ($cell) => Csv::safeCell($cell), $mapRow($item)));
                }
            });
        });
    }

    // =====================================================================
    // UNGGAH: BACA & VALIDASI
    // =====================================================================

    protected function bulkFail(string $message, array $errors = [], int $more = 0): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'more'    => $more,
        ], 422);
    }

    /** Validasi file unggahan lalu baca barisnya. Mengembalikan [header, rows] atau respons error. */
    protected function bulkParseUpload(Request $request, array $requiredColumns): array|JsonResponse
    {
        $request->validate(
            ['file' => ['required', 'file', 'extensions:xlsx,csv,txt', 'max:2048']],
            [
                'file.required'   => 'Pilih file Excel terlebih dahulu.',
                'file.extensions' => 'File harus berformat .xlsx atau .csv (gunakan template dari menu Unduh Template).',
                'file.max'        => 'Ukuran file maksimal 2 MB.',
            ]
        );

        try {
            $path = $request->file('file')->getRealPath();
            [$header, $rows] = strtolower($request->file('file')->getClientOriginalExtension()) === 'xlsx'
                ? Xlsx::read($path, $this->bulkAliases(), $this->bulkSheetName())
                : Csv::read($path, $this->bulkAliases());
        } catch (\RuntimeException $e) {
            return $this->bulkFail($e->getMessage());
        }

        if (count($rows) > $this->bulkMaxRows()) {
            return $this->bulkFail('Maksimal ' . $this->bulkMaxRows() . ' baris data per unggahan.');
        }
        if (!$rows) {
            return $this->bulkFail('Tidak ada baris data di file ini.');
        }

        $missing = array_diff($requiredColumns, $header);
        if ($missing) {
            return $this->bulkFail('Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Gunakan template terbaru.');
        }

        // Excel bisa menyimpan ulang tanggal sebagai 9/17/2026 (bulan dulu) atau 17/9/2026 (hari dulu).
        $dateValues = [];
        foreach ($rows as $row) {
            foreach ($row['data'] as $col => $val) {
                if (str_starts_with((string) $col, 'tanggal')) {
                    $dateValues[] = $val;
                }
            }
        }
        $order = Csv::detectDateOrder($dateValues);
        if ($order === 'conflict') {
            return $this->bulkFail('Format tanggal di file ini campur (ada yang bulan/hari dan ada yang hari/bulan). Samakan semuanya, sebaiknya pakai format 2026-09-30.');
        }
        if ($order === 'ambiguous') {
            return $this->bulkFail('Format tanggal tidak bisa dipastikan (mis. 9/5/2026 bisa berarti 9 Mei atau 5 September). Ubah kolom tanggal ke format 2026-09-30 lalu unggah ulang.');
        }
        $this->bulkDateOrder = $order;

        return [$header, $rows];
    }

    /** Data yang sudah ada untuk baris ber-id (sekali query, bukan per baris). */
    protected function bulkExistingById(string $modelClass, array $rows): Collection
    {
        $ids = collect($rows)->pluck('data.id')->filter(fn ($v) => ctype_digit((string) $v))->all();

        return $modelClass::with($this->bulkOwnerWith())->whereIn('id', $ids)->get()->keyBy('id');
    }

    /** Pencari pemilik data dari NIM/NIDN: fn(string $nim): ?object. Default: tabel users. */
    protected function bulkOwnerFinder(): \Closure
    {
        return $this->bulkMakeFinder(
            User::whereIn('role', ['mahasiswa', 'dosen'])->whereNotNull('nim_nidn')->get(),
            'nim_nidn'
        );
    }

    /** Pencari dari kumpulan model berdasarkan satu kolom pengenal (NIM/NIDN). */
    protected function bulkMakeFinder(Collection $people, string $column): \Closure
    {
        $people = $people->filter(fn ($p) => $p->{$column} !== null && $p->{$column} !== '');
        $byExact = $people->keyBy(fn ($p) => (string) $p->{$column});
        // Excel suka membuang angka 0 di depan (mis. NIDN 0712048901). Cocokkan juga tanpa nol di depan, selama tidak ambigu.
        $byStripped = $people->groupBy(fn ($p) => ltrim((string) $p->{$column}, '0'))
            ->filter(fn ($g) => $g->count() === 1)->map(fn ($g) => $g->first());

        return fn (string $nim) => $byExact->get($nim) ?? $byStripped->get(ltrim($nim, '0'));
    }

    /**
     * Tentukan baris ini data baru atau edit, dan siapa pemiliknya.
     *
     * @return array{0: ?object, 1: ?object, 2: string[]}  [data lama (null = baru), pemilik, pesan error]
     */
    protected function bulkResolveTarget(array $d, Collection $existingById, \Closure $findOwner): array
    {
        $errors = [];
        $idCol = $this->bulkIdColumn();
        $idLabel = str_replace('_', '/', strtoupper($idCol));

        $existing = null;
        $id = trim((string) ($d['id'] ?? ''));
        if ($id !== '') {
            $existing = ctype_digit($id) ? $existingById->get((int) $id) : null;
            if (!$existing) {
                $errors[] = "id \"{$id}\" tidak ditemukan.";
            }
        }

        $nim = trim((string) ($d[$idCol] ?? ''));
        $owner = $nim !== '' ? $findOwner($nim) : null;
        if ($nim !== '' && !$owner) {
            $errors[] = "{$idLabel} \"{$nim}\" tidak terdaftar sebagai {$this->bulkOwnerLabel()}.";
        }
        if ($existing) {
            if ($owner && $owner->id !== $existing->{$this->bulkOwnerKey()}) {
                $errors[] = "Pemilik data tidak bisa diganti saat edit ({$idLabel} berbeda dari data aslinya).";
            }
            $owner = $existing->{$this->bulkOwnerRelation()};
        } elseif ($id === '' && $nim === '') {
            $errors[] = "{$idCol} wajib diisi untuk data baru.";
        }

        return [$existing, $owner, $errors];
    }

    /** Tahun dari CSV: "2026" -> 2026 (angka), selain itu dibiarkan agar ditolak validator. */
    protected function bulkYear($value): int|string
    {
        $value = trim((string) $value);

        return ctype_digit($value) ? (int) $value : $value;
    }

    /**
     * Ubah kolom tanggal CSV jadi Y-m-d. Tanggal yang tidak dikenali ditolak dengan pesan jelas
     * (jangan sampai ditebak PHP).
     *
     * @param  string[]  $fields
     * @return array{0: array<string,?string>, 1: string[]}  [nilai per kolom, kolom yang pesan validatornya perlu disembunyikan]
     */
    protected function bulkParseDates(array $d, array $fields, array &$rowErrors): array
    {
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = Csv::parseDate($d[$field] ?? '', $this->bulkDateOrder);
        }

        $suppress = [];
        foreach ($fields as $field) {
            if ($values[$field] === false) {
                $rowErrors[] = "{$field} \"" . ($d[$field] ?? '') . '" tidak dikenali (pakai format 2026-09-30 atau 30/09/2026).';
                $values[$field] = null;
                $suppress = $fields;
            }
        }

        return [$values, $suppress];
    }

    /**
     * Jalankan aturan validasi form pada satu baris.
     *
     * @param  string[]  $suppress  kunci yang pesannya tidak ditampilkan (sudah dijelaskan pesan lain)
     * @return array{0: string[], 1: string[]}  [pesan yang ditampilkan, semua kunci yang gagal]
     */
    protected function bulkValidate(array $payload, array $rules, array $suppress = []): array
    {
        $attributes = array_combine(array_keys($rules), array_keys($rules));
        $attributes[$this->bulkOwnerKey()] = 'pemilik';

        $validator = Validator::make($payload, $rules, [
            'required'                       => ':attribute wajib diisi.',
            'required_if'                    => ':attribute wajib diisi untuk jenis ini.',
            'in'                             => ':attribute tidak valid.',
            'url'                            => ':attribute harus berupa link lengkap (https://...).',
            'max'                            => ':attribute terlalu panjang.',
            'date'                           => ':attribute tidak valid (pakai format 2026-09-30 atau 30/09/2026).',
            'tanggal_selesai.after_or_equal' => 'tanggal_selesai tidak boleh sebelum tanggal_mulai.',
            'tahun.integer'                  => 'tahun harus berupa angka 4 digit (mis. 2026).',
            'tahun.min'                      => 'tahun minimal 2000.',
            'tahun.max'                      => 'tahun maksimal 2100.',
        ], $attributes);

        $all = $validator->errors()->getMessages();
        $shown = collect($all)->except($suppress)->flatten()->all();

        return [$shown, array_keys($all)];
    }

    /** Ada baris bermasalah -> tolak seluruh unggahan, tampilkan maksimal 50 baris pertama. */
    protected function bulkRejected(array $errors): JsonResponse
    {
        return $this->bulkFail(
            count($errors) . ' baris bermasalah. Tidak ada data yang disimpan — perbaiki baris di bawah lalu unggah ulang.',
            array_slice($errors, 0, 50),
            max(0, count($errors) - 50)
        );
    }

    // =====================================================================
    // UNGGAH: SIMPAN & LAPORKAN
    // =====================================================================

    /**
     * Simpan semua baris dalam satu transaksi (semua atau tidak sama sekali).
     *
     * @param  class-string  $modelClass
     * @param  array<int,array{existing:?object,payload:array,owner:?object}>  $plan
     * @return array{created:int,updated:int,unchanged:int,perOwner:array}
     */
    protected function bulkSave(string $modelClass, array $plan): array
    {
        $created = $updated = $unchanged = 0;
        $perOwner = [];

        DB::transaction(function () use ($modelClass, $plan, &$created, &$updated, &$unchanged, &$perOwner) {
            foreach ($plan as $p) {
                $payload = $p['payload'];
                $userId = $this->bulkOwnerUserId($p['owner'] ?? null);

                if ($p['existing']) {
                    // Pemilik & tipe tidak diubah saat edit.
                    unset($payload[$this->bulkOwnerKey()], $payload['tipe_user']);
                    $p['existing']->fill($payload);
                    if ($p['existing']->isDirty()) {
                        $p['existing']->save();
                        $updated++;
                        if ($userId) {
                            $perOwner[$userId]['updated'] = ($perOwner[$userId]['updated'] ?? 0) + 1;
                        }
                    } else {
                        $unchanged++;
                    }
                } else {
                    $modelClass::create($payload);
                    $created++;
                    if ($userId) {
                        $perOwner[$userId]['created'] = ($perOwner[$userId]['created'] ?? 0) + 1;
                    }
                }
            }
        });

        return compact('created', 'updated', 'unchanged', 'perOwner');
    }

    /** Satu notifikasi ringkas per pemilik data (bukan satu per baris). */
    protected function bulkNotifyOwners(Request $request, array $perOwner, string $label): void
    {
        foreach ($perOwner as $ownerId => $n) {
            $parts = [];
            if (!empty($n['created'])) {
                $parts[] = "menambahkan {$n['created']} data";
            }
            if (!empty($n['updated'])) {
                $parts[] = "memperbarui {$n['updated']} data";
            }
            $this->notifyUser(
                $request,
                (int) $ownerId,
                "Data {$label} Anda diperbarui",
                ucfirst($request->user()->name) . ' (' . $request->user()->role . ') ' . implode(' dan ', $parts) . " {$label} Anda lewat unggah massal."
            );
        }
    }

    protected function bulkSuccess(int $created, int $updated, int $unchanged): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'created'   => $created,
            'updated'   => $updated,
            'unchanged' => $unchanged,
            'message'   => "Berhasil: {$created} data ditambahkan, {$updated} diperbarui, {$unchanged} tidak berubah.",
        ]);
    }
}