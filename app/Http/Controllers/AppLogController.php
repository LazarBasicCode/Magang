<?php

namespace App\Http\Controllers;

use App\Support\LaravelLogReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Penampil log aplikasi Laravel (storage/logs/*.log) di halaman Log Aktivitas.
 * GET /login-audit/app-logs?file=&level=&q=&page=   -> JSON
 *
 * Hanya SUPERADMIN: log berisi pesan error, jalur file server, dan potongan query/data yang
 * tidak boleh terlihat oleh admin biasa walaupun mereka diberi akses menu Log Aktivitas.
 */
class AppLogController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'superadmin', 403, 'Log aplikasi hanya untuk superadmin.');

        $dir = storage_path('logs');
        $files = LaravelLogReader::files($dir);

        // File yang dipilih HARUS salah satu dari daftar (mencegah path traversal)
        $names = array_column($files, 'name');
        $file = (string) $request->query('file', '');
        if (! in_array($file, $names, true)) {
            $file = $names[0] ?? '';
        }

        $level = (string) $request->query('level', 'all');
        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $read = $file !== '' ? LaravelLogReader::read($dir . DIRECTORY_SEPARATOR . $file) : ['entries' => [], 'truncated' => false];
        $entries = $read['entries'];

        // Pencarian dulu, supaya angka di tab level ikut menyesuaikan kata kunci
        if ($term !== '') {
            $entries = array_values(array_filter($entries, fn ($e) => stripos($e['message'], $term) !== false
                || stripos($e['details'], $term) !== false
                || stripos($e['time'], $term) !== false));
        }

        $counts = ['all' => count($entries), 'error' => 0, 'warning' => 0, 'info' => 0, 'debug' => 0];
        foreach ($entries as $e) {
            $counts[$e['group']]++;
        }

        if (isset(LaravelLogReader::GROUPS[$level])) {
            $entries = array_values(array_filter($entries, fn ($e) => $e['group'] === $level));
        }

        $total = count($entries);
        $last = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $last);
        $slice = array_slice($entries, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        return response()->json([
            'files'      => $files,
            'file'       => $file,
            'truncated'  => $read['truncated'],
            'tail_mb'    => LaravelLogReader::TAIL_BYTES / 1048576,
            'counts'     => $counts,
            'pagination' => ['current_page' => $page, 'last_page' => $last, 'total' => $total, 'per_page' => self::PER_PAGE],
            'data'       => array_map(fn ($e) => $e + ['has_details' => $e['details'] !== ''], $slice),
        ]);
    }
}
