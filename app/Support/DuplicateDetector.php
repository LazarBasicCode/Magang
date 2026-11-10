<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Deteksi data duplikat — SATU-SATUNYA tempat logika ini hidup.
 *
 * Controller tidak lagi punya pencari duplikat sendiri. Cukup pilih profil:
 *
 *   $dup = DuplicateDetector::for('kegiatan')->check($data, $ignoreId);   // simpan / ubah satu data
 *   $dup = DuplicateDetector::for('akun')->checkBatch($entries);          // unggah massal
 *   if ($blocked = DuplicateDetector::guard($request, $dup)) return $blocked;   // 409 bila perlu konfirmasi
 *   ... simpan ...
 *   response()->json([... , ...DuplicateDetector::warnings($dup)]);       // peringatan "mirip"
 *
 * Definisi:
 *  - exact   : konteks (scope) sama dan nama/judul sama persis setelah dinormalisasi
 *              -> wajib dikonfirmasi pengguna (`confirm_duplicates`), kalau tidak respons 409.
 *  - similar : konteks sama dan nama/judul mirip (>= 85%) -> hanya peringatan, tetap tersimpan.
 *
 * Menambah jenis data baru yang perlu deteksi duplikat = menambah satu entri di profiles().
 */
final class DuplicateDetector
{
    /** Ambang kemiripan teks (persen) untuk peringatan "mirip". */
    private const SIMILARITY_THRESHOLD = 85;

    private const MAX_EXACT_SHOWN = 10;

    private const MAX_WARNINGS_SHOWN = 5;

    /** Jumlah nilai konteks yang dimasukkan ke satu `whereIn` saat mengambil kandidat. */
    private const FETCH_CHUNK = 500;

    private function __construct(private readonly array $profile)
    {
    }

    // =====================================================================
    // PROFIL — satu entri per jenis data
    //   model     : model untuk mengambil kandidat dari database
    //   columns   : kolom yang diambil
    //   scope     : kolom konteks; duplikat hanya dicari di data dengan nilai konteks yang sama
    //   int       : bagian scope yang dibandingkan sebagai angka (mis. id, tahun)
    //   label     : kolom yang dibandingkan: 'nama' | ['mitra', 'judul'] (digabung) | closure(array $row)
    //   text      : awal kalimat peringatan "mirip"
    //   reference : tampilkan "(ID #n)" pada peringatan
    // =====================================================================

    private static function profiles(): array
    {
        return [
            // Akun pengguna: nama dibandingkan hanya dengan akun di role yang sama.
            'akun' => [
                'model'     => \App\Models\User::class,
                'columns'   => ['id', 'name', 'role'],
                'scope'     => ['role'],
                'int'       => [],
                'label'     => 'name',
                'text'      => 'Nama akun',
                'reference' => true,
            ],

            // Kemahasiswaan: pemilik, jenis kegiatan, tab, tingkat, dan tahun yang sama.
            'kegiatan' => [
                'model'     => \App\Models\Kemahasiswaan::class,
                'columns'   => ['id', 'mahasiswa_id', 'jenis', 'tab', 'tingkat', 'tahun', 'nama_kegiatan'],
                'scope'     => ['mahasiswa_id', 'jenis', 'tab', 'tingkat', 'tahun'],
                'int'       => ['mahasiswa_id', 'tahun'],
                'label'     => 'nama_kegiatan',
                'text'      => 'Nama kegiatan',
                'reference' => false,
            ],

            // Rekognisi: pemilik, jenis karya, dan tahun yang sama.
            // Jenis "rekognisi" (alumni) tidak punya judul -> dibandingkan lewat mitra + jabatan.
            'rekognisi' => [
                'model'     => \App\Models\Rekognisi::class,
                'columns'   => ['id', 'mahasiswa_id', 'dosen_id', 'jenis', 'tahun', 'judul', 'mitra', 'jabatan'],
                'scope'     => ['mahasiswa_id', 'dosen_id', 'jenis', 'tahun'],
                'int'       => ['mahasiswa_id', 'dosen_id', 'tahun'],
                'label'     => static function (array $row): string {
                    $judul = trim((string) ($row['judul'] ?? ''));
                    if ($judul !== '') {
                        return $judul;
                    }

                    return trim(trim((string) ($row['mitra'] ?? '')) . ' — ' . trim((string) ($row['jabatan'] ?? '')), ' —');
                },
                'text'      => 'Karya',
                'reference' => false,
            ],

            // Kerja Sama: pemilik dan jenis kerja sama yang sama; dibandingkan lewat mitra + judul kegiatan.
            'kerja_sama' => [
                'model'     => \App\Models\KerjaSama::class,
                'columns'   => ['id', 'user_id', 'jenis', 'mitra', 'judul_kegiatan'],
                'scope'     => ['user_id', 'jenis'],
                'int'       => ['user_id'],
                'label'     => ['mitra', 'judul_kegiatan'],
                'text'      => 'Kerja sama',
                'reference' => false,
            ],
        ];
    }

    public static function for(string $profile): self
    {
        $profiles = self::profiles();
        if (!isset($profiles[$profile])) {
            throw new \InvalidArgumentException("Profil deteksi duplikat tidak dikenal: {$profile}");
        }

        return new self($profiles[$profile]);
    }

    // =====================================================================
    // PEMERIKSAAN
    // =====================================================================

    /**
     * Periksa satu data (simpan / ubah). Kandidat diambil dari database.
     *
     * @param  array<string,mixed>  $data      data yang akan disimpan (harus memuat kolom scope + label)
     * @param  int|null             $ignoreId  id data yang sedang diubah (dikecualikan dari pembanding)
     * @return array{exact: array<int,array{name:string,id?:int,row?:int}>, similar: string[]}
     */
    public function check(array $data, ?int $ignoreId = null): array
    {
        return $this->checkBatch([['data' => $data, 'ignore_id' => $ignoreId]]);
    }

    /**
     * Periksa banyak data sekaligus (unggah massal). Setiap baris juga dibandingkan dengan baris
     * sebelumnya dalam berkas yang sama. Kandidat database diambil dengan segelintir query, bukan per baris.
     *
     * @param  array<int,array{data:array,row?:int|null,ignore_id?:int|null}>  $entries
     * @return array{exact: array<int,array{name:string,id?:int,row?:int}>, similar: string[]}
     */
    public function checkBatch(array $entries): array
    {
        return $this->scan($entries, $this->fetchCandidates($entries));
    }

    /**
     * Inti pembandingan — murni di memori (tanpa database) sehingga mudah diuji.
     *
     * @param  array<int,array{data:array,row?:int|null,ignore_id?:int|null}>  $entries
     * @param  iterable<array|object>  $existing  data yang sudah ada
     * @return array{exact: array<int,array{name:string,id?:int,row?:int}>, similar: string[]}
     */
    public function scan(array $entries, iterable $existing = []): array
    {
        $buckets = [];
        foreach ($existing as $candidate) {
            $candidate = self::toArray($candidate);
            $buckets[$this->bucket($candidate)][] = $candidate;
        }

        $exact = [];
        $similar = [];

        foreach ($entries as $entry) {
            $data = self::toArray($entry['data']);
            $row = $entry['row'] ?? null;
            $ignoreId = $entry['ignore_id'] ?? null;

            $name = $this->label($data);
            if ($name === '') {
                continue; // tidak ada yang bisa dibandingkan
            }

            $key = $this->bucket($data);
            $normalized = self::normalize($name);

            foreach ($buckets[$key] ?? [] as $candidate) {
                if ($ignoreId !== null && (int) ($candidate['id'] ?? 0) === $ignoreId) {
                    continue;
                }

                $candidateName = $this->label($candidate);
                if ($candidateName === '') {
                    continue;
                }

                if ($normalized === self::normalize($candidateName)) {
                    $exact[] = array_filter(
                        ['name' => $candidateName, 'id' => $candidate['id'] ?? null, 'row' => $row],
                        static fn ($value) => $value !== null
                    );
                } elseif (self::isSimilar($name, $candidateName)) {
                    $reference = ($this->profile['reference'] ?? false) && !empty($candidate['id'])
                        ? " (ID #{$candidate['id']})"
                        : '';
                    $similar[] = ($row !== null ? "Baris {$row}: " : '')
                        . "{$this->profile['text']} “{$name}” mirip dengan “{$candidateName}”{$reference}.";
                }
            }

            // Baris ini ikut jadi pembanding untuk baris berikutnya dalam unggahan yang sama.
            $buckets[$key][] = $data + ['id' => null];
        }

        return ['exact' => $exact, 'similar' => array_values(array_unique($similar))];
    }

    // =====================================================================
    // RESPONS
    // =====================================================================

    /** Kembalikan respons 409 bila ada duplikat persis dan pengguna belum mengonfirmasi; selain itu null. */
    public static function guard(Request $request, array $result): ?JsonResponse
    {
        if ($result['exact'] && !$request->boolean('confirm_duplicates')) {
            return self::confirmationResponse($result['exact']);
        }

        return null;
    }

    /** Respons 409 yang dipahami `duplicate-confirm.js`. */
    public static function confirmationResponse(array $exact): JsonResponse
    {
        return response()->json([
            'success' => false,
            'duplicate_confirmation_required' => true,
            'message' => 'Data yang persis sama ditemukan.',
            'duplicates' => array_slice($exact, 0, self::MAX_EXACT_SHOWN),
            'more_duplicates' => max(0, count($exact) - self::MAX_EXACT_SHOWN),
        ], 409);
    }

    /**
     * Bagian respons sukses untuk peringatan "mirip".
     *
     * @return array{warnings: string[], warning_count: int}
     */
    public static function warnings(array $result): array
    {
        return [
            'warnings' => array_slice($result['similar'], 0, self::MAX_WARNINGS_SHOWN),
            'warning_count' => count($result['similar']),
        ];
    }

    // =====================================================================
    // TEKS
    // =====================================================================

    /** Huruf kecil, tanda baca dan spasi ganda dibuang. */
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    /** Mirip tetapi tidak persis sama. */
    public static function isSimilar(string $first, string $second): bool
    {
        $first = self::normalize($first);
        $second = self::normalize($second);
        if ($first === '' || $second === '' || $first === $second) {
            return false;
        }

        similar_text($first, $second, $similarity);

        return $similarity >= self::SIMILARITY_THRESHOLD;
    }

    // =====================================================================
    // INTERNAL
    // =====================================================================

    /** Teks yang dibandingkan untuk satu baris data. */
    private function label(array $row): string
    {
        $label = $this->profile['label'];
        if ($label instanceof \Closure) {
            return trim((string) $label($row));
        }

        $parts = array_map(static fn ($field) => trim((string) ($row[$field] ?? '')), (array) $label);

        return implode(' — ', array_filter($parts, static fn ($part) => $part !== ''));
    }

    /** Kunci konteks: dua data hanya dibandingkan bila kuncinya sama. */
    private function bucket(array $row): string
    {
        $parts = [];
        foreach ($this->profile['scope'] as $field) {
            $value = $row[$field] ?? null;
            $parts[] = in_array($field, $this->profile['int'], true) ? (string) (int) $value : (string) $value;
        }

        return implode("\x1F", $parts);
    }

    /** Ambil kandidat dari database untuk semua konteks yang muncul di $entries. */
    private function fetchCandidates(array $entries): array
    {
        if ($entries === []) {
            return [];
        }

        $scope = $this->profile['scope'];
        $values = [];
        foreach ($scope as $field) {
            $unique = [];
            foreach ($entries as $entry) {
                $value = $entry['data'][$field] ?? null;
                $unique[$value === null ? "\0null" : 'v:' . $value] = $value; // dedup ketat: null != 0 != ''
            }
            $values[$field] = array_values($unique);
        }

        $model = $this->profile['model'];
        $first = $scope[0];
        $candidates = [];

        foreach (array_chunk($values[$first], self::FETCH_CHUNK) as $chunk) {
            $query = $model::query();
            foreach ($scope as $field) {
                $this->whereInOrNull($query, $field, $field === $first ? $chunk : $values[$field]);
            }
            foreach ($query->get($this->profile['columns']) as $candidate) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    private function whereInOrNull($query, string $field, array $values): void
    {
        $nonNull = array_values(array_filter($values, static fn ($value) => $value !== null));
        $hasNull = count($nonNull) !== count($values);

        $query->where(function ($where) use ($field, $nonNull, $hasNull) {
            if ($nonNull !== []) {
                $where->whereIn($field, $nonNull);
            }
            if ($hasNull) {
                $nonNull !== [] ? $where->orWhereNull($field) : $where->whereNull($field);
            }
        });
    }

    private static function toArray(array|object $row): array
    {
        if (is_array($row)) {
            return $row;
        }

        return method_exists($row, 'getAttributes') ? $row->getAttributes() : get_object_vars($row);
    }
}
