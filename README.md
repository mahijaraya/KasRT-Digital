KAS RT DIGITAL

Deskripsi Singkat:
Kas RT Digital adalah website pengelolaan kas dan jimpitan berbasis web yang dirancang untuk memudahkan bendahara RT dalam mencatat, mengelola, dan melaporkan keuangan RT secara digital. Website ini menggantikan sistem pencatatan manual yang rawan kesalahan dan kehilangan data.

Fitur Utama:
1. Login - Sistem autentikasi untuk bendahara dan petugas
2. Dashboard - Ringkasan total jimpitan, pengeluaran, saldo, dan jumlah warga
3. Data Warga - CRUD data warga (tambah, edit, hapus, cari, filter, sorting)
4. Jimpitan - CRUD jimpitan dengan status "Isi" (membayar) dan "Kosong" (belum)
5. Pengeluaran - CRUD pengeluaran kas dengan kategori
6. Laporan Kas - Laporan keuangan dengan filter bulan/tahun
7. Cetak Laporan - Halaman cetak laporan yang rapi
8. Dark Mode - Tampilan gelap untuk kenyamanan mata
9. Responsive Design - Tampilan menyesuaikan di desktop dan mobile

Teknologi yang Digunakan:
- Front-End: HTML5, CSS3, JavaScript, Bootstrap 5, Chart.js
- Back-End: PHP 8.0+
- Database: MySQL 5.7+
- Server Lokal: XAMPP / Laragon
- Version Control: Git & GitHub

Struktur Direktori Proyek:
kas-rt-digital/
│
├── assets/
│   ├── css/
│   │   └── style.css          # CSS utama & dark mode
│   ├── js/
│   │   └── script.js          # JavaScript global
│   └── img/                    # Folder gambar
│
├── config/
│   └── koneksi.php             # Koneksi database
│
├── database/
│   └── kas_rt_digital.sql      # Struktur database
│
├── pages/
│   ├── dashboard.php           # Halaman dashboard
│   ├── warga.php               # CRUD data warga
│   ├── jimpitan.php            # CRUD jimpitan
│   ├── pengeluaran.php         # CRUD pengeluaran
│   └── laporan.php             # Laporan kas & cetak
│
├── proses/
│   ├── proses_login.php        # Proses login
│   ├── proses_warga.php        # CRUD warga
│   ├── proses_jimpitan.php     # CRUD jimpitan
│   └── proses_pengeluaran.php  # CRUD pengeluaran
│
├── cetak_laporan.php           # Halaman cetak laporan
├── index.php                   # Redirect ke login
├── login.php                   # Halaman login
├── logout.php                  # Proses logout
└── README.md                   # Dokumentasi ini

Perubahan utama dari versi sebelumnya:
1. Input pemasukan/iuran disederhanakan menjadi input jimpitan saja.
2. File halaman utama untuk input kas masuk: pages/jimpitan.php
3. File proses CRUD jimpitan: proses/proses_jimpitan.php
4. Tabel database yang dipakai untuk kas masuk: jimpitan
5. Menu "Pemasukan / Jimpitan" diganti menjadi "Jimpitan".
6. Dashboard menampilkan Total Jimpitan, Total Pengeluaran, Saldo Akhir, dan Jumlah Warga.
7. Laporan menampilkan rekap Jimpitan dan Pengeluaran.
8. File lama pemasukan.php dan proses_pemasukan.php tetap ada sebagai redirect agar link lama tidak error.

Cara menjalankan:
1. Ekstrak folder kas-rt-digital ke htdocs XAMPP.
2. Buka phpMyAdmin.
3. Buat/import database dari file database/kas_rt_digital.sql.
4. Sesuaikan config/koneksi.php:
   - host: localhost
   - user: root
   - password: kosong atau sesuai MySQL
   - database: kas_rt_digital
   - port: 8111. Jika XAMPP memakai default, ganti menjadi 3306.
5. Jalankan di browser:
   http://localhost/kas-rt-digital/

Akun login default:
username: bendahara
password: 12345

Catatan: Untuk menambah user baru, jalankan query SQL di phpMyAdmin atau gunakan file generate_hash.php (hapus setelah selesai).

Troubleshooting:
1. Error "Cannot redeclare formatRupiah()" - Hapus atau komentari salah satu fungsi formatRupiah() yang muncul di file config/koneksi.php atau pages/dashboard.php.
2. Error "Access denied for user 'root'@'localhost'" - Cek password MySQL di config/koneksi.php.
3. Error "Table 'kas_rt_digital.users' doesn't exist" - Import ulang file database/kas_rt_digital.sql di phpMyAdmin.
4. Port MySQL Bermasalah - Jika XAMPP menggunakan port MySQL selain 3306 (misal 8111), ubah di config/koneksi.php.

Tim Pengembang:
- Amar Fathur Rahman (25SA31A076) - Login, Dashboard, Integrasi
- Yazid 'Abdul Chamid (25SA31A019) - Front-End Form & Data
- Mahija Raya Dagnafauz Sunarya (22SA31A071) - Back-End & Database

Mata Kuliah: Pemrograman Web
Dosen Pengampu: Banu Dwi Putranto, M.Kom.
Kelas: TI25A

Link Penting:
- Video Presentasi: [https://youtu.be/[link]](https://youtu.be/gZfStnjvzdM?si=e3sM3dKdKaXOTu7E)
- Website Online: https://kas-rt-digital.site.je/
- Repository GitHub: https://github.com/mahijaraya/KasRT-Digital.git
