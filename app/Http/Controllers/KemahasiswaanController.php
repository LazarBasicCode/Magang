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
     *
     * Akses "biasa" (default mahasiswa) hanya bisa melihat & mengisi datanya
     * sendiri. Akses "penuh"/"readonly" (admin/superadmin, atau staf yang
     * sengaja diberi readonly) melihat & mengelola data semua mahasiswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('kemahasiswaan') === 'biasa';
        $mahasiswa = $user->mahasiswa;

        $query = Kemahasiswaan::with('mahasiswa.user')->latest();
        if ($ownScope) {
            $query->where('mahasiswa_id', $mahasiswa->id ?? 0);
        }
        $kegiatan = $query->paginate(10);

        $mahasiswaList = $ownScope
            ? ($mahasiswa ? collect([$mahasiswa->load('user')]) : collect())
            : Mahasiswa::with('user')->orderBy('nim')->get();

        $statsQuery = fn () => $ownScope
            ? Kemahasiswaan::where('mahasiswa_id', $mahasiswa->id ?? 0)
            : Kemahasiswaan::query();

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'nasional'      => (clone $statsQuery())->where('tingkat', 'nasional')->count(),
            'internasional' => (clone $statsQuery())->where('tingkat', 'internasional')->count(),
            'inbis'         => (clone $statsQuery())->where('jenis', 'inbis')->count(),
        ];

        return view('kemahasiswaan', compact('kegiatan', 'mahasiswaList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $item = Kemahasiswaan::create($data)->load('mahasiswa.user');

        return response()->json([
            'success' => true,
            'data'    => $this->format($item),
        ]);
    }

    public function update(Request $request, Kemahasiswaan $kemahasiswaan)
    {
        $this->authorizeOwnership($request, $kemahasiswaan);

        $data = $this->validated($request);
        $this->enforceOwnMahasiswa($request, $data);

        $kemahasiswaan->update($data);
        $kemahasiswaan->load('mahasiswa.user');

        return response()->json([
            'success' => true,
            'data'    => $this->format($kemahasiswaan),
        ]);
    }

    public function destroy(Request $request, Kemahasiswaan $kemahasiswaan)
    {
        $this->authorizeOwnership($request, $kemahasiswaan);

        $id = $kemahasiswaan->id;
        $kemahasiswaan->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    /**
     * User dengan akses "biasa" tidak boleh menyimpan/memilih mahasiswa_id
     * milik orang lain, walau nilainya dipaksakan lewat request mentah —
     * mahasiswa_id selalu ditimpa jadi milik mereka sendiri.
     */
    private function enforceOwnMahasiswa(Request $request, array &$data): void
    {
        $user = $request->user();
        if ($user->menuLevel('kemahasiswaan') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(!$mahasiswa, 422, 'Profil mahasiswa kamu belum terhubung ke akun ini.');

        $data['mahasiswa_id'] = $mahasiswa->id;
    }

    /**
     * User dengan akses "biasa" hanya boleh mengubah/menghapus datanya
     * sendiri. Selain itu (penuh/readonly) boleh mengelola siapa saja.
     */
    private function authorizeOwnership(Request $request, Kemahasiswaan $kemahasiswaan): void
    {
        $user = $request->user();
        if ($user->menuLevel('kemahasiswaan') !== 'biasa') {
            return;
        }

        $mahasiswa = $user->mahasiswa;
        abort_if(
            !$mahasiswa || $kemahasiswaan->mahasiswa_id !== $mahasiswa->id,
            403,
            'Kamu hanya bisa mengelola data kegiatan milikmu sendiri.'
        );
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
