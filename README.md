# 🅿 WJ-PARK Analytics

> Aplikasi web pengelolaan penitipan motor yang ringan, modern, dan siap pakai.
> Dibangun dengan PHP Native + MySQL — tanpa framework, tanpa ribet.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-10b981?style=flat-square)
![Status](https://img.shields.io/badge/Status-Stable-6366f1?style=flat-square)

---

## 💡 Tentang

**WJ-PARK Analytics** adalah aplikasi manajemen penitipan motor berbasis web
yang dirancang untuk usaha parkir skala kecil hingga menengah.

Banyak usaha parkir masih mengandalkan buku tulis untuk mencatat kendaraan,
menghitung biaya secara manual, dan merekap pendapatan di akhir hari.
WJ-PARK hadir untuk menggantikan semua itu — dengan sistem digital yang
**ringan, cepat, dan mudah digunakan bahkan oleh operator yang tidak
terbiasa dengan teknologi.**

---

## ✨ Fitur

### Operasional Harian
- 🔵 **Catat Kendaraan Masuk** — Input nomor plat (auto-uppercase), pilih jenis kendaraan, timestamp otomatis. Sistem langsung mendeteksi duplikat plat dan mengecek kapasitas.
- 🟢 **Proses Kendaraan Keluar** — Cari plat, lihat durasi yang terus berjalan live, biaya terhitung otomatis setiap detik. Konfirmasi → selesai → cetak struk.
- 🅿 **Monitor Kapasitas** — Peta visual slot parkir per nomor. Merah = terisi, hijau = kosong. Auto-refresh setiap 60 detik.

### Analitik & Laporan
- 📊 **Dashboard** — Statistik harian (pendapatan, kendaraan, kapasitas, durasi), grafik tren pendapatan 7 hari, grafik distribusi kendaraan per jam.
- 📋 **Laporan Transaksi** — Filter harian dan bulanan, rekap per hari, hapus data via checkbox / rentang tanggal, cetak langsung dari browser.

### Manajemen
- 👥 **Manajemen User** — Tambah, edit, nonaktifkan akun operator. Password dienkripsi dengan bcrypt.
- 💰 **Kelola Tarif** — Atur tarif per jenis kendaraan, simulasi biaya interaktif.
- 🌙☀️ **Dark / Light Mode** — Toggle tema, tersimpan di localStorage.

---

## 🖼️ Preview

> Tambahkan screenshot di folder `/screenshots` lalu ganti tabel di bawah ini.

| Login | Dashboard |
|-------|-----------|
| ![login](screenshots/login.png) | ![dashboard](screenshots/dashboard.png) |

| Kendaraan Masuk | Monitor Kapasitas |
|-----------------|-------------------|
| ![masuk](screenshots/masuk.png) | ![kapasitas](screenshots/kapasitas.png) |

---

## 🛠️ Tech Stack
PHP 8.x       – Backend, logika bisnis, routing
MySQL 8.0     – Database via PHP PDO (prepared statements)
HTML5 + CSS3  – Antarmuka, CSS variables untuk theming
JavaScript    – Vanilla JS, tidak ada jQuery
Chart.js v4   – Grafik dashboard
Apache/XAMPP  – Web server lokal

Tidak ada framework, tidak ada Composer, tidak ada npm.
Clone → import SQL → buka di browser. Selesai.

---

## 🚀 Instalasi

### Dengan XAMPP (Lokal)

```bash
# Clone
git clone https://github.com/username/wjpark-analytics.git

# Pindah ke htdocs
cp -r wjpark-analytics C:/xampp/htdocs/wjpark
```

Import database:
1. Buka `http://localhost/phpmyadmin`
2. Buat database baru: `db_wjpark`
3. Import file `database.sql`

Buka di browser:
http://localhost/wjpark/

### Dengan Hosting (Online)

1. Upload seluruh file ke folder `public_html/wjpark/` via FileManager atau FTP
2. Buat database baru di cPanel → import `database.sql`
3. Edit `koneksi.php` — sesuaikan `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
4. Akses via domain

---

## 🔑 Akun Default

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Operator | `operator1` | `admin123` |

> Ganti password setelah pertama login melalui menu **Manajemen User**.

---

## ⚙️ Konfigurasi

Edit `koneksi.php`:

```php
define('DB_HOST', 'localhost');   // Host database
define('DB_NAME', 'db_wjpark');   // Nama database
define('DB_USER', 'root');        // Username MySQL
define('DB_PASS', '');            // Password MySQL
```

---

## 🔒 Keamanan

- Password di-hash dengan **bcrypt** (`password_hash` / `password_verify`)
- Seluruh query menggunakan **PDO prepared statements** — aman dari SQL Injection
- Session regenerate setelah login untuk mencegah session fixation
- Role-based access control (RBAC) di setiap halaman

---

## 🗺️ Roadmap

- [ ] Export laporan ke Excel (.xlsx)
- [ ] Notifikasi kapasitas hampir penuh (push/SMS)
- [ ] Manajemen multi-lokasi parkir
- [ ] PWA / mobile app wrapper
- [ ] Integrasi QRIS / pembayaran digital
- [ ] Prediksi pendapatan berbasis data historis

---

## 🤝 Kontribusi

Pull request, issue, dan saran sangat disambut.

```bash
# Fork → clone → buat branch baru
git checkout -b feature/nama-fitur

# Commit
git commit -m "feat: tambah fitur X"

# Push & buat Pull Request
git push origin feature/nama-fitur
```

---

## 📄 Lisensi

[MIT License](LICENSE) — bebas digunakan dan dimodifikasi
dengan menyertakan atribusi.

---

<div align="center">
  <b>WJ-PARK Analytics</b> — Simple parking management, done right.
</div>
