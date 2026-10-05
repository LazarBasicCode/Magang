<?php

namespace App\Http\Controllers;

use App\Models\KerjaSama;
use App\Models\User;
use App\Http\Controllers\Concerns\NotifiesOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use ZipArchive;
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
    private const EXCEL_COLUMNS = ['id', 'nim_nidn', 'nama', 'jenis', 'jenis_lainnya', 'arah', 'mitra', 'judul_kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'bukti_kegiatan'];

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
    // UNGGAH / UNDUH MASSAL (EXCEL) — khusus admin & superadmin
    // =====================================================================

    /** Template Excel: sheet Petunjuk + sheet Data Kerja Sama dengan kotak, lebar kolom, dan dropdown. */
    public function template(Request $request)
    {
        $this->ensureBulkAccess($request, true);

        $rows = [
            ['id','nim_nidn','nama','jenis','jenis_lainnya','arah','mitra','judul_kegiatan','tanggal_mulai','tanggal_selesai','bukti_kegiatan'],
            ['','','','conference_internasional','','','Contoh Mitra','Contoh judul kegiatan','2026-03-01','2026-03-03','https://contoh.com/bukti'],
        ];

        return $this->xlsxResponse('template-kerja-sama.xlsx', [
            $this->instructionSheet(),
            $this->dataSheet($rows, true),
        ]);
    }

    /** Download seluruh data sebagai Excel yang rapi dan siap diedit/upload ulang. */
    public function export(Request $request)
    {
        $this->ensureBulkAccess($request, false);

        $rows = [self::EXCEL_COLUMNS];
        KerjaSama::with('user')->chunkById(500, function ($items) use (&$rows) {
            foreach ($items as $item) {
                $rows[] = array_map([$this, 'safeCell'], [
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
                ]);
            }
        });

        return $this->xlsxResponse('kerja-sama-' . now()->format('Ymd-His') . '.xlsx', [
            $this->instructionSheet(),
            $this->dataSheet($rows, false),
        ]);
    }

    /**
     * Upload Excel. File CSV lama tetap diterima agar tidak memutus workflow lama.
     * Excel memakai sheet "Data Kerja Sama"; kolom nama hanya informasi dan tidak mengubah pemilik.
     */
    public function import(Request $request)
    {
        $this->ensureBulkAccess($request, true);

        $request->validate(
            ['file' => ['required', 'file', 'extensions:xlsx,csv,txt', 'max:2048']],
            [
                'file.required' => 'Pilih file Excel terlebih dahulu.',
                'file.extensions' => 'File harus .xlsx, .csv, atau .txt. Untuk struktur paling rapi gunakan template Excel.',
                'file.max' => 'Ukuran file maksimal 2 MB.',
            ]
        );

        try {
            $extension = strtolower($request->file('file')->getClientOriginalExtension());
            if ($extension === 'xlsx') {
                [$header, $rows] = $this->readXlsx($request->file('file')->getRealPath());
            } else {
                [$header, $rows] = $this->readCsv($request->file('file')->getRealPath());
            }
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
            return $this->importFailed('Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Gunakan template Excel terbaru.');
        }

        $existingById = KerjaSama::whereIn('id', collect($rows)->pluck('data.id')->filter(fn ($v) => ctype_digit((string) $v))->all())
            ->get()->keyBy('id');

        $people = User::whereIn('role', ['mahasiswa', 'dosen'])->whereNotNull('nim_nidn')->get();
        $byExact = $people->keyBy(fn ($u) => (string) $u->nim_nidn);
        $byStripped = $people->groupBy(fn ($u) => ltrim((string) $u->nim_nidn, '0'))
            ->filter(fn ($g) => $g->count() === 1)->map(fn ($g) => $g->first());

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
                if (!$existing) $rowErrors[] = "id \"{$id}\" tidak ditemukan.";
            }

            $nim = trim((string) ($d['nim_nidn'] ?? ''));
            $owner = $nim !== '' ? ($byExact->get($nim) ?? $byStripped->get(ltrim($nim, '0'))) : null;
            if ($nim !== '' && !$owner) $rowErrors[] = "NIM/NIDN \"{$nim}\" tidak terdaftar sebagai mahasiswa/dosen.";
            if ($existing) {
                if ($owner && $owner->id !== $existing->user_id) $rowErrors[] = 'Pemilik data tidak bisa diganti saat edit (NIM/NIDN berbeda dari data aslinya).';
                $owner = $existing->user ?? User::find($existing->user_id);
            } elseif ($id === '' && $nim === '') {
                $rowErrors[] = 'nim_nidn wajib diisi untuk data baru.';
            }

            $jenis = $this->normalizeKey($d['jenis'] ?? '');
            $payload = [
                'user_id' => $owner?->id,
                'tipe_user' => $owner?->role === 'dosen' ? 'dosen' : 'mahasiswa',
                'jenis' => $jenis,
                'jenis_lainnya' => trim((string) ($d['jenis_lainnya'] ?? '')) ?: null,
                'arah' => $this->normalizeKey($d['arah'] ?? '') ?: null,
                'mitra' => trim((string) ($d['mitra'] ?? '')),
                'judul_kegiatan' => trim((string) ($d['judul_kegiatan'] ?? '')),
                'tanggal_mulai' => $this->parseDate($d['tanggal_mulai'] ?? ''),
                'tanggal_selesai' => $this->parseDate($d['tanggal_selesai'] ?? ''),
                'bukti_kegiatan' => trim((string) ($d['bukti_kegiatan'] ?? '')),
            ];

            $badDates = [];
            foreach (['tanggal_mulai', 'tanggal_selesai'] as $field) {
                if ($payload[$field] === false) {
                    $rowErrors[] = "{$field} \"" . ($d[$field] ?? '') . "\" tidak dikenali (pakai format 2026-09-30 atau 30/09/2026).";
                    $payload[$field] = null;
                    $badDates[] = $field;
                }
            }

            $validator = Validator::make($payload, self::RULES, [
                'required' => ':attribute wajib diisi.',
                'required_if' => ':attribute wajib diisi untuk jenis ini.',
                'in' => ':attribute tidak valid.',
                'url' => ':attribute harus berupa link lengkap (https://...).',
                'max' => ':attribute terlalu panjang.',
                'date' => ':attribute tidak valid (pakai format 2026-09-30 atau 30/09/2026).',
                'tanggal_selesai.after_or_equal' => 'tanggal_selesai tidak boleh sebelum tanggal_mulai.',
            ], [
                'jenis' => 'jenis','jenis_lainnya' => 'jenis_lainnya','arah' => 'arah','mitra' => 'mitra',
                'judul_kegiatan' => 'judul_kegiatan','tanggal_mulai' => 'tanggal_mulai',
                'tanggal_selesai' => 'tanggal_selesai','bukti_kegiatan' => 'bukti_kegiatan','user_id' => 'pemilik',
            ]);
            $messages = collect($validator->errors()->getMessages())->except(array_merge($owner ? [] : ['user_id'], $badDates))->flatten()->all();
            $rowErrors = array_merge($rowErrors, $messages);

            if (!$validator->errors()->has('jenis') && $jenis !== '' && ($msg = $this->jenisRestriction($payload['tipe_user'], $jenis))) $rowErrors[] = $msg;

            if ($rowErrors) {
                $errors[] = ['row' => $line, 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            if ($jenis !== 'lainnya') $payload['jenis_lainnya'] = null;
            if ($jenis !== 'guest_lecture') $payload['arah'] = null;
            $plan[] = ['existing' => $existing, 'payload' => $payload];
        }

        if ($errors) {
            return $this->importFailed(
                count($errors) . ' baris bermasalah. Tidak ada data yang disimpan — perbaiki baris di bawah lalu unggah ulang.',
                array_slice($errors, 0, 50),
                max(0, count($errors) - 50)
            );
        }

        $created = $updated = $unchanged = 0;
        $perOwner = [];
        DB::transaction(function () use ($plan, &$created, &$updated, &$unchanged, &$perOwner) {
            foreach ($plan as $p) {
                $payload = $p['payload'];
                if ($p['existing']) {
                    unset($payload['user_id'], $payload['tipe_user']);
                    $p['existing']->fill($payload);
                    if ($p['existing']->isDirty()) {
                        $p['existing']->save();
                        $updated++;
                        $perOwner[$p['existing']->user_id]['updated'] = ($perOwner[$p['existing']->user_id]['updated'] ?? 0) + 1;
                    } else $unchanged++;
                } else {
                    KerjaSama::create($payload);
                    $created++;
                    $perOwner[$payload['user_id']]['created'] = ($perOwner[$payload['user_id']]['created'] ?? 0) + 1;
                }
            }
        });

        foreach ($perOwner as $ownerId => $n) {
            $parts = [];
            if (!empty($n['created'])) $parts[] = "menambahkan {$n['created']} data";
            if (!empty($n['updated'])) $parts[] = "memperbarui {$n['updated']} data";
            $this->notifyUser($request, (int) $ownerId, 'Data kerja sama Anda diperbarui',
                ucfirst($request->user()->name) . ' (' . $request->user()->role . ') ' . implode(' dan ', $parts) . ' kerja sama Anda lewat unggah massal.');
        }

        return response()->json([
            'success' => true, 'created' => $created, 'updated' => $updated, 'unchanged' => $unchanged,
            'message' => "Berhasil: {$created} data ditambahkan, {$updated} diperbarui, {$unchanged} tidak berubah.",
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

    private function instructionSheet(): array
    {
        return [
            'name' => 'Petunjuk',
            'widths' => [22, 34, 24, 34, 34],
            'rows' => [
                [
                    ['value' => 'PETUNJUK IMPORT / EXPORT KERJA SAMA', 'style' => 2],
                    ['value' => ''],
                    ['value' => ''],
                    ['value' => ''],
                    ['value' => ''],
                ],
                [
                    ['value' => 'Cara menggunakan', 'style' => 2],
                    ['value' => 'Isi data pada sheet "Data Kerja Sama". Jangan mengubah nama kolom.', 'style' => 1],
                ],
                [
                    ['value' => 'ID', 'style' => 2],
                    ['value' => 'Kosong = tambah data baru. Isi ID dari hasil Download = edit data tersebut.', 'style' => 1],
                ],
                [
                    ['value' => 'NIM/NIDN', 'style' => 2],
                    ['value' => 'Harus sudah terdaftar di Data Master. Saat edit, pemilik tidak boleh diganti.', 'style' => 1],
                ],
                [
                    ['value' => 'Format tanggal', 'style' => 2],
                    ['value' => 'Gunakan YYYY-MM-DD, contoh 2026-09-30.', 'style' => 1],
                ],
                [
                    ['value' => 'Link bukti', 'style' => 2],
                    ['value' => 'Harus berupa URL lengkap, misalnya https://drive.google.com/...', 'style' => 1],
                ],
                [
                    ['value' => 'Jenis', 'style' => 2],
                    ['value' => 'conference_internasional', 'style' => 1],
                    ['value' => 'Mahasiswa & Dosen', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'pkl', 'style' => 1],
                    ['value' => 'Mahasiswa saja', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'sharing_session', 'style' => 1],
                    ['value' => 'Mahasiswa & Dosen', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'keynote_session', 'style' => 1],
                    ['value' => 'Dosen saja', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'guest_lecture', 'style' => 1],
                    ['value' => 'Dosen saja; wajib isi arah', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'pengabdian_internasional', 'style' => 1],
                    ['value' => 'Dosen saja', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'research_internasional', 'style' => 1],
                    ['value' => 'Dosen saja', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'lainnya', 'style' => 1],
                    ['value' => 'Mahasiswa & Dosen; wajib isi jenis_lainnya', 'style' => 1],
                ],
                [
                    ['value' => 'Arah guest lecture', 'style' => 2],
                    ['value' => 'inbound', 'style' => 1],
                    ['value' => 'Dosen', 'style' => 1],
                ],
                [
                    ['value' => '', 'style' => 2],
                    ['value' => 'outbound', 'style' => 1],
                    ['value' => 'Dosen', 'style' => 1],
                ],
                [
                    ['value' => 'Catatan', 'style' => 2],
                    ['value' => 'Kolom nama hanya informasi. Sistem menentukan pemilik dari NIM/NIDN.', 'style' => 1],
                ],
            ],
        ];
    }

    private function dataSheet(array $rows, bool $template): array
    {
        return [
            'name' => 'Data Kerja Sama',
            'widths' => [10, 18, 28, 30, 28, 16, 28, 34, 18, 18, 42],
            'rows' => array_map(function ($row, $index) {
                return array_map(function ($value) use ($index) {
                    return ['value' => $this->safeCell($value), 'style' => $index === 0 ? 2 : 1];
                }, $row);
            }, $rows, array_keys($rows)),
            'validations' => [
                ['range' => 'D2:D1001', 'formula' => 'conference_internasional,pkl,sharing_session,keynote_session,guest_lecture,pengabdian_internasional,research_internasional,lainnya', 'error' => 'Pilih jenis yang tersedia di sheet Petunjuk.'],
                ['range' => 'F2:F1001', 'formula' => 'inbound,outbound', 'error' => 'Pilih inbound atau outbound.'],
            ],
            'freeze' => true,
            'autofilter' => true,
        ];
    }

    private function xlsxResponse(string $filename, array $sheets)
    {
        if (!class_exists(ZipArchive::class)) {
            abort(500, 'Ekstensi PHP ZIP (ZipArchive) belum aktif. Aktifkan extension=zip pada PHP.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'kerja_sama_xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes(count($sheets)));
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook($sheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels(count($sheets)));
        $zip->addFromString('xl/styles.xml', $this->xlsxStyles());
        foreach (array_values($sheets) as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->xlsxSheet($sheet));
        }
        $zip->close();

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function xlsxSheet(array $sheet): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        if (!empty($sheet['freeze'])) {
            $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        }
        $xml .= '<sheetFormatPr defaultRowHeight="20"/><cols>';
        foreach ($sheet['widths'] ?? [] as $i => $width) {
            $n = $i + 1;
            $xml .= '<col min="' . $n . '" max="' . $n . '" width="' . (float) $width . '" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';
        foreach ($sheet['rows'] ?? [] as $r => $cells) {
            $rowNum = $r + 1;
            $xml .= '<row r="' . $rowNum . '">';
            foreach ($cells as $c => $cell) {
                $value = is_array($cell) ? (string) ($cell['value'] ?? '') : (string) $cell;
                $style = is_array($cell) ? (int) ($cell['style'] ?? 1) : 1;
                $ref = $this->xlsxColumn($c + 1) . $rowNum;
                $xml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</t></is></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';
        if (!empty($sheet['autofilter']) && !empty($sheet['rows'])) {
            $last = $this->xlsxColumn(count($sheet['rows'][0]));
            $xml .= '<autoFilter ref="A1:' . $last . count($sheet['rows']) . '"/>';
        }
        foreach ($sheet['validations'] ?? [] as $v) {
            $xml .= '<dataValidations count="1"><dataValidation type="list" allowBlank="1" showErrorMessage="1" errorStyle="stop" errorTitle="Pilihan tidak valid" error="' . htmlspecialchars($v['error'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '" sqref="' . $v['range'] . '"><formula1>"' . htmlspecialchars($v['formula'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '"</formula1></dataValidation></dataValidations>';
        }
        $xml .= '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/></worksheet>';
        return $xml;
    }

    private function xlsxWorkbook(array $sheets): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        foreach (array_values($sheets) as $i => $sheet) {
            $xml .= '<sheet name="' . htmlspecialchars($sheet['name'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }
        return $xml . '</sheets></workbook>';
    }

    private function xlsxWorkbookRels(int $count): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= $count; $i++) {
            $xml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        return $xml . '</Relationships>';
    }

    private function xlsxContentTypes(int $count): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        for ($i = 1; $i <= $count; $i++) {
            $xml .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        return $xml . '</Types>';
    }

    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="0"/><fonts count="2"><font><sz val="11"/><name val="Aptos"/></font><font><b/><sz val="11"/><name val="Aptos"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="solid"><fgColor rgb="E8F0FE"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="1" borderId="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function xlsxColumn(int $n): string
    {
        $s = '';
        while ($n > 0) {
            $n--;
            $s = chr(65 + ($n % 26)) . $s;
            $n = intdiv($n, 26);
        }
        return $s;
    }

    /**
     * Baca sheet xlsx dengan inline strings maupun shared strings (Excel/LibreOffice).
     * @return array{0: array<int,string>, 1: array<int,array{line:int,data:array<string,string>}>}
     */
    private function readXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) throw new \RuntimeException('Ekstensi PHP ZIP (ZipArchive) belum aktif.');
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new \RuntimeException('File Excel tidak dapat dibuka. Pastikan file .xlsx valid.');

        $wb = simplexml_load_string((string) $zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));
        if (!$wb || !$rels) { $zip->close(); throw new \RuntimeException('Struktur file Excel tidak valid.'); }

        $wbn = $wb->getNamespaces(true);
        $main = $wbn[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $rns = $wbn['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $wb->registerXPathNamespace('m', $main);
        $rels->registerXPathNamespace('p', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $target = null;
        foreach ($wb->xpath('//m:sheets/m:sheet') ?: [] as $sh) {
            if ((string) $sh['name'] === 'Data Kerja Sama') { $target = $sh; break; }
        }
        $target ??= ($wb->xpath('//m:sheets/m:sheet')[0] ?? null);
        if (!$target) { $zip->close(); throw new \RuntimeException('Sheet data tidak ditemukan.'); }

        $rid = (string) $target->attributes($rns)->id;
        $sheetPath = null;
        foreach ($rels->xpath('//p:Relationship') ?: [] as $rel) {
            if ((string) $rel['Id'] === $rid) {
                $sheetPath = 'xl/' . ltrim((string) $rel['Target'], '/');
                break;
            }
        }
        if (!$sheetPath || $zip->locateName($sheetPath) === false) { $zip->close(); throw new \RuntimeException('Sheet data Excel tidak dapat dibaca.'); }

        $shared = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $ss = simplexml_load_string((string) $zip->getFromName('xl/sharedStrings.xml'));
            if ($ss) {
                $sns = $ss->getNamespaces(true);
                $ss->registerXPathNamespace('m', $sns[''] ?? $main);
                foreach ($ss->xpath('//m:si') ?: [] as $si) {
                    $texts = $si->xpath('.//m:t') ?: [];
                    $shared[] = implode('', array_map(fn ($t) => (string) $t, $texts));
                }
            }
        }

        $sx = simplexml_load_string((string) $zip->getFromName($sheetPath));
        $zip->close();
        if (!$sx) throw new \RuntimeException('Isi sheet Excel tidak valid.');
        $sns = $sx->getNamespaces(true);
        $sx->registerXPathNamespace('m', $sns[''] ?? $main);

        $rows = [];
        foreach ($sx->xpath('//m:sheetData/m:row') ?: [] as $rowNode) {
            $cells = [];
            foreach ($rowNode->xpath('./m:c') ?: [] as $cell) {
                $ref = (string) $cell['r'];
                preg_match('/([A-Z]+)\d+$/', $ref, $m);
                $col = $this->xlsxColumnIndex($m[1] ?? 'A');
                $type = (string) $cell['t'];
                if ($type === 'inlineStr') {
                    $texts = $cell->xpath('.//m:t') ?: [];
                    $value = implode('', array_map(fn ($t) => (string) $t, $texts));
                } elseif ($type === 's') {
                    $value = $shared[(int) ($cell->v ?? -1)] ?? '';
                } else {
                    $value = (string) ($cell->v ?? '');
                }
                $cells[$col] = $this->unsafeCell(trim($value));
            }
            if ($cells) {
                $max = max(array_keys($cells));
                $data = array_fill(0, $max + 1, '');
                foreach ($cells as $i => $v) $data[$i] = $v;
                $rows[] = ['line' => (int) $rowNode['r'], 'data' => $data];
            }
        }

        if (!$rows) throw new \RuntimeException('Sheet Data Kerja Sama kosong.');
        $aliases = [
            'nim' => 'nim_nidn','nidn' => 'nim_nidn','nim_nidn' => 'nim_nidn','nim/nidn' => 'nim_nidn',
            'judul' => 'judul_kegiatan','bukti' => 'bukti_kegiatan','link_bukti' => 'bukti_kegiatan',
            'tgl_mulai' => 'tanggal_mulai','tgl_selesai' => 'tanggal_selesai',
        ];
        $header = array_map(function ($h) use ($aliases) {
            $h = $this->normalizeKey($h);
            return $aliases[$h] ?? $h;
        }, $rows[0]['data']);
        $out = [];
        foreach (array_slice($rows, 1) as $row) {
            $cells = $row['data'];
            if (!array_filter($cells, fn ($v) => trim((string) $v) !== '')) continue;
            $d = [];
            foreach ($header as $i => $name) if ($name !== '' && !isset($d[$name])) $d[$name] = (string) ($cells[$i] ?? '');
            $out[] = ['line' => $row['line'], 'data' => $d];
        }
        return [array_values(array_filter($header)), $out];
    }

    private function xlsxColumnIndex(string $letters): int
    {
        $n = 0;
        foreach (str_split($letters) as $char) $n = $n * 26 + ord($char) - 64;
        return max(0, $n - 1);
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