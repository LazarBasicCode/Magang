<?php

namespace App\Http\Controllers;

use App\Models\KerjaSama;
use App\Models\User;
use Illuminate\Http\Request;

class KerjaSamaController extends Controller
{
    /**
     * Halaman utama Kerja Sama (server-rendered untuk load pertama & SEO).
     * Aksi tambah/edit/hapus selanjutnya berjalan lewat fetch() tanpa reload.
     *
     * Akses "biasa" (default mahasiswa/dosen) hanya bisa melihat & mengisi
     * datanya sendiri. Akses "penuh"/"readonly" melihat & mengelola semua.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('kerja_sama') === 'biasa';

        $query = KerjaSama::with('user')->latest();
        if ($ownScope) {
            $query->where('user_id', $user->id);
        }
        $items = $query->paginate(10);

        $userList = $ownScope
            ? collect([$user])
            : User::whereIn('role', ['mahasiswa', 'dosen'])->orderBy('name')->get();

        $internasionalJenis = [
            'conference_internasional',
            'guest_lecture',
            'pengabdian_internasional',
            'research_internasional',
        ];

        $statsQuery = fn () => $ownScope
            ? KerjaSama::where('user_id', $user->id)
            : KerjaSama::query();

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'mahasiswa'     => (clone $statsQuery())->where('tipe_user', 'mahasiswa')->count(),
            'dosen'         => (clone $statsQuery())->where('tipe_user', 'dosen')->count(),
            'internasional' => (clone $statsQuery())->whereIn('jenis', $internasionalJenis)->count(),
        ];

        return view('kerja-sama', compact('items', 'userList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $item = KerjaSama::create($data)->load('user');

        return response()->json([
            'success' => true,
            'data'    => $this->format($item),
        ]);
    }

    public function update(Request $request, KerjaSama $kerjaSama)
    {
        $this->authorizeOwnership($request, $kerjaSama);

        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $kerjaSama->update($data);
        $kerjaSama->load('user');

        return response()->json([
            'success' => true,
            'data'    => $this->format($kerjaSama),
        ]);
    }

    public function destroy(Request $request, KerjaSama $kerjaSama)
    {
        $this->authorizeOwnership($request, $kerjaSama);

        $id = $kerjaSama->id;
        $kerjaSama->delete();

        return response()->json([
            'success' => true,
            'id'      => $id,
        ]);
    }

    private function enforceOwnUser(Request $request, array &$data): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('kerja_sama') !== 'biasa') {
            return;
        }

        $data['user_id'] = $actor->id;
        $data['tipe_user'] = $actor->role === 'dosen' ? 'dosen' : 'mahasiswa';
    }

    private function authorizeOwnership(Request $request, KerjaSama $kerjaSama): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('kerja_sama') !== 'biasa') {
            return;
        }

        abort_if(
            $kerjaSama->user_id !== $actor->id,
            403,
            'Kamu hanya bisa mengelola data kerja sama milikmu sendiri.'
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id'          => ['required', 'exists:users,id'],
            'tipe_user'        => ['required', 'in:mahasiswa,dosen'],
            'jenis'            => ['required', 'in:conference_internasional,pkl,sharing_session,keynote_session,guest_lecture,pengabdian_internasional,research_internasional'],
            'arah'             => ['nullable', 'required_if:jenis,guest_lecture', 'in:inbound,outbound'],
            'mitra'            => ['required', 'string', 'max:255'],
            'judul_kegiatan'   => ['required', 'string', 'max:255'],
            'tanggal_mulai'    => ['required', 'date'],
            'tanggal_selesai'  => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'bukti_kegiatan'   => ['required', 'url', 'max:2048'],
        ]);
    }

    /**
     * Bentuk payload JSON yang dikonsumsi JS untuk membangun/mengganti baris tabel.
     */
    private function format(KerjaSama $item): array
    {
        return [
            'id'               => $item->id,
            'user_id'          => $item->user_id,
            'nim_nidn'         => $item->user->nim_nidn ?? '-',
            'nama'             => $item->user->name ?? 'Tanpa Nama',
            'tipe_user'        => $item->tipe_user,
            'jenis'            => $item->jenis,
            'arah'             => $item->arah,
            'mitra'            => $item->mitra,
            'judul_kegiatan'   => $item->judul_kegiatan,
            'tanggal_mulai'    => optional($item->tanggal_mulai)->format('Y-m-d'),
            'tanggal_selesai'  => optional($item->tanggal_selesai)->format('Y-m-d'),
            'bukti_kegiatan'   => $item->bukti_kegiatan,
        ];
    }
}
