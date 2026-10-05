<?php

namespace App\Http\Controllers;

use App\Models\KerjaSama;
use App\Models\User;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KerjaSamaController extends Controller
{
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

    /** Kolom file CSV (urutan template & ekspor). "nama" hanya informasi, diabaikan saat unggah. */
    private const CSV_COLUMNS = ['id', 'nim_nidn', 'nama', 'jenis', 'jenis_lainnya', 'arah', 'mitra', 'judul_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan'];

    private const MAX_IMPORT_ROWS = 1000;

    use NotifiesOwner;

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
    // =====================================================================

    /** Unduh template CSV kosong lengkap dengan petunjuk pengisian. */
    public function template(Request $request): StreamedResponse
    {
        $this->ensureBulkAccess($request, true);

        $columns = array_values(array_diff(self::CSV_COLUMNS, ['nama']));
        $notes = [
            '# PETUNJUK — baris yang diawali tanda # otomatis diabaikan saat diunggah.',
            '# id: KOSONGKAN untuk data baru. Isi id (dari hasil Download) untuk MENGEDIT data yang sudah ada.',
            '# nim_nidn: NIM mahasiswa / NIDN dosen yang sudah terdaftar di Data Master. Pemilik data tidak bisa diganti saat edit.',
            '# jenis: conference_internasional | pkl | sharing_session | keynote_session | guest_lecture | pengabdian_internasional | research_internasional | lainnya',
            '# Mahasiswa hanya boleh: conference_internasional, pkl, sharing_session, lainnya. Dosen tidak boleh pkl.',
            '# jenis_lainnya: wajib jika jenis = lainnya.  arah: wajib (inbound / outbound) jika jenis = guest_lecture.',
            '# tanggal_mulai & tanggal_selesai: format 2026-09-30 atau 30/09/2026.  bukti_kegiatan: link lengkap (https://...).',
            '# CONTOH (hapus tanda # dan sesuaikan): ;2210001;conference_internasional;;;Universitas Tokyo;Judul kegiatan;2026-03-01;2026-03-03;https://drive.google.com/...',
        ];

        return $this->csvResponse('template-kerja-sama.csv', function ($out) use ($columns, $notes) {
            foreach ($notes as $note) {
                $this->csvLine($out, [$note]);
            }
            $this->csvLine($out, $columns);
        });
    }

    /** Unduh seluruh data kerja sama (bisa diedit lalu diunggah ulang). */
    public function export(Request $request): StreamedResponse
    {
        $this->ensureBulkAccess($request, false);

        return $this->csvResponse('kerja-sama-' . now()->format('Ymd-His') . '.csv', function ($out) {
            $this->csvLine($out, self::CSV_COLUMNS);

            KerjaSama::with('user')->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $item) {
                    $this->csvLine($out, array_map([$this, 'safeCell'], [
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
                    ]));
                }
            });
        });
    }

    /**
     * Unggah CSV: baris dengan id => edit data itu, tanpa id => tambah baru.
     * Semua baris divalidasi dulu; kalau ada yang bermasalah, TIDAK ADA data
     * yang disimpan dan daftar error per baris dikembalikan.
     */
    public function import(Request $request)
    {
        $this->ensureBulkAccess($request, true);

        $request->validate(
            ['file' => ['required', 'file', 'extensions:csv,txt', 'max:2048']],
            [
                'file.required'   => 'Pilih file CSV terlebih dahulu.',
                'file.extensions' => 'File harus berformat .csv (gunakan template dari menu Unduh Template).',
                'file.max'        => 'Ukuran file maksimal 2 MB.',
            ]
        );

        try {
            [$header, $rows] = $this->readCsv($request->file('file')->getRealPath());
        } catch (\RuntimeException $e) {
            return $this->importFailed($e->getMessage());
        }

        if (count($rows) > self::MAX_IMPORT_ROWS) {
            return $this->importFailed('Maksimal ' . self::MAX_IMPORT_ROWS . ' baris data per unggahan.');
        }
        if (!$rows) {
            return $this->importFailed('Tidak ada baris data di file ini.');
        }

        $missing = array_diff(['jenis', 'mitra', 'judul_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan'], $header);
        if ($missing) {
            return $this->importFailed('Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Gunakan template terbaru.');
        }

        // ---- Siapkan data pendukung (sekali query, bukan per baris) ----
        $existingById = KerjaSama::whereIn('id', collect($rows)->pluck('data.id')->filter(fn ($v) => ctype_digit((string) $v))->all())
            ->get()->keyBy('id');

        $people = User::whereIn('role', ['mahasiswa', 'dosen'])->whereNotNull('nim_nidn')->get();
        $byExact = $people->keyBy(fn ($u) => (string) $u->nim_nidn);
        // Excel suka membuang angka 0 di depan (mis. NIDN 0712048901). Cocokkan juga tanpa nol di depan, selama tidak ambigu.
        $byStripped = $people->groupBy(fn ($u) => ltrim((string) $u->nim_nidn, '0'))
            ->filter(fn ($g) => $g->count() === 1)->map(fn ($g) => $g->first());

        // ---- Validasi semua baris ----
        $plan = [];
        $errors = [];
        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            $existing = null;
            $id = trim((string) ($d['id'] ?? ''));
            if ($id !== '') {
                $existing = ctype_digit($id) ? $existingById->get((int) $id) : null;
                if (!$existing) {
                    $rowErrors[] = "id \"{$id}\" tidak ditemukan.";
                }
            }

            $nim = trim((string) ($d['nim_nidn'] ?? ''));
            $owner = $nim !== '' ? ($byExact->get($nim) ?? $byStripped->get(ltrim($nim, '0'))) : null;
            if ($nim !== '' && !$owner) {
                $rowErrors[] = "NIM/NIDN \"{$nim}\" tidak terdaftar sebagai mahasiswa/dosen.";
            }
            if ($existing) {
                if ($owner && $owner->id !== $existing->user_id) {
                    $rowErrors[] = 'Pemilik data tidak bisa diganti saat edit (NIM/NIDN berbeda dari data aslinya).';
                }
                $owner = $existing->user ?? User::find($existing->user_id);
            } elseif ($id === '' && $nim === '') {
                $rowErrors[] = 'nim_nidn wajib diisi untuk data baru.';
            }

            $jenis = $this->normalizeKey($d['jenis'] ?? '');
            $payload = [
                'user_id'         => $owner?->id,
                'tipe_user'       => $owner?->role === 'dosen' ? 'dosen' : 'mahasiswa',
                'jenis'           => $jenis,
                'jenis_lainnya'   => trim((string) ($d['jenis_lainnya'] ?? '')) ?: null,
                'arah'            => $this->normalizeKey($d['arah'] ?? '') ?: null,
                'mitra'           => trim((string) ($d['mitra'] ?? '')),
                'judul_kegiatan'  => trim((string) ($d['judul_kegiatan'] ?? '')),
                'tanggal_mulai'   => $this->parseDate($d['tanggal_mulai'] ?? ''),
                'tanggal_selesai' => $this->parseDate($d['tanggal_selesai'] ?? ''),
                'bukti_kegiatan'  => trim((string) ($d['bukti_kegiatan'] ?? '')),
            ];

            // Tanggal yang tidak dikenali ditolak dengan pesan jelas (jangan sampai ditebak PHP).
            $badDates = [];
            foreach (['tanggal_mulai', 'tanggal_selesai'] as $field) {
                if ($payload[$field] === false) {
                    $rowErrors[] = "{$field} \"" . ($d[$field] ?? '') . "\" tidak dikenali (pakai format 2026-09-30 atau 30/09/2026).";
                    $payload[$field] = null;
                    $badDates = ['tanggal_mulai', 'tanggal_selesai'];
                }
            }

            $validator = Validator::make($payload, self::RULES, [
                'required'                  => ':attribute wajib diisi.',
                'required_if'               => ':attribute wajib diisi untuk jenis ini.',
                'in'                        => ':attribute tidak valid.',
                'url'                       => ':attribute harus berupa link lengkap (https://...).',
                'max'                       => ':attribute terlalu panjang.',
                'date'                      => ':attribute tidak valid (pakai format 2026-09-30 atau 30/09/2026).',
                'tanggal_selesai.after_or_equal' => 'tanggal_selesai tidak boleh sebelum tanggal_mulai.',
            ], [
                'jenis' => 'jenis', 'jenis_lainnya' => 'jenis_lainnya', 'arah' => 'arah', 'mitra' => 'mitra',
                'judul_kegiatan' => 'judul_kegiatan', 'tanggal_mulai' => 'tanggal_mulai',
                'tanggal_selesai' => 'tanggal_selesai', 'bukti_kegiatan' => 'bukti_kegiatan', 'user_id' => 'pemilik',
            ]);
            // user_id sudah ditangani pesan di atas; hindari pesan ganda.
            $messages = collect($validator->errors()->getMessages())->except(array_merge($owner ? [] : ['user_id'], $badDates))->flatten()->all();
            $rowErrors = array_merge($rowErrors, $messages);

            if (!$validator->errors()->has('jenis') && $jenis !== '' && ($msg = $this->jenisRestriction($payload['tipe_user'], $jenis))) {
                $rowErrors[] = $msg;
            }

            if ($rowErrors) {
                $errors[] = ['row' => $line, 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            if ($jenis !== 'lainnya') {
                $payload['jenis_lainnya'] = null;
            }
            if ($jenis !== 'guest_lecture') {
                $payload['arah'] = null;
            }

            $plan[] = ['existing' => $existing, 'payload' => $payload];
        }

        if ($errors) {
            return $this->importFailed(
                count($errors) . ' baris bermasalah. Tidak ada data yang disimpan — perbaiki baris di bawah lalu unggah ulang.',
                array_slice($errors, 0, 50),
                max(0, count($errors) - 50)
            );
        }

        // ---- Simpan (semua atau tidak sama sekali) ----
        $created = $updated = $unchanged = 0;
        $perOwner = [];

        DB::transaction(function () use ($plan, &$created, &$updated, &$unchanged, &$perOwner) {
            foreach ($plan as $p) {
                $payload = $p['payload'];

                if ($p['existing']) {
                    // Pemilik & tipe tidak diubah saat edit.
                    unset($payload['user_id'], $payload['tipe_user']);
                    $p['existing']->fill($payload);
                    if ($p['existing']->isDirty()) {
                        $p['existing']->save();
                        $updated++;
                        $perOwner[$p['existing']->user_id]['updated'] = ($perOwner[$p['existing']->user_id]['updated'] ?? 0) + 1;
                    } else {
                        $unchanged++;
                    }
                } else {
                    KerjaSama::create($payload);
                    $created++;
                    $perOwner[$payload['user_id']]['created'] = ($perOwner[$payload['user_id']]['created'] ?? 0) + 1;
                }
            }
        });

        // Satu notifikasi ringkas per pemilik data (bukan satu per baris).
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
                'Data kerja sama Anda diperbarui',
                ucfirst($request->user()->name) . ' (' . $request->user()->role . ') ' . implode(' dan ', $parts) . ' kerja sama Anda lewat unggah massal.'
            );
        }

        return response()->json([
            'success'   => true,
            'created'   => $created,
            'updated'   => $updated,
            'unchanged' => $unchanged,
            'message'   => "Berhasil: {$created} data ditambahkan, {$updated} diperbarui, {$unchanged} tidak berubah.",
        ]);
    }

    /** Hanya admin/superadmin. Unggah & template butuh akses penuh; unduh cukup readonly ke atas. */
    private function ensureBulkAccess(Request $request, bool $needFull): void
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'superadmin'], true), 403, 'Fitur ini hanya untuk admin dan superadmin.');

        $level = $user->menuLevel('kerja_sama');
        $allowed = $needFull ? $level === 'penuh' : in_array($level, ['readonly', 'penuh'], true);
        abort_unless($allowed, 403, 'Kamu tidak punya akses untuk fitur ini.');
    }

    private function importFailed(string $message, array $errors = [], int $more = 0)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'more'    => $more,
        ], 422);
    }

    private function csvResponse(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca sebagai UTF-8
            $writer($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Pemisah ";" karena itu yang dipakai Excel dengan pengaturan regional Indonesia. */
    private function csvLine($out, array $row): void
    {
        fputcsv($out, $row, ';', '"', '');
    }

    /** Cegah "CSV injection": sel yang diawali = + - @ akan dibaca Excel sebagai rumus. */
    private function safeCell($value)
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $value : $value;
    }

    private function unsafeCell(string $value): string
    {
        return strlen($value) > 1 && $value[0] === "'" && in_array($value[1], ['=', '+', '-', '@'], true)
            ? substr($value, 1)
            : $value;
    }

    private function normalizeKey($value): string
    {
        return preg_replace('/[\s\-\/]+/', '_', mb_strtolower(trim((string) $value)));
    }

    /** Terima 2026-09-30, 30/09/2026, 30-09-2026, 30.09.2026, dst. Hasil: Y-m-d, null kalau kosong, false kalau tidak dikenali. */
    private function parseDate($value): string|false|null
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]/', $value, $m)) {
            $value = $m[1];
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d', 'd/m/y'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $err = \DateTime::getLastErrors();
            if ($date && (!$err || ($err['warning_count'] === 0 && $err['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return false;
    }

    /**
     * Baca CSV (UTF-8 / Windows-1252, pemisah ; , atau tab).
     *
     * @return array{0: array<int,string>, 1: array<int,array{line:int,data:array<string,string>}>}
     */
    private function readCsv(string $path): array
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252'); // CSV biasa dari Excel
        }
        // Excel kadang menambahkan baris "sep=;" paling atas.
        $content = preg_replace('/^sep=.\r?\n/i', '', $content);

        // Tebak pemisah dari baris pertama yang bukan komentar/kosong.
        $delimiter = ';';
        foreach (preg_split('/\r\n|\r|\n/', $content) as $first) {
            if (trim($first) === '' || str_starts_with(ltrim($first, " \t\"'"), '#')) {
                continue;
            }
            $counts = [';' => substr_count($first, ';'), ',' => substr_count($first, ','), "\t" => substr_count($first, "\t")];
            arsort($counts);
            $delimiter = array_key_first($counts);
            break;
        }

        $aliases = [
            'nim' => 'nim_nidn', 'nidn' => 'nim_nidn', 'nim_nidn' => 'nim_nidn', 'nim/nidn' => 'nim_nidn',
            'judul' => 'judul_kegiatan', 'bukti' => 'bukti_kegiatan', 'link_bukti' => 'bukti_kegiatan',
            'tgl_mulai' => 'tanggal_mulai', 'tgl_selesai' => 'tanggal_selesai',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $header = null;
        $rows = [];
        $line = 0;
        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $line++;
            $cells = array_map(fn ($c) => trim((string) $c), $cells);

            if (!array_filter($cells, fn ($c) => $c !== '')) {
                continue; // baris kosong
            }
            if (str_starts_with($cells[0], '#')) {
                continue; // baris komentar
            }

            if ($header === null) {
                $header = array_map(function ($h) use ($aliases) {
                    $h = $this->normalizeKey($h);

                    return $aliases[$h] ?? $h;
                }, $cells);
                continue;
            }

            $data = [];
            foreach ($header as $i => $name) {
                if ($name !== '' && !isset($data[$name])) {
                    $data[$name] = $this->unsafeCell($cells[$i] ?? '');
                }
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }
        fclose($handle);

        if ($header === null) {
            throw new \RuntimeException('Baris judul kolom tidak ditemukan. Gunakan template dari menu Unduh Template.');
        }

        return [array_values(array_filter($header)), $rows];
    }

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