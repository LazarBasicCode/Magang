<?php

namespace App\Http\Controllers;

use App\Models\Rekognisi;
use App\Models\User;
use Illuminate\Http\Request;

class LppmRekognisiController extends Controller
{
    public function index(Request $request)
    {
        $items = Rekognisi::with('user')->latest()->paginate(10);

        // Semua user yang bisa punya rekognisi: dosen & mahasiswa
        $userList = User::whereIn('role', ['dosen', 'mahasiswa'])->orderBy('name')->get();

        $stats = [
            'total'         => Rekognisi::count(),
            'nasional'      => Rekognisi::where('jenis', 'nasional')->count(),
            'internasional' => Rekognisi::where('jenis', 'internasional')->count(),
            'alumni'        => Rekognisi::where('jenis', 'alumni')->count(),
        ];

        return view('lppm_rekognisi', compact('items', 'userList', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = Rekognisi::create($data)->load('user');

        return response()->json(['success' => true, 'data' => $this->format($item)]);
    }

    public function update(Request $request, Rekognisi $rekognisi)
    {
        $data = $this->validated($request);
        $rekognisi->update($data);
        $rekognisi->load('user');

        return response()->json(['success' => true, 'data' => $this->format($rekognisi)]);
    }

    public function destroy(Rekognisi $rekognisi)
    {
        $id = $rekognisi->id;
        $rekognisi->delete();

        return response()->json(['success' => true, 'id' => $id]);
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
