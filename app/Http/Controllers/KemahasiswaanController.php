<?php

namespace App\Http\Controllers;

use App\Models\Kemahasiswaan;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KemahasiswaanController extends Controller
{
    /**
     * Halaman utama Kemahasiswaan (server-rendered untuk load pertama & SEO).
     * Aksi tambah/edit/hapus selanjutnya berjalan lewat fetch() tanpa reload.
     */
    public function index(Request $request)
    {
        $kegiatan = Kemahasiswaan::with('mahasiswa.user')->latest()->paginate(10);

        $mahasiswaList = Mahasiswa::with('user')->orderBy('nim')->get();

        $stats = [
            'total'         => Kemahasiswaan::count(),
            'nasional'      => Kemahasiswaan::where('tingkat', 'nasional')->count(),
            'internasional' => Kemahasiswaan::where('tingkat', 'internasional')->count(),
            'inbis'         => Kemahasiswaan::where('jenis', 'inbis')->count(),
        ];

        return view('kemahasiswaan', compact('kegiatan', 'mahasiswaList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $item = Kemahasiswaan::create($data)->load('mahasiswa.user');

        return response()->json([
            'success' => true,
            'data'    => $this->format($item),
        ]);
    }

    public function update(Request $request, Kemahasiswaan $kemahasiswaan)
    {
        $data = $this->validated($request);

        $kemahasiswaan->update($data);
        $kemahasiswaan->load('mahasiswa.user');

        return response()->json([
            'success' => true,
            'data'    => $this->format($kemahasiswaan),
        ]);
    }

    public function destroy(Kemahasiswaan $kemahasiswaan)
    {
        $id = $kemahasiswaan->id;
        $kemahasiswaan->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'mahasiswa_id'   => ['required', Rule::exists('mahasiswa', 'id')],
            'jenis'          => ['required', 'in:inbis,kemahasiswaan'],
            'tab'            => ['required', 'in:akademik,non_akademik'],
            'tingkat'        => ['required', 'in:lokal,nasional,internasional'],
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
            'nama_kegiatan'  => ['required', 'string', 'max:255'],
            'bukti_kegiatan' => ['required', 'url', 'max:2048'],
        ]);
    }

    /**
     * Bentuk payload JSON yang dikonsumsi JS untuk membangun/mengganti baris tabel.
     */
    private function format(Kemahasiswaan $item): array
    {
        return [
            'id'             => $item->id,
            'mahasiswa_id'   => $item->mahasiswa_id,
            'nim'            => $item->mahasiswa->nim ?? '-',
            'nama'           => optional($item->mahasiswa->user)->name ?? 'Tanpa Nama',
            'jenis'          => $item->jenis,
            'tab'            => $item->tab,
            'tingkat'        => $item->tingkat,
            'tahun'          => $item->tahun,
            'nama_kegiatan'  => $item->nama_kegiatan,
            'bukti_kegiatan' => $item->bukti_kegiatan,
        ];
    }
}
