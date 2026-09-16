<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\LppmDosen;
use Illuminate\Http\Request;

class LppmDosenController extends Controller
{
    public function index(Request $request)
    {
        $items = LppmDosen::with('dosen.user')->latest()->paginate(10);
        $dosenList = Dosen::with('user')->orderBy('nidn')->get();

        $stats = [
            'total'   => LppmDosen::count(),
            'jurnal'  => LppmDosen::where('jenis', 'q_internasional')->count(),
            'sinta'   => LppmDosen::where('jenis', 'sinta_nasional')->count(),
            'hki_buku'=> LppmDosen::whereIn('jenis', ['hki', 'book'])->count(),
        ];

        return view('lppm_dosen', compact('items', 'dosenList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = LppmDosen::create($data)->load('dosen.user');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, LppmDosen $lppmDosen)
    {
        $data = $this->validated($request);
        $lppmDosen->update($data);
        $lppmDosen->load('dosen.user');

        return response()->json(['success' => true, 'data' => $this->format($lppmDosen)]);
    }

    public function destroy(LppmDosen $lppmDosen)
    {
        $id = $lppmDosen->id;
        $lppmDosen->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dosen_id'       => ['required', 'exists:dosen,id'],
            'jenis'          => ['required', 'in:q_internasional,sinta_nasional,hki,book'],
            'judul'          => ['required', 'string', 'max:255'],
            'penulis'        => ['required', 'string', 'max:255'],
            'nama_jurnal'    => ['nullable', 'required_if:jenis,q_internasional', 'required_if:jenis,sinta_nasional', 'string', 'max:255'],
            'peringkat'      => ['nullable', 'required_if:jenis,q_internasional', 'required_if:jenis,sinta_nasional', 'string', 'max:50'],
            'jenis_hki'      => ['nullable', 'required_if:jenis,hki', 'in:hak_cipta,paten,merek'],
            'kategori_buku'  => ['nullable', 'required_if:jenis,book', 'in:ajar,referensi,chapter'],
            'link_doi'       => ['nullable', 'url', 'max:2048'],
            'bukti_kegiatan' => ['required', 'url', 'max:2048'],
            'tahun'          => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);
    }

    private function format(LppmDosen $item): array
    {
        return [
            'id'             => $item->id,
            'dosen_id'       => $item->dosen_id,
            'nidn'           => $item->dosen->nidn ?? '-',
            'nama'           => optional($item->dosen->user)->name ?? 'Tanpa Nama',
            'jenis'          => $item->jenis,
            'judul'          => $item->judul,
            'penulis'        => $item->penulis,
            'nama_jurnal'    => $item->nama_jurnal,
            'peringkat'      => $item->peringkat,
            'jenis_hki'      => $item->jenis_hki,
            'kategori_buku'  => $item->kategori_buku,
            'link_doi'       => $item->link_doi,
            'bukti_kegiatan' => $item->bukti_kegiatan,
            'tahun'          => $item->tahun,
        ];
    }
}
