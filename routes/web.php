<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Controllers\LppmDosenController;
use App\Http\Controllers\LppmMahasiswaController;
use App\Http\Controllers\LppmRekognisiController;
use App\Http\Controllers\KerjaSamaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\BulkSelectionController;
use App\Http\Controllers\CetakLaporanController;
use App\Http\Controllers\LoginAuditController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AccountController;

// ---- Route khusus TAMU (belum login) ----
// Middleware 'guest' mengalihkan user yang sudah login ke /dashboard, jadi
// tidak bisa membuka halaman login, mengirim login baru, atau memakai
// lupa/reset password selama sesinya masih aktif. Untuk ganti akun,
// user wajib logout dulu.
Route::middleware('guest')->group(function () {
    // Halaman Login (index.blade.php)
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');

    // Pemrosesan login
    Route::post('/login-process', [AuthController::class, 'loginProcess'])
        ->middleware('throttle:login-ip');

    // ---- Lupa Password ----
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Logout hanya untuk user yang sedang login (POST + CSRF, bukan GET).
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Rute yang dilindungi (Hanya bisa diakses jika sudah login)
Route::middleware(['auth'])->group(function () {
    // ---- Pengaturan akun sendiri (menu profil kanan atas) ----
    Route::post('/account/password', [AccountController::class, 'changePassword'])->name('account.password');
    Route::post('/account/recovery-email', [AccountController::class, 'updateRecoveryEmail'])->name('account.recovery-email');

    // ---- Dashboard ----
    // Terbuka untuk semua user yang login (tidak diatur lewat Hak Akses).
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // ---- Kemahasiswaan ----
    Route::middleware('menu.access:kemahasiswaan,readonly')->group(function () {
        Route::get('/kemahasiswaan', [KemahasiswaanController::class, 'index'])->name('kemahasiswaan.index');
        // Unduh seluruh data (CSV) — pembatasan role admin/superadmin ada di controller.
        Route::get('/kemahasiswaan/export', [KemahasiswaanController::class, 'export'])->name('kemahasiswaan.export');
    });
    // Template & unggah massal CSV — hanya admin/superadmin dengan akses penuh.
    Route::middleware('menu.access:kemahasiswaan,penuh')->group(function () {
        Route::get('/kemahasiswaan/template', [KemahasiswaanController::class, 'template'])->name('kemahasiswaan.template');
        Route::post('/kemahasiswaan/import', [KemahasiswaanController::class, 'import'])->name('kemahasiswaan.import');
    });
    Route::middleware('menu.access:kemahasiswaan,biasa')->group(function () {
        Route::post('/kemahasiswaan', [KemahasiswaanController::class, 'store'])->name('kemahasiswaan.store');
        Route::put('/kemahasiswaan/{kemahasiswaan}', [KemahasiswaanController::class, 'update'])->name('kemahasiswaan.update');
        Route::delete('/kemahasiswaan/{kemahasiswaan}', [KemahasiswaanController::class, 'destroy'])->name('kemahasiswaan.destroy');
    });

    // ---- LPPM Dosen ----
    Route::middleware('menu.access:lppm_dosen,readonly')->group(function () {
        Route::get('/lppm/dosen', [LppmDosenController::class, 'index'])->name('lppm.dosen.index');
        // Unduh seluruh data (CSV) — pembatasan role admin/superadmin ada di controller.
        Route::get('/lppm/dosen/export', [LppmDosenController::class, 'export'])->name('lppm.dosen.export');
    });
    // Template & unggah massal CSV — hanya admin/superadmin dengan akses penuh.
    Route::middleware('menu.access:lppm_dosen,penuh')->group(function () {
        Route::get('/lppm/dosen/template', [LppmDosenController::class, 'template'])->name('lppm.dosen.template');
        Route::post('/lppm/dosen/import', [LppmDosenController::class, 'import'])->name('lppm.dosen.import');
    });
    Route::middleware('menu.access:lppm_dosen,biasa')->group(function () {
        Route::post('/lppm/dosen', [LppmDosenController::class, 'store'])->name('lppm.dosen.store');
        Route::put('/lppm/dosen/{lppmDosen}', [LppmDosenController::class, 'update'])->name('lppm.dosen.update');
        Route::delete('/lppm/dosen/{lppmDosen}', [LppmDosenController::class, 'destroy'])->name('lppm.dosen.destroy');
    });

    // ---- LPPM Mahasiswa ----
    Route::middleware('menu.access:lppm_mahasiswa,readonly')->group(function () {
        Route::get('/lppm/mahasiswa', [LppmMahasiswaController::class, 'index'])->name('lppm.mahasiswa.index');
        // Unduh seluruh data (CSV) — pembatasan role admin/superadmin ada di controller.
        Route::get('/lppm/mahasiswa/export', [LppmMahasiswaController::class, 'export'])->name('lppm.mahasiswa.export');
    });
    // Template & unggah massal CSV — hanya admin/superadmin dengan akses penuh.
    Route::middleware('menu.access:lppm_mahasiswa,penuh')->group(function () {
        Route::get('/lppm/mahasiswa/template', [LppmMahasiswaController::class, 'template'])->name('lppm.mahasiswa.template');
        Route::post('/lppm/mahasiswa/import', [LppmMahasiswaController::class, 'import'])->name('lppm.mahasiswa.import');
    });
    Route::middleware('menu.access:lppm_mahasiswa,biasa')->group(function () {
        Route::post('/lppm/mahasiswa', [LppmMahasiswaController::class, 'store'])->name('lppm.mahasiswa.store');
        Route::put('/lppm/mahasiswa/{lppmMahasiswa}', [LppmMahasiswaController::class, 'update'])->name('lppm.mahasiswa.update');
        Route::delete('/lppm/mahasiswa/{lppmMahasiswa}', [LppmMahasiswaController::class, 'destroy'])->name('lppm.mahasiswa.destroy');
    });

    // ---- LPPM Rekognisi ----
    Route::middleware('menu.access:rekognisi,readonly')->group(function () {
        Route::get('/lppm/rekognisi', [LppmRekognisiController::class, 'index'])->name('lppm.rekognisi.index');
        // Unduh seluruh data (CSV) — pembatasan role admin/superadmin ada di controller.
        Route::get('/lppm/rekognisi/export', [LppmRekognisiController::class, 'export'])->name('lppm.rekognisi.export');
    });
    // Template & unggah massal CSV — hanya admin/superadmin dengan akses penuh.
    Route::middleware('menu.access:rekognisi,penuh')->group(function () {
        Route::get('/lppm/rekognisi/template', [LppmRekognisiController::class, 'template'])->name('lppm.rekognisi.template');
        Route::post('/lppm/rekognisi/import', [LppmRekognisiController::class, 'import'])->name('lppm.rekognisi.import');
    });
    Route::middleware('menu.access:rekognisi,biasa')->group(function () {
        Route::post('/lppm/rekognisi', [LppmRekognisiController::class, 'store'])->name('lppm.rekognisi.store');
        Route::put('/lppm/rekognisi/{rekognisi}', [LppmRekognisiController::class, 'update'])->name('lppm.rekognisi.update');
        Route::delete('/lppm/rekognisi/{rekognisi}', [LppmRekognisiController::class, 'destroy'])->name('lppm.rekognisi.destroy');
    });

    // ---- Kerja Sama ----
    Route::middleware('menu.access:kerja_sama,readonly')->group(function () {
        Route::get('/kerja-sama', [KerjaSamaController::class, 'index'])->name('kerja-sama.index');
        // Unduh seluruh data (CSV) — pembatasan role admin/superadmin ada di controller.
        Route::get('/kerja-sama/export', [KerjaSamaController::class, 'export'])->name('kerja-sama.export');
    });
    // Template & unggah massal CSV — hanya admin/superadmin dengan akses penuh.
    Route::middleware('menu.access:kerja_sama,penuh')->group(function () {
        Route::get('/kerja-sama/template', [KerjaSamaController::class, 'template'])->name('kerja-sama.template');
        Route::post('/kerja-sama/import', [KerjaSamaController::class, 'import'])->name('kerja-sama.import');
    });
    Route::middleware('menu.access:kerja_sama,biasa')->group(function () {
        Route::post('/kerja-sama', [KerjaSamaController::class, 'store'])->name('kerja-sama.store');
        Route::put('/kerja-sama/{kerjaSama}', [KerjaSamaController::class, 'update'])->name('kerja-sama.update');
        Route::delete('/kerja-sama/{kerjaSama}', [KerjaSamaController::class, 'destroy'])->name('kerja-sama.destroy');
    });

    // ---- Data Master Users ----
    Route::middleware('menu.access:data_master,readonly')->group(function () {
        Route::get('/data-master/users', [UserController::class, 'index'])->name('data-master.users.index');
        // Unduh data akun (Excel) — pembatasan role admin/superadmin ada di controller.
        Route::get('/data-master/users/export', [UserController::class, 'export'])->name('data-master.users.export');
    });
    // Template & unggah massal Excel — hanya admin/superadmin dengan akses penuh.
    Route::middleware('menu.access:data_master,penuh')->group(function () {
        Route::get('/data-master/users/template', [UserController::class, 'template'])->name('data-master.users.template');
        Route::post('/data-master/users/import', [UserController::class, 'import'])->name('data-master.users.import');
    });
    Route::middleware('menu.access:data_master,biasa')->group(function () {
        Route::post('/data-master/users', [UserController::class, 'store'])->name('data-master.users.store');
        Route::put('/data-master/users/{user}', [UserController::class, 'update'])->name('data-master.users.update');
        Route::delete('/data-master/users/{user}', [UserController::class, 'destroy'])->name('data-master.users.destroy');
    });

    // ---- Hak Akses ----
    // Akses menu ini sendiri diatur lewat sistem hak akses yang sama:
    // default admin = tidak diberi akses, sampai superadmin mengubahnya
    // lewat halaman ini juga.
    Route::middleware('menu.access:hak_akses,readonly')->group(function () {
        Route::get('/hak-akses', [HakAksesController::class, 'index'])->name('hak-akses.index');
    });
    Route::middleware('menu.access:hak_akses,biasa')->group(function () {
        Route::put('/hak-akses/{user}', [HakAksesController::class, 'update'])->name('hak-akses.update');
    });

    // ---- Audit Percobaan Login ----
    // Dibatasi ke role superadmin langsung di controller (lihat
    // LoginAuditController), bukan lewat menu.access, karena isinya data
    // keamanan yang belum perlu masuk sistem hak-akses per-menu.
    // ---- Backup & Restore ----
    // readonly: lihat daftar, buat & unduh backup. penuh: upload, restore, hapus.
    Route::middleware('menu.access:backup,readonly')->group(function () {
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup/create', [BackupController::class, 'create'])->name('backup.create');
        Route::get('/backup/download/{file}', [BackupController::class, 'download'])->name('backup.download');
    });
    Route::middleware('menu.access:backup,penuh')->group(function () {
        Route::post('/backup/inspect', [BackupController::class, 'inspect'])->name('backup.inspect');
        Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
        Route::delete('/backup/{file}', [BackupController::class, 'destroy'])->name('backup.destroy');
    });

    // ---- Laporan (khusus superadmin, dicek di controller) ----
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // ---- Cetak Laporan per menu (admin/superadmin; akses menu dicek di controller) ----
    // Daftar menu & kolom: App\Support\PrintReports. Slug: kemahasiswaan, lppm-mahasiswa, lppm-dosen, rekognisi, kerja-sama, data-master.
    Route::get('/cetak/{menu}', [CetakLaporanController::class, 'show'])->name('cetak.show');

    // ---- Aksi untuk data terpilih (centang baris tabel): export & cetak. Daftar menu: BulkSelectionController::MENUS ----
    Route::post('/pilihan/{menu}/export', [BulkSelectionController::class, 'export'])->name('pilihan.export');
    Route::post('/pilihan/{menu}/cetak', [BulkSelectionController::class, 'cetak'])->name('pilihan.cetak');
    Route::post('/pilihan/{menu}/hapus', [BulkSelectionController::class, 'hapus'])->name('pilihan.hapus');
    Route::post('/pilihan/{menu}/ubah', [BulkSelectionController::class, 'ubah'])->name('pilihan.ubah');

    Route::get('/login-audit', [LoginAuditController::class, 'index'])->name('login-audit.index');
    Route::get('/login-audit/data', [LoginAuditController::class, 'data'])->name('login-audit.data');
    Route::get('/login-audit/{attempt}', [LoginAuditController::class, 'show'])->name('login-audit.show');

    // ---- Notifikasi (untuk semua user yang login, bukan cuma admin) ----
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{userNotification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});
