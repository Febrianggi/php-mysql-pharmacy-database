# GFF Farma: Website Database Apotek

Bahasa: [English](README.md) | Bahasa Indonesia

> Catatan: tampilan website, data Excel, dan user guideline ditulis dalam Bahasa Indonesia. README ini tersedia dalam bahasa Inggris dan Indonesia.

*Ad Veritatem per Caritatem*

GFF Farma adalah aplikasi web berbasis PHP dan MySQL untuk mengelola stok obat, akun pelanggan, dan transaksi pembelian di apotek skala kecil. Proyek ini dibuat sebagai Project Akhir mata kuliah Basis Data tahun 2025.

## Demo langsung

Demo statis berjalan di GitHub Pages: https://febrianggi.github.io/php-mysql-pharmacy-database/

Demo ini salinan tampilan yang hanya berjalan di browser, tanpa PHP dan MySQL. Data contoh ada di dalam `index.html`, dan semua perubahan yang kamu buat (pembelian, obat baru, edit, akun baru) tersimpan di browsermu sendiri lewat localStorage. Tombol "Reset data demo" mengembalikan data ke awal.

| Peran | Username | Password |
|---|---|---|
| Pelanggan | `demo` | `demo123` |
| Admin | `admin` | `admin123` |

Akun ini hanya ada di demo dan terpisah dari akun admin pada versi lengkap PHP dan MySQL di `app/`.

## Latar belakang

Apotek skala UMKM punya kewajiban pelaporan pajak yang sama dengan perusahaan besar, tetapi banyak yang hanya memakai buku tulis atau tidak mencatat sama sekali, sehingga akuntansi dan pelaporan pajak jadi sulit di kemudian hari. GFF Farma mencatat stok obat dan riwayat transaksi dalam satu database.

## Fitur

Semua pengguna (wajib login atau sign up):
- Sign up dengan username, nama lengkap, nomor telepon, dan password. Password di-hash dengan `password_hash`.
- Melihat daftar obat, mencari, dan memfilter berdasarkan jenis (Kapsul, Tablet, Suntik, Cair) atau kategori (Flu & Batuk, Nyeri & Demam, Alergi, Jantung, dan lainnya).
- Membeli obat. Stok divalidasi dan transaksi tercatat.

Khusus admin:
- Menambah, mengedit, dan menghapus obat (kode, nama, harga, stok, jenis, perlu atau tidak perlu resep, gambar, kandungan, kategori).
- Melihat, mengedit, dan menghapus transaksi. Menghapus transaksi mengembalikan stok.
- Melihat, mengedit, dan menghapus user, termasuk mengubah peran user.
- Mencari data obat, transaksi, dan user.

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP (`mysqli`) |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript, Font Awesome |
| Server lokal | XAMPP (Apache + MySQL) dan phpMyAdmin |

## Rancangan database

Tiga tabel yang saling berelasi, dinormalisasi sampai 3NF (tahapan UNF, 1NF, 2NF, hingga 3NF dikerjakan di data Excel).

```
users (1) ────< transactions >──── (1) obat
```

| Tabel | Kolom |
|---|---|
| `users` | `username` (PK), `fullname`, `phone`, `password`, `is_admin`, `created_at` |
| `obat` | `kode_obat` (PK), `nama_obat`, `stok`, `harga`, `jenis`, `keterangan`, `gambar`, `kandungan`, `kategori` |
| `transactions` | `id` (PK, auto increment), `username` (FK ke `users`), `kode_obat` (FK ke `obat`), `tanggal_pembelian`, `jumlah` |

`db_connect.php` membuat tabel dan akun admin bawaan secara otomatis saat pertama kali dijalankan.

## Struktur proyek

```
.
├── index.html              # Demo statis untuk GitHub Pages (hanya jalan di browser)
├── .nojekyll
├── app/                    # Aplikasi lengkap PHP dan MySQL
│   ├── db_connect.php          # Koneksi DB, membuat tabel dan admin bawaan otomatis
│   ├── login.php / logout.php / signup.php
│   ├── index.php               # Halaman utama: tabel obat, transaksi, dan user
│   ├── add.php / insert.php    # Tambah obat
│   ├── edit.php / update.php   # Edit obat
│   ├── delete.php              # Hapus obat
│   ├── process_purchase.php    # Catat pembelian dan kurangi stok
│   ├── edit_transaction.php / delete_transaction.php
│   ├── edit_customer.php / delete_customer.php
│   ├── check_medicine_code.php / get_medicine.php   # Helper AJAX
│   ├── style.css
│   └── database.sql            # Opsional: skema dan contoh data obat (impor manual)
├── data/                   # Data Excel dan tahapan normalisasi (UNF sampai 3NF)
├── guide/                  # User guideline (PDF)
├── README.md / README.id.md
└── .gitignore
```

## Cara menjalankan

1. Pasang [XAMPP](https://www.apachefriends.org/) lalu jalankan Apache dan MySQL.
2. Salin isi folder `app/` ke `C:\xampp\htdocs\apotek` (atau `/opt/lampp/htdocs/apotek`). File di dalam `app/` langsung masuk ke `apotek`, tanpa folder `app` tambahan.
3. Buka phpMyAdmin (`http://localhost/phpmyadmin`) dan buat database kosong bernama `gff_farma`.
4. Jika kredensial MySQL berbeda dari bawaan (`root`, tanpa password), ubah `app/db_connect.php`.
5. Buka `http://localhost/apotek/login.php`. Tabel terbentuk otomatis.
6. Login dengan akun admin bawaan yang ada di `app/db_connect.php`, atau daftar sebagai pelanggan.

Untuk memuat 12 contoh obat tanpa input manual, impor `app/database.sql` lewat tab Import di phpMyAdmin.

## Catatan teknis

- Susunan folder. Semua file `.php` dan `style.css` di dalam `app/` harus berada di folder yang sama. Antar halaman saling memanggil lewat path relatif (misalnya `include 'db_connect.php'`), jadi jangan dipindah ke subfolder dari `app/`. GitHub Pages hanya menampilkan demo statis `index.html`. Ia tidak bisa menjalankan PHP atau MySQL, jadi aplikasi lengkap tetap membutuhkan XAMPP.
- Gambar. Gambar obat tidak diupload ke server. Di form tambah dan edit, admin menempelkan URL gambar (`URL Gambar`) yang disimpan di kolom `gambar`. Jika kosong atau link-nya rusak, ditampilkan placeholder abu-abu. Beberapa link gambar contoh berasal dari situs pihak ketiga dan bisa kedaluwarsa, jadi ganti dengan URL milikmu bila perlu. Kartu founder memakai foto di `app/img/` (`founder-febri.jpg`, `founder-fadhli.jpg`, `founder-grego.jpg`); ganti file-file itu untuk mengubahnya.
- Koneksi internet. Ikon Font Awesome dimuat dari CDN dan gambar obat berupa URL eksternal, jadi ikon dan gambar tidak muncul saat offline.
- Data contoh. Database dimulai kosong, hanya berisi admin bawaan. Impor `app/database.sql` untuk 12 contoh obat (lengkap dengan URL gambar) atau tambahkan lewat halaman admin. Pelanggan dibuat lewat sign up.
- Setup otomatis. `db_connect.php` menjalankan `CREATE TABLE IF NOT EXISTS` di setiap request, jadi mengimpor file `.sql` tidak wajib. Database `gff_farma` sendiri harus dibuat lebih dulu.
- Kebutuhan. PHP dengan `mysqli` (mysqlnd) dan `mbstring`, serta MySQL atau MariaDB. Semuanya sudah ada di XAMPP. Diuji pada PHP 8.3 dengan MariaDB 10.11.
- Session dan peran. Saat login, data user disimpan di `$_SESSION['user']`, dan halaman khusus admin memeriksa `is_admin`. `process_purchase.php` mengurangi stok saat pelanggan membeli, dan menghapus transaksi mengembalikannya.
- Pemecahan masalah. `Koneksi gagal` berarti MySQL belum jalan atau kredensial di `db_connect.php` salah. Halaman kosong berarti error PHP disembunyikan, jadi aktifkan tampilan error atau cek `error.log` Apache. "Unknown database" berarti `gff_farma` belum dibuat.

## Keamanan

Sudah diterapkan:
- Semua query SQL memakai prepared statement dan output di-escape, sehingga aman dari SQL injection dan XSS.
- Password di-hash dengan `password_hash`. Session ID dibuat ulang saat login dan sign up, dan hash password tidak disimpan di session.
- Semua form, permintaan pembelian, dan link hapus dilindungi token CSRF.
- Input divalidasi di server (angka, nilai yang diperbolehkan, URL gambar http(s), aturan username, telepon, dan password). Login hanya menampilkan satu pesan error, sehingga username tidak bisa ditebak.
- Pembelian dan edit yang mengubah stok berjalan dalam transaksi database dengan row lock, sehingga stok tidak bisa minus atau terjual dua kali.
- Halaman khusus admin dan helper AJAX memeriksa peran di server. Akun admin utama dan akun yang sedang dipakai tidak bisa dihapus.

Tetap menjadi tanggung jawabmu bila di-deploy ke publik:
- Ganti password admin bawaan di `app/db_connect.php` dan beri password pada MySQL, jangan pakai `root` tanpa password.
- Gunakan HTTPS dan tambahkan pembatasan percobaan login.
- Nomor pembayaran di `app/index.php` dan kontak di footer masih placeholder.
