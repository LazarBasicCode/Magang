<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Foto profil (pengguna yang sedang login).
 *
 * Alurnya sengaja sederhana:
 *   1. Browser mengecilkan foto jadi ±256x256 WebP (±20 KB) — public/js/avatar-upload.js.
 *   2. File kecil itu dikirim ke sini; Laravel meneruskannya ke ImgBB dan menerima LINK gambar.
 *      (Kunci API ada di .env, jadi tidak bocor ke browser.)
 *   3. Hanya link itu yang disimpan di kolom users.avatar_url. Di mana pun foto tampil (header, tabel, profil),
 *      browser memuatnya langsung dari link tersebut: server & database aplikasi tidak menyimpan/mengirim gambar.
 *
 * Menampilkan foto di Blade: @include('partials.avatar', [...]) — lihat resources/views/partials/avatar.blade.php.
 */
class AvatarController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            // Browser sudah mengecilkan jadi ±20 KB; batas 1 MB hanya pengaman bila ada yang mengirim file mentah.
            'avatar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'avatar.required' => 'Pilih foto terlebih dahulu.',
            'avatar.file'     => 'Berkas foto tidak valid.',
            // Berkas melebihi batas PHP (upload_max_filesize) ditolak sebelum validasi lain berjalan.
            'avatar.uploaded' => 'Ukuran foto terlalu besar atau gagal diunggah. Gunakan foto yang lebih kecil.',
            'avatar.mimes'    => 'Format foto harus JPG, PNG, atau WebP.',
            'avatar.max'      => 'Ukuran foto terlalu besar (maksimal 1 MB setelah dikecilkan).',
        ]);

        $key = config('services.avatar.key');
        if (!$key) {
            return response()->json(['message' => 'Fitur foto profil belum dikonfigurasi (isi AVATAR_IMGBB_KEY di .env).'], 503);
        }

        try {
            $response = Http::timeout(20)->asForm()->post(config('services.avatar.url'), [
                'key'   => $key,
                'image' => base64_encode(file_get_contents($request->file('avatar')->getRealPath())),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Tidak bisa menghubungi layanan penyimpanan foto. Coba lagi sebentar lagi.'], 502);
        }

        $url = (string) $response->json('data.url');
        if (!$response->successful() || !$this->isSafeUrl($url)) {
            report(new \RuntimeException('Unggah avatar gagal: HTTP ' . $response->status()));

            return response()->json(['message' => 'Layanan penyimpanan foto menolak unggahan. Coba lagi atau gunakan foto lain.'], 502);
        }

        $request->user()->forceFill(['avatar_url' => $url])->save();

        return response()->json(['message' => 'Foto profil berhasil diperbarui.', 'avatar_url' => $url]);
    }

    public function destroy(Request $request): JsonResponse
    {
        // Hanya melepas link dari akun. (Berkas di layanan gambar tidak ikut terhapus; ImgBB gratis tanpa API hapus.)
        $request->user()->forceFill(['avatar_url' => null])->save();

        return response()->json(['message' => 'Foto profil dihapus.']);
    }

    /**
     * Link dari layanan luar hanya diterima bila berupa URL http(s) yang wajar: bukan javascript:, dan tanpa spasi,
     * tanda kutip, atau kurung siku/sudut (FILTER_VALIDATE_URL saja meloloskan tanda kutip). Tampilannya tetap di-escape
     * di Blade/JS; ini lapisan pengaman tambahan sebelum link masuk database.
     */
    private function isSafeUrl(string $url): bool
    {
        return strlen($url) <= 500
            && preg_match('~^https?://[^\s"\'<>`\\\\]+$~i', $url) === 1
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
