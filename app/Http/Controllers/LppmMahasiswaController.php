<?php

namespace App\Http\Controllers;

use App\Models\LppmMahasiswa;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class LppmMahasiswaController extends Controller
{
    /**
     * Akses "biasa" (default mahasiswa) hanya bisa melihat & mengisi
     * publikasinya sendiri. Akses "penuh"/"readonly" melihat & mengelola
     * data semua mahasiswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('lppm_mahasiswa') === 'biasa';
        $mahasiswa = $user->mahasiswa;

        $query = LppmMahasiswa::with('mahasiswa.user')->latest();
        if ($ownScope) {
            $query->where('mahasiswa_id', $mahasiswa->id ?? 0);
        }
        $items = $query->paginate(10);

        $mahasiswaList = $ownScope
            ? ($mahasiswa ? collect([$mahasiswa->load('user')]) : collect())
            : Mahasiswa::with('user')->orderBy('nim')->get();

        $statsQuery = fn () => $ownScope
            ? LppmMahasiswa::where('mahasiswa_id', $mahasiswa->id ?? 0)
            : LppmMahasiswa::query();

        $stats = [
            'total'      => (clone $statsQuery())->count(),
            'sinta'      => (clone $statsQuery())->where('jenis', 'sinta_nasional')->count(),
            'conference' => (clone $statsQuery())->where('jenis', 'conference_internasional')->count(),
            'jurnal'     => (clone $statsQuery())->where('jenis', 'jurnal_internasional')->count(),
        ];

        return view('lppm_mahasiswa', compact('items', 'mahasiswaList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $item = LppmMahasiswa::create($data)->load('mahasiswa.user');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, LppmMahasiswa $lppmMahasiswa)
    {
        $this->authorizeOwnership($request, $lppmMahasiswa);

        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $lppmMahasiswa->update($data);
        $lppmMahasiswa->load('mahasiswa.user');

        return response()->json(['success' => true, 'data' => $this->format($lppmMahasiswa)]);
    }

    public function destroy(Request $request, LppmMahasiswa $lppmMahasiswa)
    {
        $this->authorizeOwnership($request, $lppmMahasiswa);

        $id = $lppmMahasiswa->id;
        $lppmMahasiswa->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    private function enforceOwnMahasiswa(Request $request, array &$data): void
    {
        $user = $request->user();
        if ($user->menuLevel('lppm_mahasiswa') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(!$mahasiswa, 422, 'Profil mahasiswa kamu belum terhubung ke akun ini.');

        $data['mahasiswa_id'] = $mahasiswa->id;
    }

    private function authorizeOwnership(Request $request, LppmMahasiswa $lppmMahasiswa): void
    {
        $user = $request->user();
        if ($user->menuLevel('lppm_mahasiswa') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(
            !$mahasiswa || $lppmMahasiswa->mahasiswa_id !== $mahasiswa->id,
            403,
            'Kamu hanya bisa mengelola data publikasi milikmu sendiri.'
        );
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
