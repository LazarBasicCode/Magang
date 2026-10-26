<?php

namespace App\Http\Middleware;

use App\Support\ActivityDescriber;
use App\Support\ActivityLog;
use App\Support\ChangeTracker;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat aktivitas pengguna ke log "activity" SETELAH request selesai,
 * jadi cukup dipasang sekali di grup route yang butuh login — semua aksi
 * (tambah/ubah/hapus, unggah/unduh Excel, cetak, hak akses, backup, ganti
 * password, dst.) tercatat otomatis, termasuk route yang ditambahkan nanti.
 *
 * Aturan:
 *  - Sukses (status < 400) pada aksi tulis (POST/PUT/PATCH/DELETE) -> info.
 *  - Unduh Excel/template/backup & buka cetak (GET) -> info.
 *  - Membuka halaman biasa (GET lain) TIDAK dicatat.
 *  - 403 (tidak punya hak akses), pada method apa pun -> warning.
 *  - Unggah Excel yang ditolak validasinya (422) -> warning.
 *  - Isi request (password, formulir, dsb.) tidak pernah ikut dicatat. Yang dicatat hanya perubahan data
 *    (kolom, nilai lama -> baru) dari model Eloquent lewat ChangeTracker; kolom sensitif disamarkan.
 *
 * Pencatatan gagal tidak boleh mengganggu respons ke pengguna.
 */
class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Mulai dari nol: perubahan data yang tercatat hanya milik request ini.
        app(ChangeTracker::class)->flush();

        $response = $next($request);

        try {
            $this->log($request, $response);
        } catch (\Throwable $e) {
            // Sengaja diam — lihat docblock.
        }

        return $response;
    }

    private function log(Request $request, Response $response): void
    {
        $route = $request->route();
        $name = $route?->getName();

        if (ActivityDescriber::isIgnored($name)) {
            return;
        }

        $status = $response->getStatusCode();
        $isRead = in_array($request->method(), ['GET', 'HEAD'], true);
        $isImport = str_ends_with($name, '.import');
        $denied = $status === 403;
        $importRejected = $isImport && $status === 422;

        if (!$denied && !$importRejected) {
            if ($status >= 400) {
                return;
            }
            if ($isRead && !ActivityDescriber::isLoggedGet($name)) {
                return;
            }
        }

        $json = $response instanceof JsonResponse ? $response->getData(true) : null;
        $model = $this->subject($route?->parameters() ?? [], is_array($json) ? $json : null);

        $described = ActivityDescriber::describe($name, [
            'model'   => $model,
            'params'  => $this->stringParams($route?->parameters() ?? []),
            'file'    => $this->uploadedFileName($request),
            'count'   => $this->selectedCount($request),
            'summary' => is_array($json) ? $json : [],
        ]);

        [$action, $text] = $described ?? ($isRead
            ? ['page', 'membuka ' . ($name ?? $request->path())]
            : ActivityDescriber::generic($request->method(), $name));

        $context = ['status' => $status];
        if ($model) {
            $context['data'] = $model['type'] ?? null;
            $context['data_id'] = $model['id'] ?? null;
        }
        if (($file = $this->uploadedFile($request)) !== null) {
            $context['file'] = ActivityDescriber::clean($file->getClientOriginalName(), 120);
            $context['ukuran_kb'] = (int) round(($file->getSize() ?: 0) / 1024);
        }

        if ($denied) {
            ActivityLog::record('denied', 'ditolak (tidak punya hak akses) saat mencoba ' . $text, $context, 'warning');

            return;
        }

        if ($importRejected) {
            $reason = is_array($json) && !empty($json['message'])
                ? ' — ' . ActivityDescriber::clean((string) $json['message'], 160)
                : '';
            ActivityLog::record('upload.failed', 'gagal ' . $text . $reason, $context, 'warning');

            return;
        }

        // Sebelum -> sesudah (input -> output) data yang dibuat/diubah/dihapus request ini, bila ada.
        $context += app(ChangeTracker::class)->summary();

        ActivityLog::record($action, $text, $context);
    }

    /** Model hasil route-model-binding; kalau tidak ada (mis. store), ambil dari JSON respons. */
    private function subject(array $params, ?array $json): ?array
    {
        foreach ($params as $value) {
            if ($value instanceof Model) {
                return ActivityLog::subjectOf($value);
            }
        }

        if ($json && is_array($json['data'] ?? null)) {
            $data = $json['data'];

            return [
                'type'  => null,
                'id'    => $data['id'] ?? null,
                'label' => ActivityDescriber::labelFrom($data),
            ];
        }

        return null;
    }

    /** Parameter route berupa string/angka biasa (mis. {menu}, {file}). */
    private function stringParams(array $params): array
    {
        return array_filter($params, fn ($v) => is_string($v) || is_int($v));
    }

    private function uploadedFile(Request $request)
    {
        foreach ($request->allFiles() as $file) {
            return is_array($file) ? ($file[0] ?? null) : $file;
        }

        return null;
    }

    private function uploadedFileName(Request $request): ?string
    {
        $file = $this->uploadedFile($request);

        return $file ? $file->getClientOriginalName() : null;
    }

    /** Jumlah baris terpilih pada /pilihan/{menu}/* (array id atau string "1,2,3"). */
    private function selectedCount(Request $request): ?int
    {
        $ids = $request->input('ids');

        if (is_array($ids)) {
            return count($ids);
        }
        if (is_string($ids) && trim($ids) !== '') {
            return count(array_filter(explode(',', $ids), fn ($v) => trim($v) !== ''));
        }

        return null;
    }
}
