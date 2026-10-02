<?php

namespace App\Http\Controllers;

use App\Support\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Backup & Restore.
 *  - level "readonly": lihat daftar, buat & unduh backup (tidak mengubah data aplikasi)
 *  - level "penuh"   : tambah upload, pulihkan (restore), dan hapus backup
 * Lihat route di routes/web.php dan HakAkses::ADMIN_READONLY_CEILING_MENUS.
 */
class BackupController extends Controller
{
    public function index(Request $request)
    {
        return view('backup', [
            'backups'    => BackupService::list(),
            'current'    => BackupService::currentCounts(),
            'tables'     => BackupService::TABLES,
            'canRestore' => $request->user()->canAccessMenu('backup', 'penuh'),
            'zipOk'      => BackupService::zipAvailable(),
        ]);
    }

    public function create(Request $request)
    {
        $request->validate(['mode' => ['required', 'in:download,store']]);

        try {
            $path = BackupService::create($request->user()->name);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('backup.index')->with('backup_error', $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Gagal membuat backup. Cek storage/logs/laravel.log.');
        }

        if ($request->input('mode') === 'download') {
            return response()->download($path, basename($path));
        }

        return redirect()->route('backup.index')->with('backup_status', 'Backup berhasil dibuat: ' . basename($path));
    }

    public function download(string $file)
    {
        $path = BackupService::resolveStored($file);
        abort_unless($path, 404);

        return response()->download($path, basename($path));
    }

    public function destroy(string $file)
    {
        $path = BackupService::resolveStored($file);
        abort_unless($path, 404);
        @unlink($path);

        return redirect()->route('backup.index')->with('backup_status', 'Backup dihapus: ' . $file);
    }

    /** Langkah 1 restore: periksa file (upload baru atau yang tersimpan) tanpa mengubah data. */
    public function inspect(Request $request)
    {
        $request->validate([
            'file'   => ['nullable', 'file', 'max:102400'],
            'stored' => ['nullable', 'string', 'max:200'],
        ]);

        BackupService::cleanupUploads();

        try {
            if ($request->hasFile('file')) {
                $token = bin2hex(random_bytes(16));
                $request->file('file')->move(BackupService::uploadDir(), $token . '.zip');
                $path = BackupService::resolveUpload($token);
                $source = ['type' => 'upload', 'value' => $token];
            } elseif ($request->filled('stored')) {
                $path = BackupService::resolveStored($request->input('stored'));
                abort_unless($path, 404);
                $source = ['type' => 'stored', 'value' => $request->input('stored')];
            } else {
                return response()->json(['success' => false, 'message' => 'Pilih file backup terlebih dahulu.'], 422);
            }

            $info = BackupService::inspect($path);
        } catch (RuntimeException $e) {
            if (isset($path) && $source['type'] === 'upload') {
                @unlink($path);
            }

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'source' => $source, 'info' => $info]);
    }

    /** Langkah 2 restore: eksekusi setelah user mengetik kata konfirmasi. */
    public function restore(Request $request)
    {
        $data = $request->validate([
            'source_type' => ['required', 'in:upload,stored'],
            'source'      => ['required', 'string', 'max:200'],
            'confirm'     => ['required', 'in:PULIHKAN'],
        ], ['confirm.in' => 'Ketik PULIHKAN (huruf besar) untuk mengonfirmasi.']);

        $path = $data['source_type'] === 'upload'
            ? BackupService::resolveUpload($data['source'])
            : BackupService::resolveStored($data['source']);
        abort_unless($path, 404);

        $actor = $request->user();

        try {
            $result = BackupService::restore($path, $actor->name);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Pemulihan gagal dan data tidak diubah. Cek storage/logs/laravel.log.',
            ], 422);
        }

        Log::warning('Restore database dijalankan', ['oleh' => $actor->name, 'file' => basename($path), 'snapshot' => $result['snapshot']]);
        if ($data['source_type'] === 'upload') {
            @unlink($path);
        }

        // Semua sesi sudah dihapus; akhiri sesi ini juga.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success'  => true,
            'message'  => 'Pemulihan berhasil. Silakan login kembali.',
            'snapshot' => $result['snapshot'],
            'redirect' => url('/'),
        ]);
    }
}
