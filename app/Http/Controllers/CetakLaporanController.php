<?php

namespace App\Http\Controllers;

use App\Support\PrintReports;
use Illuminate\Http\Request;

class CetakLaporanController extends Controller
{
    /**
     * Halaman cetak laporan satu menu (semua data, bukan hanya halaman paginasi).
     * Daftar menu & kolomnya ada di App\Support\PrintReports.
     *
     * Akses sama dengan fitur Download: admin/superadmin dengan akses readonly ke atas pada menu terkait.
     * Opsional ?tahun=2026 untuk memfilter periode (hanya untuk menu yang punya data tahun/tanggal).
     */
    public function show(Request $request, string $menu)
    {
        $cfg = PrintReports::find($menu);
        abort_unless($cfg, 404);

        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'superadmin'], true), 403, 'Fitur ini hanya untuk admin dan superadmin.');
        abort_unless($user->canAccessMenu($cfg['menu'], 'readonly'), 403, 'Kamu tidak punya akses untuk fitur ini.');

        $model = $cfg['model'];
        $year  = $cfg['year'];
        $col   = $year['column'] ?? 'id';

        $years = collect();
        $tahun = null;

        if ($year) {
            $isDate = !empty($year['date']);
            $years  = $model::query()->pluck($col)
                ->map(fn ($v) => $isDate ? ($v ? \Carbon\Carbon::parse($v)->year : null) : (int) $v)
                ->filter()->unique()->sortDesc()->values();

            $q = $request->query('tahun');
            $tahun = ctype_digit((string) $q) && $years->contains((int) $q) ? (int) $q : null;
        }

        $items = $model::with($cfg['with'])
            ->when($tahun && $year, fn ($q) => !empty($year['date'])
                ? $q->whereYear($col, $tahun)
                : $q->where($col, $tahun))
            ->when($year, fn ($q) => $q->orderByDesc($col))
            ->orderBy('id')
            ->get();

        return view('cetak-laporan', [
            'cfg'     => $cfg,
            'slug'    => $menu,
            'items'   => $items,
            'years'   => $years,
            'tahun'   => $tahun,
            'stats'   => collect($cfg['stats'])->map(fn ($s) => [
                'label' => $s[0], 'icon' => $s[1], 'color' => $s[2], 'value' => $s[3]($items),
            ]),
        ]);
    }
}
