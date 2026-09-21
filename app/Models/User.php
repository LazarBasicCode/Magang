<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'nim_nidn',
        'password',
        'role',
    ];

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
     * Label yang ditampilkan di bawah nama pada header, menyesuaikan hak akses
     * user untuk menu yang sedang dibuka. Kalau levelnya "readonly", tampilkan
     * keterangan pratinjau; selain itu tampilkan label peran (Super Admin/Admin/
     * Dosen/Mahasiswa).
     */
    public function accessLabelFor(string $menu): string
    {
        return $this->menuLevel($menu) === 'readonly'
            ? 'Pratinjau · Hanya Lihat'
            : $this->roleLabel();
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
}