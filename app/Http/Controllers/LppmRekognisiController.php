<?php

namespace App\Http\Controllers;

use App\Models\Rekognisi;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class LppmRekognisiController extends Controller
{
    /**
     * Akses "biasa" (default mahasiswa/dosen) hanya bisa melihat & mengisi
     * rekognisinya sendiri. Akses "penuh"/"readonly" melihat & mengelola
     * data semua orang.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ownScope = $user->menuLevel('rekognisi') === 'biasa';

        $query = Rekognisi::with('user')->latest();
        if ($ownScope) {
            $query->where('user_id', $user->id);
        }
        $items = $query->paginate(10);

        // Semua user yang bisa punya rekognisi: dosen & mahasiswa
        $userList = $ownScope
            ? collect([$user])
            : User::whereIn('role', ['dosen', 'mahasiswa'])->orderBy('name')->get();

        $statsQuery = fn () => $ownScope
            ? Rekognisi::where('user_id', $user->id)
            : Rekognisi::query();

        $stats = [
            'total'         => (clone $statsQuery())->count(),
            'nasional'      => (clone $statsQuery())->where('jenis', 'nasional')->count(),
            'internasional' => (clone $statsQuery())->where('jenis', 'internasional')->count(),
            'alumni'        => (clone $statsQuery())->where('jenis', 'alumni')->count(),
        ];

        return view('lppm_rekognisi', compact('items', 'userList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $item = Rekognisi::create($data)->load('user');

        $this->notifyOwnerIfByOthers($request, $item, 'ditambahkan');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);

        $data = $this->validated($request);
        $this->enforceOwnUser($request, $data);

        $rekognisi->update($data);
        $rekognisi->load('user');

        $this->notifyOwnerIfByOthers($request, $rekognisi, 'diperbarui');

        return response()->json(['success' => true, 'data' => $this->format($rekognisi)]);
    }

    public function destroy(Request $request, Rekognisi $rekognisi)
    {
        $this->authorizeOwnership($request, $rekognisi);

        $rekognisi->loadMissing('user');
        $this->notifyOwnerIfByOthers($request, $rekognisi, 'dihapus');

        $id = $rekognisi->id;
        $rekognisi->delete();

        return response()->json(['success' => true, 'id' => $id]);
    }

    /**
     * Kalau yang menambah/mengubah/menghapus BUKAN pemilik data rekognisi
     * itu sendiri (berarti admin/staf yang mengelola data dosen/mahasiswa
     * lain), beri tahu pemiliknya lewat notifikasi.
     */
    private function notifyOwnerIfByOthers(Request $request, Rekognisi $item, string $aksi): void
    {
        $actor = $request->user();
        $ownerId = $item->user_id;

        if (!$actor || !$ownerId || $actor->id === $ownerId) {
            return;
        }

        UserNotification::send($ownerId, 'data_updated', [
            'title'       => "Data rekognisi Anda {$aksi}",
            'description' => "\"{$item->mitra}\" ({$item->jenis}) {$aksi} oleh {$actor->name} ({$actor->role}).",
            'data'        => ['actor_id' => $actor->id, 'actor_name' => $actor->name, 'rekognisi_id' => $item->id],
        ]);
    }

    private function enforceOwnUser(Request $request, array &$data): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        // Timpa user_id (dan tipe_user turunannya) jadi milik mereka sendiri,
        // berapa pun yang dikirim dari klien.
        $data['user_id'] = $actor->id;
        $data['tipe_user'] = $actor->role === 'dosen' ? 'dosen' : 'mahasiswa';
    }

    private function authorizeOwnership(Request $request, Rekognisi $rekognisi): void
    {
        $actor = $request->user();
        if ($actor->menuLevel('rekognisi') !== 'biasa') {
            return;
        }

        abort_if(
            $rekognisi->user_id !== $actor->id,
            403,
            'Kamu hanya bisa mengelola data rekognisi milikmu sendiri.'
        );
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'user_id'         => ['required', 'exists:users,id'],
            'jenis'           => ['required', 'in:nasional,internasional,alumni'],
            'mitra'           => ['required', 'string', 'max:255'],
            'jabatan'         => ['nullable', 'required_if:jenis,alumni', 'string', 'max:255'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'bukti_kegiatan'  => ['required', 'url', 'max:2048'],
            'bukti_tambahan'  => ['nullable', 'url', 'max:2048'],
        ]);

        // tipe_user diturunkan otomatis dari role user yang dipilih, bukan dari input klien
        $user = User::findOrFail($data['user_id']);
        $data['tipe_user'] = $user->role === 'dosen' ? 'dosen' : 'mahasiswa';

        if ($data['jenis'] !== 'alumni') {
            $data['jabatan'] = null;
        }

        return $data;
    }

    private function format(Rekognisi $item): array
    {
        return [
            'id'              => $item->id,
            'user_id'         => $item->user_id,
            'tipe_user'       => $item->tipe_user,
            'nim_nidn'        => optional($item->user)->nim_nidn ?? '-',
            'nama'            => optional($item->user)->name ?? 'Tanpa Nama',
            'jenis'           => $item->jenis,
            'mitra'           => $item->mitra,
            'jabatan'         => $item->jabatan,
            'tanggal_mulai'   => optional($item->tanggal_mulai)->format('Y-m-d'),
            'tanggal_selesai' => optional($item->tanggal_selesai)->format('Y-m-d'),
            'bukti_kegiatan'  => $item->bukti_kegiatan,
            'bukti_tambahan'  => $item->bukti_tambahan,
        ];
    }
}
