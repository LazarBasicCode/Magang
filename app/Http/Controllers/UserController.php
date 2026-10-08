<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\UserNotification;
use App\Http\Controllers\Concerns\HandlesBulkData;
use App\Support\Csv;
use App\Support\SessionRevoker;
use App\Support\Xlsx;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use HandlesBulkData;

    /**
     * Halaman utama Data Master Pengguna (server-rendered untuk load pertama).
     * Aksi tambah/edit/hapus selanjutnya berjalan lewat fetch() tanpa reload.
     */
    public function index(Request $request)
    {
        $users = User::with(['mahasiswa', 'dosen'])->latest()->paginate(10);

        $stats = [
            'total'     => User::count(),
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'dosen'     => User::where('role', 'dosen')->count(),
            'admin'     => User::whereIn('role', ['admin', 'superadmin'])->count(),
        ];

        return view('data-master-users', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, isUpdate: false);

        // Admin cuma boleh membuat akun dosen/mahasiswa. Tanpa cek ini, admin
        // bisa membuat akun admin/superadmin baru lewat form yang sama
        // (privilege escalation via akun baru, bukan cuma edit akun lama).
        $actor = $request->user();
        if ($actor->role !== 'superadmin' && !in_array($data['role'], ['mahasiswa', 'dosen'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya boleh membuat akun dengan role Dosen atau Mahasiswa.',
            ], 403);
        }

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'nim_nidn' => $data['identifier'] ?? null,
                'email'    => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            $this->syncIdentifier($user, $data['role'], $data['identifier'] ?? null, $data);

            return $user->load('mahasiswa', 'dosen');
        });

        return response()->json([
            'success' => true,
            'data'    => $this->format($user),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();

        // --- Otorisasi berbasis hierarki role, tidak cukup hanya level menu ---

        // (6) Admin tidak boleh menaikkan role dirinya sendiri (atau mengubah
        // role dirinya sama sekali) lewat form ini. Dicek sebelum aturan
        // umum di bawah karena kasus "edit diri sendiri" butuh pesan
        // spesifik dan tidak boleh disamakan dengan "kelola user lain".
        if ($actor->id === $user->id) {
            // Pakai filled(), bukan input(), sebagai jaring pengaman: kalau
            // request tidak mengirim 'role' sama sekali (misal klien API lain
            // di luar form saat ini, atau form berubah nanti), kita tidak mau
            // membandingkan null !== $actor->role (selalu true) yang akan
            // memblokir dengan pesan "ubah role" padahal masalahnya cuma
            // field hilang. validated() di bawah tetap akan menolak request
            // semacam itu dengan pesan yang benar (role wajib diisi).
            if ($actor->role !== 'superadmin' && $request->filled('role') && $request->input('role') !== $actor->role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak boleh mengubah role akun Anda sendiri.',
                ], 403);
            }
        } elseif (!$actor->canManageTargetUser($user)) {
            // (1) (2) (3) Admin tidak boleh mengelola superadmin, sesama
            // admin, atau siapa pun di luar dosen/mahasiswa. Dosen/mahasiswa
            // tidak boleh mengelola user lain sama sekali.
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk mengubah data pengguna ini.',
            ], 403);
        }

        $data = $this->validated($request, isUpdate: true, user: $user);

        // (3) Admin hanya boleh menempatkan/mempertahankan target sebagai
        // dosen/mahasiswa — mencegah admin "menaikkan" user lain menjadi
        // admin/superadmin lewat field role pada form edit ini.
        if ($actor->role !== 'superadmin' && $actor->id !== $user->id
            && !in_array($data['role'], ['mahasiswa', 'dosen'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya boleh menetapkan role Dosen atau Mahasiswa.',
            ], 403);
        }

        // (5) Superadmin terakhir tidak boleh "dilucuti" jadi role lain
        // lewat edit role (setara dengan menghapusnya secara fungsional).
        if ($user->role === 'superadmin' && $data['role'] !== 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mengubah role superadmin terakhir yang tersisa di sistem.',
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $user, $data) {
            $user->name = $data['name'];
            $user->role = $data['role'];
            $user->nim_nidn = $data['identifier'] ?? null;
            $user->email = $data['email'] ?? null;
            $passwordChanged = !empty($data['password']);
            if ($passwordChanged) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // Password berubah -> semua sesi login akun itu dikeluarkan (mencegah penyusup yang sudah masuk).
            // Kalau yang diubah akun si pengubah sendiri, sesi yang sedang dipakai dipertahankan agar form ini tetap jalan.
            if ($passwordChanged) {
                $self = $request->user()?->id === $user->id;
                SessionRevoker::revoke(
                    $user,
                    $self ? SessionRevoker::PASSWORD_CHANGED : SessionRevoker::PASSWORD_ADMIN,
                    $self ? $request->session()->getId() : null
                );
            }

            $this->syncIdentifier($user, $data['role'], $data['identifier'] ?? null, $data);

            // Beri tahu pemilik akun kalau datanya diubah oleh orang lain
            // (admin/superadmin) — bukan oleh dirinya sendiri.
            $actor = $request->user();
            if ($actor && $actor->id !== $user->id) {
                UserNotification::send($user->id, 'data_updated', [
                    'title'       => 'Data akun Anda diperbarui',
                    'description' => "Diubah oleh {$actor->name} ({$actor->role}).",
                    'data'        => ['actor_id' => $actor->id, 'actor_name' => $actor->name],
                ]);
            }
        });

        $user->load('mahasiswa', 'dosen');

        return response()->json([
            'success' => true,
            'data'    => $this->format($user),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();

        // (4) User tidak boleh menghapus dirinya sendiri lewat Data Master.
        if ($actor->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri.',
            ], 403);
        }

        // (1) (2) (3) Bug utama: middleware menu.access:data_master,biasa
        // hanya memastikan admin PUNYA akses ke menu Data Master — bukan
        // memeriksa SIAPA yang boleh mereka hapus. Tanpa cek ini, admin
        // dengan level "biasa" bisa menghapus superadmin atau admin lain.
        if (!$actor->canManageTargetUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk menghapus pengguna ini.',
            ], 403);
        }

        // (5) Superadmin terakhir tidak boleh dihapus, siapa pun pelakunya
        // (termasuk sesama superadmin), supaya sistem tidak pernah
        // kehilangan seluruh akses superadmin-nya.
        if ($user->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus superadmin terakhir yang tersisa di sistem.',
                ], 422);
            }
        }

        $id = $user->id;
        $user->mahasiswa()->delete();
        $user->dosen()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    // =====================================================================
    // UNGGAH / UNDUH MASSAL (EXCEL .xlsx) — khusus admin & superadmin
    // Kerangka umum: Concerns\HandlesBulkData + Support\Xlsx.
    // Aturan keamanan (sengaja ketat, karena ini data akun & password):
    //  - Admin hanya boleh membuat/mengubah akun dosen & mahasiswa; superadmin boleh semua role.
    //  - Role akun yang sudah ada TIDAK bisa diubah lewat unggah massal (ubah lewat form edit).
    //  - Password tidak pernah diekspor. Kolom password saat edit: kosong = tidak diganti.
    // =====================================================================

    private const SHEET = 'Data Master';

    protected function bulkMenu(): string
    {
        return 'data_master';
    }

    // Model User tidak punya relasi pemilik; relasi ini hanya dipakai bulkExistingById().
    protected function bulkOwnerWith(): string
    {
        return 'mahasiswa';
    }

    protected function bulkSheetName(): ?string
    {
        return self::SHEET;
    }

    protected function bulkExtraAliases(): array
    {
        return [
            'name'             => 'nama',
            'peran'            => 'role',
            'kata_sandi'       => 'password',
            // Alias untuk kolom baru: angkatan & status
            'tahun_angkatan'   => 'angkatan',
            'tahun'            => 'angkatan',
            'status_mahasiswa' => 'status',
            'status_dosen'     => 'status',
        ];
    }

    /** Role yang boleh dibuat lewat unggah massal oleh user yang sedang login. */
    private function bulkRoles(Request $request): array
    {
        return $request->user()->role === 'superadmin'
            ? ['mahasiswa', 'dosen', 'admin', 'superadmin']
            : ['mahasiswa', 'dosen'];
    }

    /** Unduh template Excel (.xlsx): sheet petunjuk + sheet data, dengan dropdown role & status. */
    public function template(Request $request)
    {
        $this->bulkEnsureAccess($request, true);
        $roles = $this->bulkRoles($request);

        // Dropdown status digabung dari Mahasiswa::STATUS + Dosen::STATUS.
        // Key-nya sama (aktif, cuti, lulus, dll) — kalau ada beda, union tetap aman.
        $statusOptions = array_values(array_unique(array_merge(
            array_keys(Mahasiswa::STATUS),
            array_keys(Dosen::STATUS),
        )));

        $maxYear = now()->year + 1;

        return $this->bulkXlsxTemplateResponse(
            'template-data-master.xlsx',
            'PETUNJUK IMPORT / EXPORT DATA MASTER',
            self::SHEET,
            [
                'id'       => ['required' => 'Tidak',          'example' => '',                'width' => 10, 'note' => 'KOSONGKAN untuk akun baru. Isi id (dari hasil Download) untuk MENGEDIT akun yang sudah ada.'],
                'nim_nidn' => ['required' => 'Ya',             'example' => '2210001',         'width' => 18, 'note' => 'NIM / NIDN, dipakai sebagai username login. Harus unik. Tulis persis, termasuk 0 di depan.'],
                'nama'     => ['required' => 'Ya',             'example' => 'Budi Santoso',    'width' => 30, 'note' => 'Nama lengkap pengguna.'],
                'role'     => ['required' => 'Ya',             'example' => 'mahasiswa',       'width' => 16, 'options' => $roles, 'note' => 'Pilih dari dropdown: ' . implode(' | ', $roles) . '. Role akun yang sudah ada tidak bisa diubah di sini.'],
                'email'    => ['required' => 'Tidak',          'example' => '',                'width' => 30, 'note' => 'Email pemulihan password (opsional). Kalau diisi harus unik.'],
                'angkatan' => ['required' => 'Ya (mahasiswa)', 'example' => '2022',            'width' => 12, 'note' => 'Hanya untuk role mahasiswa. Tahun 4 digit (1990–' . $maxYear . '). Kosongkan untuk dosen/admin/superadmin.'],
                'status'   => ['required' => 'Ya (mhs/dosen)', 'example' => 'aktif',           'width' => 14, 'options' => $statusOptions, 'note' => 'Wajib untuk mahasiswa/dosen (pilihan: ' . implode(' | ', $statusOptions) . '). Kosongkan untuk admin/superadmin.'],
                'password' => ['required' => 'Ya (akun baru)', 'example' => 'rahasia123',      'width' => 20, 'note' => 'Minimal 6 karakter. Saat edit: kosongkan kalau password tidak diganti.'],
            ],
            [
                'Isi data di sheet "' . self::SHEET . '", mulai dari baris di bawah judul kolom. Baris "# CONTOH" boleh dihapus.',
                'Kolom id: kosongkan untuk akun baru, isi id (dari hasil Download) untuk mengedit akun.',
                'Kolom angkatan & status: WAJIB untuk mahasiswa/dosen, kosongkan untuk admin/superadmin.',
                'Jangan mengubah judul kolom. Simpan tetap sebagai .xlsx.',
                'File ini berisi password asli: hapus file setelah diunggah dan jangan dibagikan.',
                'Maksimal ' . $this->bulkMaxRows() . ' baris per unggahan, ukuran file maksimal 2 MB.',
            ]
        );
    }

    /** Unduh data akun sebagai Excel (tanpa password). Admin hanya melihat dosen & mahasiswa. */
    public function export(Request $request)
    {
        $this->bulkEnsureAccess($request, false);

        $cell = fn ($v, $style = Xlsx::STYLE_CELL) => ['value' => (string) $v, 'style' => $style];
        $header = ['id', 'nim_nidn', 'nama', 'role', 'email', 'angkatan', 'status', 'password'];
        $rows = [array_map(fn ($h) => $cell($h, Xlsx::STYLE_HEADER), $header)];

        User::with(['mahasiswa', 'dosen'])
            ->when($request->user()->role !== 'superadmin', fn ($q) => $q->whereIn('role', ['mahasiswa', 'dosen']))
            ->chunkById(500, function ($users) use (&$rows, $cell) {
                foreach ($users as $u) {
                    // Angkatan hanya untuk mahasiswa; status diambil dari relasi yang sesuai.
                    $angkatan = $u->role === 'mahasiswa' ? ($u->mahasiswa?->angkatan ?? '') : '';
                    $status = match ($u->role) {
                        'mahasiswa' => $u->mahasiswa?->status ?? '',
                        'dosen'     => $u->dosen?->status ?? '',
                        default     => '',
                    };

                    // Kolom password sengaja kosong: hash tidak boleh keluar, kosong = tidak diganti saat diunggah ulang.
                    $rows[] = array_map(fn ($v) => $cell(Csv::safeCell($v)), [
                        $u->id, $u->nim_nidn, $u->name, $u->role, $u->email,
                        $angkatan, $status,
                        '',
                    ]);
                }
            });

        return Xlsx::download('data-master-' . now()->format('Ymd-His') . '.xlsx', [[
            'name' => self::SHEET, 'widths' => [10, 18, 30, 16, 30, 12, 14, 20], 'rows' => $rows, 'freeze' => true, 'autofilter' => true,
            'autoborder' => ['cols' => count($header), 'from' => 2, 'to' => max(count($rows) + 1000, 2000)],
        ]]);
    }

    /**
     * Unggah Excel (.xlsx) atau CSV: baris dengan id => edit akun itu, tanpa id => akun baru.
     * Semua baris divalidasi dulu; kalau ada yang bermasalah, TIDAK ADA data yang disimpan.
     */
    public function import(Request $request)
    {
        $this->bulkEnsureAccess($request, true);

        $parsed = $this->bulkParseUpload($request, ['nim_nidn', 'nama', 'role']);
        if ($parsed instanceof JsonResponse) {
            return $parsed;
        }
        [, $rows] = $parsed;

        $actor = $request->user();
        $roles = $this->bulkRoles($request);
        $existingById = $this->bulkExistingById(User::class, $rows);

        $maxYear = now()->year + 1;
        $plan = [];
        $errors = [];
        $seenNim = [];
        $seenEmail = [];

        foreach ($rows as $row) {
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

            $payload = [
                'name'     => trim((string) ($d['nama'] ?? '')),
                'nim_nidn' => trim((string) ($d['nim_nidn'] ?? '')),
                'role'     => Csv::normalizeKey($d['role'] ?? ''),
                'email'    => strtolower(trim((string) ($d['email'] ?? ''))) ?: null,
                'angkatan' => trim((string) ($d['angkatan'] ?? '')),
                'status'   => Csv::normalizeKey($d['status'] ?? ''),
                'password' => (string) ($d['password'] ?? ''),
            ];

            // Aturan angkatan/status mengikuti role di baris itu.
            $angkatanRule = $payload['role'] === 'mahasiswa'
                ? ['required', 'integer', 'between:1990,' . $maxYear]
                : ['nullable'];

            $statusRule = match ($payload['role']) {
                'mahasiswa' => ['required', Rule::in(array_keys(Mahasiswa::STATUS))],
                'dosen'     => ['required', Rule::in(array_keys(Dosen::STATUS))],
                default     => ['nullable'],
            };

            $validator = Validator::make($payload, [
                'name'     => ['required', 'string', 'max:255'],
                // Akun lama: role dikunci (dicek di bawah). Akun baru: hanya role yang boleh dibuat user ini.
                'role'     => $existing ? ['required'] : ['required', Rule::in($roles)],
                'nim_nidn' => ['required', 'string', 'max:50', Rule::unique('users', 'nim_nidn')->ignore($existing?->id)],
                'email'    => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($existing?->id)],
                'angkatan' => $angkatanRule,
                'status'   => $statusRule,
                'password' => [$existing ? 'nullable' : 'required', 'string', 'min:6'],
            ], [
                'required'          => ':attribute wajib diisi.',
                'in'                => ':attribute tidak valid atau tidak boleh Anda buat (boleh: ' . implode(', ', $roles) . ').',
                'unique'            => ':attribute sudah dipakai akun lain.',
                'email'             => ':attribute bukan alamat email yang valid.',
                'min'               => ':attribute minimal :min karakter.',
                'max'               => ':attribute terlalu panjang.',
                'integer'           => ':attribute harus berupa angka tahun (contoh: 2022).',
                'between'           => ':attribute harus di antara 1990 dan ' . $maxYear . '.',
                'angkatan.required' => 'angkatan wajib diisi untuk mahasiswa.',
                'angkatan.integer'  => 'angkatan harus berupa angka tahun (contoh: 2022).',
                'angkatan.between'  => 'angkatan harus di antara 1990 dan ' . $maxYear . '.',
                'status.required'   => 'status wajib diisi untuk mahasiswa/dosen.',
                'status.in'         => 'status tidak valid (pilihan: ' . implode(', ', array_keys(Mahasiswa::STATUS)) . ').',
            ], ['name' => 'nama', 'nim_nidn' => 'nim_nidn', 'role' => 'role', 'email' => 'email', 'angkatan' => 'angkatan', 'status' => 'status', 'password' => 'password']);
            $rowErrors = array_merge($rowErrors, $validator->errors()->all());

            if ($existing) {
                if ($payload['role'] !== '' && $payload['role'] !== $existing->role) {
                    $rowErrors[] = "Role tidak bisa diubah lewat unggah massal (role saat ini: {$existing->role}). Ubah lewat form edit.";
                }
                if ($actor->id !== $existing->id && !$actor->canManageTargetUser($existing)) {
                    $rowErrors[] = 'Anda tidak berhak mengubah akun ini.';
                }
            }

            // Duplikat di dalam file yang sama (belum ada di database, jadi lolos aturan unique di atas).
            if ($payload['nim_nidn'] !== '') {
                if (isset($seenNim[$payload['nim_nidn']])) {
                    $rowErrors[] = "nim_nidn \"{$payload['nim_nidn']}\" muncul lagi di baris {$seenNim[$payload['nim_nidn']]}.";
                } else {
                    $seenNim[$payload['nim_nidn']] = $row['line'];
                }
            }
            if ($payload['email']) {
                if (isset($seenEmail[$payload['email']])) {
                    $rowErrors[] = "email \"{$payload['email']}\" muncul lagi di baris {$seenEmail[$payload['email']]}.";
                } else {
                    $seenEmail[$payload['email']] = $row['line'];
                }
            }

            if ($rowErrors) {
                $errors[] = ['row' => $row['line'], 'messages' => array_values(array_unique($rowErrors))];
                continue;
            }

            $plan[] = ['existing' => $existing, 'payload' => $payload];
        }

        if ($errors) {
            return $this->bulkRejected($errors);
        }

        $created = $updated = $unchanged = 0;
        $notify = [];

        DB::transaction(function () use ($plan, $actor, $request, &$created, &$updated, &$unchanged, &$notify) {
            foreach ($plan as $p) {
                $pl = $p['payload'];

                if ($p['existing']) {
                    $user = $p['existing'];
                    $user->name = $pl['name'];
                    $user->nim_nidn = $pl['nim_nidn'];
                    $user->email = $pl['email'];
                    $passwordChanged = $pl['password'] !== '';
                    if ($passwordChanged) {
                        $user->password = Hash::make($pl['password']);
                    }
                    if ($user->isDirty()) {
                        $user->save();
                        if ($passwordChanged) {
                            // Sama seperti edit lewat form: password berubah -> semua sesi akun itu dikeluarkan.
                            $self = $user->id === $actor->id;
                            SessionRevoker::revoke(
                                $user,
                                $self ? SessionRevoker::PASSWORD_CHANGED : SessionRevoker::PASSWORD_ADMIN,
                                $self ? $request->session()->getId() : null
                            );
                        }
                        $this->syncIdentifier($user, $user->role, $pl['nim_nidn'], [
                            'angkatan' => $pl['angkatan'] !== '' ? $pl['angkatan'] : null,
                            'status'   => $pl['status']   !== '' ? $pl['status']   : null,
                        ]);
                        $updated++;
                        if ($user->id !== $actor->id) {
                            $notify[] = $user->id;
                        }
                    } else {
                        $unchanged++;
                    }
                } else {
                    $user = User::create([
                        'name'     => $pl['name'],
                        'nim_nidn' => $pl['nim_nidn'],
                        'email'    => $pl['email'],
                        'password' => Hash::make($pl['password']),
                        'role'     => $pl['role'],
                    ]);
                    $this->syncIdentifier($user, $pl['role'], $pl['nim_nidn'], [
                        'angkatan' => $pl['angkatan'] !== '' ? $pl['angkatan'] : null,
                        'status'   => $pl['status']   !== '' ? $pl['status']   : null,
                    ]);
                    $created++;
                }
            }
        });

        // Sama seperti edit lewat form: beri tahu pemilik akun yang datanya diubah orang lain.
        foreach (array_unique($notify) as $userId) {
            UserNotification::send($userId, 'data_updated', [
                'title'       => 'Data akun Anda diperbarui',
                'description' => "Diubah oleh {$actor->name} ({$actor->role}) lewat unggah massal.",
                'data'        => ['actor_id' => $actor->id, 'actor_name' => $actor->name],
            ]);
        }

        return $this->bulkSuccess($created, $updated, $unchanged);
    }

    private function validated(Request $request, bool $isUpdate, ?User $user = null): array
    {
        // Angkatan & status hanya relevan (dan wajib) untuk role tertentu:
        // - mahasiswa: angkatan + status akademik
        // - dosen    : status dosen
        // - admin/superadmin: tidak punya keduanya (diabaikan).
        $role = $request->input('role');
        $maxYear = now()->year + 1;

        $angkatanRule = $role === 'mahasiswa'
            ? ['required', 'integer', 'between:1990,' . $maxYear]
            : ['nullable'];

        $statusRule = match ($role) {
            'mahasiswa' => ['required', Rule::in(array_keys(Mahasiswa::STATUS))],
            'dosen'     => ['required', Rule::in(array_keys(Dosen::STATUS))],
            default     => ['nullable'],
        };

        return $request->validate([
            'angkatan'   => $angkatanRule,
            'status'     => $statusRule,
            'name'       => ['required', 'string', 'max:255'],
            'role'       => ['required', 'in:superadmin,admin,dosen,mahasiswa'],
            'password'   => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6'],
            // Wajib untuk SEMUA role: kolom ini (users.nim_nidn) dipakai sebagai
            // kredensial login. Admin/superadmin tanpa nilai ini tidak akan
            // pernah bisa login. Harus unik supaya login tidak ambigu.
            'identifier' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'nim_nidn')->ignore($user?->id),
            ],
            // Email opsional — dipakai untuk fitur lupa password. Kalau diisi,
            // harus unik supaya tautan reset tidak salah sasaran ke akun lain.
            'email' => [
                'nullable', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
        ], [
            'angkatan.required' => 'Angkatan wajib diisi untuk mahasiswa.',
            'angkatan.integer'  => 'Angkatan harus berupa angka tahun (contoh: 2022).',
            'angkatan.between'  => 'Angkatan harus di antara 1990 dan ' . $maxYear . '.',
            'status.required'   => 'Status wajib dipilih.',
            'status.in'         => 'Status yang dipilih tidak valid.',
        ]);
    }

    /**
     * Sinkronkan baris mahasiswa/dosen sesuai role terbaru.
     * Kalau role berubah, baris relasi lama yang tidak relevan dihapus.
     * Catatan: users.nim_nidn ditulis terpisah di store()/update() karena
     * kolom itu ada langsung di tabel users, di luar tabel mahasiswa/dosen.
     *
     * $profile (opsional) berisi 'angkatan' & 'status' dari form. Kalau null
     * (mis. unggah massal yang tidak membawa kolom itu), angkatan/status yang
     * sudah tersimpan TIDAK ditimpa.
     */
    private function syncIdentifier(User $user, string $role, ?string $identifier, ?array $profile = null): void
    {
        if ($role !== 'mahasiswa') {
            Mahasiswa::where('user_id', $user->id)->delete();
        }
        if ($role !== 'dosen') {
            Dosen::where('user_id', $user->id)->delete();
        }

        if ($role === 'mahasiswa') {
            $attrs = ['nim' => $identifier];
            if ($profile !== null) {
                $attrs['angkatan'] = $profile['angkatan'] ?? null;
                $attrs['status']   = $profile['status'] ?? 'aktif';
            }
            Mahasiswa::updateOrCreate(['user_id' => $user->id], $attrs);
        } elseif ($role === 'dosen') {
            $attrs = ['nidn' => $identifier];
            if ($profile !== null) {
                $attrs['status'] = $profile['status'] ?? 'aktif';
            }
            Dosen::updateOrCreate(['user_id' => $user->id], $attrs);
        }
    }

    /**
     * Bentuk payload JSON yang dikonsumsi JS untuk membangun/mengganti baris tabel.
     */
    private function format(User $user): array
    {
        $profile = match ($user->role) {
            'mahasiswa' => $user->mahasiswa,
            'dosen'     => $user->dosen,
            default     => null,
        };

        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'role'         => $user->role,
            'identifier'   => $user->nim_nidn,
            'email'        => $user->email,
            'angkatan'     => $user->role === 'mahasiswa' ? $user->mahasiswa?->angkatan : null,
            'status'       => $profile?->status,
            'status_label' => $profile?->statusLabel(),
            'status_badge' => $profile?->statusBadge(),
        ];
    }
}