<div align="center">

# 🎓 Proyek_Magang

**Sistem Informasi Terpadu untuk Manajemen Kemahasiswaan, Penelitian, dan Kerja Sama**

Platform berbasis Laravel untuk mengelola prestasi mahasiswa, publikasi ilmiah dosen, rekognisi, dan kerja sama institusi — dalam satu ekosistem digital.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)](#lisensi)

</div>

---

## 📖 Tentang Proyek

**Proyek_Magang** adalah sistem informasi yang dirancang untuk mendigitalisasi pengelolaan data kemahasiswaan dan akademik di lingkungan kampus. Sistem ini menyatukan tiga pilar utama kegiatan kampus — kemahasiswaan, penelitian & pengabdian masyarakat (LPPM), dan kerja sama institusional — ke dalam satu platform yang terstruktur dan mudah diaudit.

Setiap capaian, mulai dari prestasi lomba mahasiswa hingga publikasi jurnal internasional dosen, tercatat rapi lengkap dengan bukti pendukung, sehingga memudahkan proses akreditasi, pelaporan, dan pengambilan keputusan berbasis data.

## ✨ Fitur Utama

<table>
<tr>
<td width="33%" valign="top">

### 🏆 Kemahasiswaan
- Data prestasi Inbis (Inkubasi Bisnis)
- Data prestasi non-akademik & akademik
- Filter berdasarkan tingkat (Lokal/Nasional/Internasional)
- Filter tahun (TS-2, TS-1, TS)
- Upload bukti (SK, Foto, Sertifikat)

</td>
<td width="33%" valign="top">

### 🔬 LPPM
- Publikasi mahasiswa (Sinta, Conference, Jurnal Internasional)
- Publikasi dosen (Q1-Q4, HKI, Sinta, Buku)
- Rekognisi nasional, internasional & alumni
- Pencatatan DOI & peringkat jurnal

</td>
<td width="33%" valign="top">

### 🤝 Kerja Sama
- Conference & PKL mahasiswa
- Keynote speaker dosen
- Guest lecture (inbound/outbound)
- Pengabdian & riset internasional

</td>
</tr>
</table>

### 🔐 Manajemen Hak Akses Granular

Sistem role-based access control dengan 4 tingkatan pengguna:

| Role | Deskripsi |
|---|---|
| 👑 **Super Admin** | Kontrol penuh sistem, termasuk pengaturan hak akses Admin & Dosen |
| 🛡️ **Admin** | Kelola data master (user, mahasiswa, dosen) sesuai hak akses yang diberikan |
| 👨‍🏫 **Dosen** | Input & kelola data LPPM, rekognisi, dan kerja sama pribadi |
| 🎓 **Mahasiswa** | Input & kelola data prestasi, LPPM, dan kerja sama pribadi |

## 🗂️ Struktur Basis Data

Sistem ini terdiri dari **9 tabel utama**:

```
users            → data akun & role pengguna
├── mahasiswa     → profil mahasiswa (NIM)
├── dosen         → profil dosen (NIDN)
├── hak_akses     → matriks izin akses menu per user
├── kemahasiswaan → data prestasi (Inbis & Kemahasiswaan)
├── lppm_mahasiswa → publikasi ilmiah mahasiswa
├── lppm_dosen    → publikasi ilmiah dosen
├── rekognisi     → rekognisi nasional/internasional/alumni
└── kerja_sama    → kegiatan kerja sama institusi
```

> 📌 Lihat detail skema lengkap di [`docs/erd.md`](docs/erd.md) *(opsional, sesuaikan dengan struktur repo kamu)*

## 🛠️ Tech Stack

- **Backend:** Laravel 10.x (PHP 8.1+)
- **Database:** MySQL 8.0
- **Frontend:** *(sesuaikan — Blade / Vue / React)*
- **Autentikasi:** Laravel Sanctum / Breeze *(sesuaikan)*

## 🚀 Instalasi

### Prasyarat

Pastikan sudah terpasang di sistem kamu:

- PHP >= 8.1
- Composer
- MySQL / MariaDB
- Node.js & NPM

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/username/Proyek_Magang.git
cd Proyek_Magang

# 2. Install dependency PHP
composer install

# 3. Install dependency frontend
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
# DB_DATABASE=proyek_magang
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Jalankan migration
php artisan migrate

# 8. (Opsional) Jalankan seeder
php artisan db:seed

# 9. Build asset frontend
npm run build

# 10. Jalankan server lokal
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000` 🎉

## 📁 Struktur Direktori Penting

```
├── app/
│   ├── Models/           # Model Eloquent (User, Mahasiswa, Dosen, dll)
│   └── Http/Controllers/ # Controller per modul
├── database/
│   ├── migrations/       # 9 migration tabel inti sistem
│   └── seeders/
├── resources/
│   └── views/            # Tampilan per role & menu
└── routes/
    └── web.php
```

## 🗺️ Roadmap

- [x] Rancangan ERD & DFD sistem
- [x] Migration database (9 tabel)
- [ ] Model & relasi Eloquent
- [ ] Modul autentikasi & hak akses
- [ ] Modul Kemahasiswaan
- [ ] Modul LPPM (Dosen & Mahasiswa)
- [ ] Modul Kerja Sama
- [ ] Dashboard rekap & laporan
- [ ] Export laporan (PDF/Excel)

## 🤝 Kontribusi

Proyek ini dikembangkan sebagai bagian dari program PKL. Saran dan masukan sangat terbuka melalui *issue* atau *pull request*.

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur/nama-fitur`)
3. Commit perubahan (`git commit -m 'Menambahkan fitur x'`)
4. Push ke branch (`git push origin fitur/nama-fitur`)
5. Buka Pull Request

## 📄 Lisensi

Didistribusikan di bawah lisensi MIT. Lihat `LICENSE` untuk informasi lebih lanjut.

## 👤 Kontak

Dikembangkan sebagai proyek PKL — *Proyek_Magang*

---

<div align="center">
<sub>Dibuat dengan 💜 untuk mendukung digitalisasi data kampus</sub>
</div>
