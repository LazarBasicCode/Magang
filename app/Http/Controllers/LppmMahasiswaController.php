<?php

namespace App\Http\Controllers;

use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class LppmMahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $items = LppmMahasiswa::with('mahasiswa.user')->latest()->paginate(10);
        $mahasiswaList = Mahasiswa::with('user')->orderBy('nim')->get();

        $stats = [
            'total'      => LppmMahasiswa::count(),
            'sinta'      => LppmMahasiswa::where('jenis', 'sinta_nasional')->count(),
            'conference' => LppmMahasiswa::where('jenis', 'conference_internasional')->count(),
            'jurnal'     => LppmMahasiswa::where('jenis', 'jurnal_internasional')->count(),
        ];

        return view('lppm_mahasiswa', compact('items', 'mahasiswaList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = LppmMahasiswa::create($data)->load('mahasiswa.user');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, LppmMahasiswa $lppmMahasiswa)
    {
        $data = $this->validated($request);
        $lppmMahasiswa->update($data);
        $lppmMahasiswa->load('mahasiswa.user');

        return response()->json(['success' => true, 'data' => $this->format($lppmMahasiswa)]);
    }

    public function destroy(LppmMahasiswa $lppmMahasiswa)
    {
        $id = $lppmMahasiswa->id;
        $lppmMahasiswa->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'mahasiswa_id'   => ['required', 'exists:mahasiswa,id'],
            'jenis'          => ['required', 'in:sinta_nasional,conference_internasional,jurnal_internasional'],
            'judul'          => ['required', 'string', 'max:255'],
            'penulis'        => ['required', 'string', 'max:255'],
            'nama_jurnal'    => ['nullable', 'required_if:jenis,sinta_nasional', 'required_if:jenis,jurnal_internasional', 'string', 'max:255'],
            'peringkat'      => ['nullable', 'required_if:jenis,sinta_nasional', 'required_if:jenis,jurnal_internasional', 'string', 'max:50'],
            'link_doi'       => ['nullable', 'url', 'max:2048'],
            'bukti_kegiatan' => ['required', 'url', 'max:2048'],
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);
    }

    private function format(LppmMahasiswa $item): array
    {
        return [
            'id'             => $item->id,
            'mahasiswa_id'   => $item->mahasiswa_id,
            'nim'            => $item->mahasiswa->nim ?? '-',
            'nama'           => optional($item->mahasiswa->user)->name ?? 'Tanpa Nama',
            'jenis'          => $item->jenis,
            'judul'          => $item->judul,
            'penulis'        => $item->penulis,
            'nama_jurnal'    => $item->nama_jurnal,
            'peringkat'      => $item->peringkat,
            'link_doi'       => $item->link_doi,
            'bukti_kegiatan' => $item->bukti_kegiatan,
            'tahun'          => $item->tahun,
        ];
    }
}
