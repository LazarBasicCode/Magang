<?php

namespace App\Http\Controllers;

use App\Support\ReportData;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Menu Laporan khusus superadmin: versi lengkap dari ringkasan dashboard,
     * bisa difilter per tahun dan dicetak.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'superadmin', 403, 'Menu Laporan hanya untuk Super Admin.');

        $tahun = $request->query('tahun');
        $tahun = ctype_digit((string) $tahun) ? (int) $tahun : null;

        return view('laporan', ['report' => ReportData::build($tahun)]);
    }
}
