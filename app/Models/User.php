<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'nim_nidn',
        'email',
        'password',
        'role',
    ];

    /**
     * Kirim notifikasi reset password memakai template SIDA sendiri
     * (bukan template default Laravel), supaya tautannya mengarah ke
     * halaman /reset-password kita dan teksnya berbahasa Indonesia.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi ke tabel Mahasiswa (1 User memiliki 1 profil Mahasiswa)
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    // Relasi ke tabel Dosen (1 User memiliki 1 profil Dosen)
    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    // Relasi ke tabel Rekognisi (1 User bisa punya banyak Rekognisi)
    public function rekognisi()
    {
        return $this->hasMany(Rekognisi::class);
    }

    // Relasi ke tabel Kerja_Sama (1 User bisa menginput banyak Kerja Sama)
    public function kerjaSama()
    {
        return $this->hasMany(KerjaSama::class);
    }

    // Relasi ke baris Hak Akses milik user ini (satu baris per menu)
    public function hakAkses()
    {
        return $this->hasMany(HakAkses::class);
    }

    /**
     * Level akses efektif user ini untuk satu menu tertentu.
     * Superadmin selalu 'penuh' untuk semua menu (bypass, tidak tergantung DB).
     * Kalau ada baris hak_akses eksplisit (pernah diatur superadmin), pakai itu.
     * Kalau belum pernah diatur, jatuh ke default per role (lihat HakAkses::defaultsForRole).
     */
    public function menuLevel(string $menu): string
    {
        if ($this->role === 'superadmin') {
            return 'penuh';
        }

        $row = $this->relationLoaded('hakAkses')
            ? $this->hakAkses->firstWhere('menu', $menu)
            : $this->hakAkses()->where('menu', $menu)->first();

        if ($row && HakAkses::isValidLevel($row->level)) {
            return $row->level;
        }

        return HakAkses::defaultsForRole($this->role)[$menu] ?? 'none';
    }

    /**
     * True kalau level akses user untuk $menu >= $min (default: 'readonly',
     * artinya minimal bisa melihat halamannya).
     */
    public function canAccessMenu(string $menu, string $min = 'readonly'): bool
    {
        $current = HakAkses::RANK[$this->menuLevel($menu)] ?? 0;
        $required = HakAkses::RANK[$min] ?? 0;

        return $current >= $required;
    }

    /**
     * Label peran yang enak dibaca untuk ditampilkan di UI (header profil, dsb).
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'admin'      => 'Admin',
            'dosen'      => 'Dosen',
            'mahasiswa'  => 'Mahasiswa',
            default      => ucfirst($this->role ?? '-'),
        };
    }

    /**
     * Menu "operasional" yang menentukan label peran dinamis admin di header
     * profile (lihat adminRoleLabel()). Sengaja TIDAK termasuk menu
     * admin-only (data_master, hak_akses, log): menu-menu itu administratif,
     * bukan bidang kerja, dan admin baru selalu default 'penuh' di
     * data_master — kalau ikut dihitung, semua admin baru akan langsung
     * berlabel "Admin Data Master" alih-alih "Admin SIDA".
     */
    public const OPERATIONAL_MENUS = [
        'kemahasiswaan',
        'lppm_mahasiswa',
        'lppm_dosen',
        'rekognisi',
        'kerja_sama',
    ];

    /**
     * Label peran dinamis untuk admin, dipakai di header-profile-role.
     * Aturan (lihat juga allMenuLevels() untuk levels-nya):
     * - Tepat SATU menu operasional levelnya 'penuh', sisanya bukan 'penuh'
     *   -> "Admin {Label Menu Itu}" (mis. "Admin Kemahasiswaan").
     * - Tidak ada menu operasional yang 'penuh' (campuran none/readonly/
     *   biasa) -> "Admin SIDA".
     * - Dua menu operasional atau lebih yang 'penuh' -> "Admin SIDA" juga,
     *   karena label tidak bisa mewakili spesialisasi tunggal.
     * Hanya dipakai untuk role 'admin'; role lain pakai roleLabel() biasa.
     */
    public function adminRoleLabel(): string
    {
        $levels = $this->allMenuLevels();

        $fullMenus = array_values(array_filter(
            self::OPERATIONAL_MENUS,
            fn ($menu) => ($levels[$menu] ?? 'none') === 'penuh'
        ));

        if (count($fullMenus) === 1) {
            $menu = $fullMenus[0];
            return 'Admin ' . (HakAkses::MENUS[$menu]['label'] ?? ucfirst($menu));
        }

        return 'Admin SIDA';
    }

    /**
     * Label yang ditampilkan di bawah nama pada header, menyesuaikan hak akses
     * user untuk menu yang sedang dibuka.
     * - "readonly" -> keterangan pratinjau, berlaku untuk semua role.
     * - role 'admin' (bukan readonly) -> label dinamis dari adminRoleLabel()
     *   (mis. "Admin Kemahasiswaan" / "Admin SIDA"), supaya header
     *   mencerminkan bidang kerja admin sesuai hak akses yang superadmin
     *   berikan, bukan cuma teks statis "Admin".
     * - role lain (superadmin/dosen/mahasiswa) -> label peran biasa.
     */
    public function accessLabelFor(string $menu): string
    {
        if ($this->menuLevel($menu) === 'readonly') {
            return 'Pratinjau · Hanya Lihat';
        }

        if ($this->role === 'admin') {
            return $this->adminRoleLabel();
        }

        return $this->roleLabel();
    }

    /**
     * Ringkasan akses per menu untuk ditampilkan di dropdown "Informasi
     * Akses" pada header profile: label menu, ikon, level, dan teks level
     * yang enak dibaca. Menu dengan level 'none' tetap disertakan (dropdown
     * yang menampilkannya boleh memilih untuk menyembunyikan atau meredupkan
     * baris 'none' di sisi tampilan).
     */
    public function accessBreakdown(): array
    {
        $levels = $this->allMenuLevels();

        $levelLabels = [
            'penuh'    => 'Akses Penuh',
            'biasa'    => 'Akses Biasa',
            'readonly' => 'Hanya Lihat',
            'none'     => 'Tidak Ada Akses',
        ];

        $rows = [];
        foreach (HakAkses::MENUS as $key => $menu) {
            $level = $levels[$key] ?? 'none';
            $rows[] = [
                'key'   => $key,
                'label' => $menu['label'],
                'icon'  => $menu['icon'],
                'level' => $level,
                'level_label' => $levelLabels[$level] ?? ucfirst($level),
            ];
        }

        return $rows;
    }

    /**
     * Inisial nama (maks. 2 huruf) untuk avatar di header profile, memakai
     * pola yang sama dengan avatar mahasiswa di tabel Kemahasiswaan
     * (lihat kemahasiswaan.blade.php).
     */
    public function initials(): string
    {
        return collect(explode(' ', trim($this->name ?? '')))
            ->filter()
            ->take(2)
            ->map(fn ($w) => strtoupper($w[0]))
            ->implode('');
    }

    /**
     * Kelas warna avatar (c-primary/c-info/dst, lihat style.css), dipilih
     * konsisten berdasarkan id user supaya warnanya stabil setiap render,
     * sama seperti avatarColor() untuk mahasiswa di tabel.
     */
    public function avatarColorClass(): string
    {
        $colors = ['c-primary', 'c-info', 'c-warning', 'c-success', 'c-danger'];
        return $colors[$this->id % count($colors)];
    }

    /**
     * Semua level akses user ini, per menu (dipakai untuk mengisi modal
     * Hak Akses & menghitung status/ringkasan tanpa query berulang).
     */
    public function allMenuLevels(): array
    {
        $levels = [];
        foreach (array_keys(HakAkses::MENUS) as $menu) {
            $levels[$menu] = $this->menuLevel($menu);
        }
        return $levels;
    }

    /**
     * Urutan peringkat role, dipakai untuk perbandingan hierarki
     * (superadmin > admin > dosen/mahasiswa). Dosen & mahasiswa sengaja
     * diberi peringkat sama karena tidak ada relasi kelola di antara
     * keduanya lewat Data Master.
     */
    public const ROLE_RANK = [
        'superadmin' => 3,
        'admin'      => 2,
        'dosen'      => 1,
        'mahasiswa'  => 1,
    ];

    public function roleRank(): int
    {
        return self::ROLE_RANK[$this->role] ?? 0;
    }

    /**
     * True kalau user ini ($this, sebagai "actor") boleh mengelola
     * (edit/hapus/ubah role) akun $target lewat Data Master Pengguna.
     *
     * Aturan hierarki:
     * - superadmin boleh mengelola siapa saja, termasuk superadmin lain.
     * - admin hanya boleh mengelola role di bawahnya sendiri (dosen,
     *   mahasiswa) — TIDAK admin lain, TIDAK superadmin, TIDAK dirinya
     *   sendiri (pakai id check terpisah untuk kasus itu di controller).
     * - dosen/mahasiswa tidak pernah boleh mengelola user lain lewat
     *   endpoint ini (mereka semestinya tidak lolos middleware menu.access
     *   sama sekali, tapi dicek juga di sini sebagai jaring pengaman).
     */
    public function canManageTargetUser(User $target): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        if ($this->role === 'admin') {
            return $target->roleRank() < $this->roleRank();
        }

        return false;
    }
}