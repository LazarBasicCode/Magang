<?php

namespace App\Http\Controllers;

use App\Models\Kemahasiswaan;
use App\Models\KerjaSama;
use App\Models\Rekognisi;
use App\Models\UserNotification;
use Illuminate\Http\Request;

/**
 * Halaman Profil (GET /profil): identitas, rekap, riwayat gabungan, upload terbaru, notifikasi.
 * Satu halaman untuk mahasiswa & dosen; sumber datanya mengikuti role.
 * Hanya MEMBACA data milik user yang sedang login (tidak ada aksi tulis di sini).
 */
class ProfilController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->loadMissing(['mahasiswa', 'dosen']);
        $isDosen = $user->role === 'dosen';
        $profile = $isDosen ? $user->dosen : $user->mahasiswa;

        // Kategori per role: [key, label, ikon, menu (hak akses), url menu asal, query milik user, pemetaan baris]
        $sources = $isDosen
            ? [
                ['kerja_sama', 'Kerja Sama', 'handshake', 'kerja_sama', '/kerja-sama',
                    KerjaSama::where('user_id', $user->id),
                    fn ($i) => [$i->judul_kegiatan, $i->mitra, optional($i->tanggal_mulai)->year]],
                ['rekognisi', 'Rekognisi', 'workspace_premium', 'rekognisi', '/lppm/rekognisi',
                    Rekognisi::where('user_id', $user->id),
                    fn ($i) => [$i->judul ?: ($i->jabatan_efektif ?: $i->mitra), $this->label($i->jenis) . ($i->skala ? ' · ' . $this->label($i->skala) : ''), optional($i->tanggal_mulai)->year]],
            ]
            : [
                ['kemahasiswaan', 'Kemahasiswaan', 'school', 'kemahasiswaan', '/kemahasiswaan',
                    $profile ? Kemahasiswaan::where('mahasiswa_id', $profile->id) : null,
                    fn ($i) => [$i->nama_kegiatan, $this->label($i->jenis) . ' · ' . $this->label($i->tingkat), $i->tahun]],
                ['kerja_sama', 'Kerja Sama', 'handshake', 'kerja_sama', '/kerja-sama',
                    KerjaSama::where('user_id', $user->id),
                    fn ($i) => [$i->judul_kegiatan, $i->mitra, optional($i->tanggal_mulai)->year]],
                ['rekognisi', 'Rekognisi', 'workspace_premium', 'rekognisi', '/lppm/rekognisi',
                    Rekognisi::where('user_id', $user->id),
                    fn ($i) => [$i->judul ?: ($i->jabatan_efektif ?: $i->mitra), $this->label($i->jenis) . ($i->skala ? ' · ' . $this->label($i->skala) : ''), optional($i->tanggal_mulai)->year]],
            ];

        $items = collect();
        $categories = [];
        foreach ($sources as [$key, $label, $icon, $menu, $url, $query, $map]) {
            $rows = $query ? $query->latest()->get() : collect();
            $categories[] = ['key' => $key, 'label' => $label, 'icon' => $icon, 'count' => $rows->count()];

            $canOpen = $user->canAccessMenu($menu);
            foreach ($rows as $row) {
                [$judul, $sub, $tahun] = $map($row);
                $items->push([
                    'id'      => $row->id,
                    'kat'     => $key,
                    'label'   => $label,
                    'icon'    => $icon,
                    'judul'   => (string) $judul,
                    'sub'     => (string) $sub,
                    'tahun'   => $tahun ? (int) $tahun : null,
                    'bukti'   => $row->bukti_kegiatan ?? null,
                    'url'     => $canOpen ? url($url) : null,   // tombol "Edit" membuka menu asal
                    'dibuat'  => optional($row->created_at)->toIso8601String(),
                    'relatif' => $row->created_at?->locale('id')->diffForHumans(),
                ]);
            }
        }

        // Riwayat: tahun terbaru dulu, lalu yang terakhir diinput. Upload terbaru: murni urutan input.
        $riwayat = $items->sortByDesc(fn ($i) => sprintf('%04d|%s', $i['tahun'] ?? 0, $i['dibuat']))->values();
        $terbaru = $items->sortByDesc('dibuat')->take(4)->values();

        $perTahun = $items->whereNotNull('tahun')->groupBy('tahun')->map->count()->sortKeysDesc();

        // Notifikasi: nama kolom bergantung model UserNotification, jadi dibaca defensif.
        $notifs = rescue(fn () => UserNotification::where('user_id', $user->id)->latest()->limit(10)->get()->map(function ($n) {
            $d = is_array($n->data ?? null) ? $n->data : [];
            return [
                'id'      => $n->id,
                'judul'   => $n->title ?? $d['title'] ?? $this->label($n->type ?? 'notifikasi'),
                'pesan'   => $n->message ?? $n->body ?? $d['message'] ?? $d['pesan'] ?? '',
                'baca'    => !empty($n->read_at),
                'relatif' => $n->created_at?->locale('id')->diffForHumans(),
                'waktu'   => $n->created_at?->locale('id')->translatedFormat('j F Y, H:i'),
            ];
        }), collect(), false);

        return view('profil', [
            'user'       => $user,
            'isDosen'    => $isDosen,
            'profile'    => $profile,
            'categories' => $categories,
            'total'      => $items->count(),
            'perTahun'   => $perTahun,
            'riwayat'    => $riwayat,
            'terbaru'    => $terbaru,
            'notifs'     => $notifs,
            'years'      => $perTahun->keys()->values(),
        ]);
    }

    private function label(?string $v): string
    {
        return ucwords(str_replace('_', ' ', (string) $v));
    }
}