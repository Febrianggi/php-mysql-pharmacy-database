# GFF Farma — Website Database Apotek

**🌐 Bahasa:** [🇬🇧 English](README.md) · 🇮🇩 **Bahasa Indonesia**

> 📝 **Catatan:** dokumen proyek (laporan, slide presentasi, dan user guideline) serta antarmuka aplikasi ditulis dalam **Bahasa Indonesia**. README ini tersedia dalam bahasa Inggris dan Indonesia.

> *Ad Veritatem per Caritatem*

**GFF Farma** adalah aplikasi web berbasis PHP + MySQL untuk mengelola stok obat, akun pelanggan, dan transaksi pembelian pada apotek skala kecil. Proyek ini dibuat sebagai Project Akhir mata kuliah **Basis Data**, 2025.

## Latar Belakang

Apotek skala UMKM memiliki kewajiban pelaporan pajak yang sama dengan perusahaan besar, tetapi sering hanya mengandalkan buku tulis atau bahkan tidak mencatat sama sekali. Akibatnya, akuntansi dan pelaporan pajak menjadi sulit. GFF Farma membantu apotek mencatat stok obat dan riwayat transaksi secara sistematis dalam satu database.

## Fitur

**Semua pengguna (wajib login / sign up)**
- Sign up dengan username, nama lengkap, nomor telepon, dan password (password di-hash dengan `password_hash`)
- Melihat daftar obat, mencari, dan memfilter berdasarkan jenis (Kapsul, Tablet, Suntik, Cair) atau kategori (Flu & Batuk, Nyeri & Demam, Alergi, Jantung, …)
- Membeli obat — stok divalidasi dan transaksi tercatat

**Khusus admin**
- Menambah, mengedit, dan menghapus obat (kode, nama, harga, stok, jenis, perlu/tidak perlu resep, gambar, kandungan, kategori)
- Melihat, mengedit, dan menghapus transaksi (menghapus transaksi mengembalikan stok)
- Melihat, mengedit, dan menghapus user, termasuk mengubah peran user
- Pencarian cepat pada data obat, transaksi, dan user

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP (`mysqli`) |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript, Font Awesome |
| Server lokal | XAMPP (Apache + MySQL) dan phpMyAdmin |

## Rancangan Database

Tiga tabel yang saling berelasi, dinormalisasi hingga **3NF** (UNF → 1NF → 2NF → 3NF; lembar kerja lengkap ada di [`docs/`](docs/)).

```
users (1) ────< transactions >──── (1) obat
```

| Tabel | Kolom |
|---|---|
| `users` | `username` (PK), `fullname`, `phone`, `password`, `is_admin`, `created_at` |
| `obat` | `kode_obat` (PK), `nama_obat`, `stok`, `harga`, `jenis`, `keterangan`, `gambar`, `kandungan`, `kategori` |
| `transactions` | `id` (PK, auto increment), `username` (FK → users), `kode_obat` (FK → obat), `tanggal_pembelian`, `jumlah` |

Tabel dan akun admin bawaan dibuat otomatis oleh `db_connect.php` saat pertama kali dijalankan.

## Struktur Proyek

```
.
├── db_connect.php          # Koneksi DB, membuat tabel & admin bawaan otomatis
├── login.php / logout.php / signup.php
├── index.php               # Halaman utama: tabel obat, transaksi, dan user
├── add.php / insert.php    # Tambah obat
├── edit.php / update.php   # Edit obat
├── delete.php              # Hapus obat
├── process_purchase.php    # Catat pembelian dan kurangi stok
├── edit_transaction.php / delete_transaction.php
├── edit_customer.php / delete_customer.php
├── check_medicine_code.php / get_medicine.php   # Helper AJAX
├── style.css
├── database.sql            # Opsional: skema + contoh data obat (impor manual)
├── .gitignore
└── docs/                   # Laporan, presentasi, user guideline, data normalisasi
```

## Cara Menjalankan

1. Pasang [XAMPP](https://www.apachefriends.org/) lalu jalankan **Apache** dan **MySQL**.
2. Salin folder ini ke `C:\xampp\htdocs\apotek` (atau `/opt/lampp/htdocs/apotek`).
3. Buka phpMyAdmin (`http://localhost/phpmyadmin`) dan buat database kosong bernama **`gff_farma`**.
4. Jika kredensial MySQL berbeda dari bawaan (`root`, tanpa password), ubah `db_connect.php`.
5. Buka `http://localhost/apotek/login.php`. Tabel akan terbentuk otomatis.
6. Login dengan akun admin bawaan yang ada di `db_connect.php`, atau daftar sebagai pelanggan.

> Opsional: impor `database.sql` lewat phpMyAdmin (tab *Import*) untuk memuat 12 contoh obat tanpa input manual.

## Catatan Teknis

- **Struktur datar.** Semua file `.php` dan `style.css` harus berada di **satu folder yang sama** — antar halaman saling memanggil lewat path relatif (misalnya `include 'db_connect.php'`), jadi jangan dipindah ke subfolder.
- **Gambar.** Gambar obat **tidak diupload ke server**. Pada form tambah/edit, admin menempelkan **URL gambar** (`URL Gambar`) yang disimpan di kolom `gambar`. Jika dikosongkan (atau link-nya rusak), ditampilkan gambar placeholder abu-abu. Beberapa link gambar contoh berasal dari situs pihak ketiga dan bisa kedaluwarsa; ganti dengan URL milikmu bila perlu. Tidak perlu folder gambar. Kartu founder memakai placeholder bawaan; untuk memakai foto sendiri, taruh file di folder yang sama dengan `index.php` lalu ubah `src="fotomu.jpg"`.
- **Perlu koneksi internet.** Ikon Font Awesome dimuat dari CDN dan gambar obat berupa URL eksternal, sehingga ikon dan gambar tidak muncul saat offline.
- **Data contoh.** Database dimulai kosong (selain admin bawaan). Impor `database.sql` untuk 12 contoh obat (lengkap dengan URL gambar), atau tambahkan lewat halaman admin; pelanggan dibuat lewat sign up.
- **Setup otomatis.** `db_connect.php` menjalankan `CREATE TABLE IF NOT EXISTS` di setiap request, jadi tidak perlu impor file `.sql`. Database `gff_farma` sendiri harus dibuat lebih dulu.
- **Kebutuhan.** PHP dengan `mysqli` (mysqlnd) dan `mbstring` (semua sudah ada di XAMPP) serta MySQL / MariaDB. Diuji pada PHP 8.3 dengan MariaDB 10.11.
- **Session & peran.** Saat login, data user disimpan di `$_SESSION['user']`; halaman khusus admin memeriksa `is_admin`. Stok berkurang di `process_purchase.php` saat pelanggan membeli, dan dikembalikan saat admin menghapus transaksi.
- **Pemecahan masalah.** `Koneksi gagal` = MySQL belum jalan atau kredensial di `db_connect.php` salah; halaman kosong = aktifkan tampilan error PHP atau cek `error.log` Apache; "Unknown database" = buat dulu database `gff_farma`.

## Keamanan

Sudah diterapkan:
- Semua query SQL memakai **prepared statement** (aman dari SQL injection); output di-escape (aman dari XSS).
- Password di-hash dengan `password_hash`; session ID dibuat ulang saat login/sign up; hash password tidak disimpan di session.
- **Proteksi CSRF** pada semua form, permintaan pembelian, dan link hapus.
- Validasi input di sisi server (angka, nilai yang diperbolehkan, URL gambar http(s), aturan username/telepon/password) dan satu pesan error login yang seragam (username tidak bisa ditebak).
- Pembelian dan edit yang mengubah stok berjalan dalam **transaksi database** dengan row lock, sehingga stok tidak bisa minus atau terjual dua kali.
- Halaman khusus admin dan helper AJAX memeriksa peran di server. Akun admin utama dan akun yang sedang dipakai tidak bisa dihapus.

Tetap menjadi tanggung jawabmu bila di-deploy ke publik:
- Ganti password admin bawaan di `db_connect.php` dan beri password pada MySQL (jangan pakai `root` tanpa password).
- Gunakan HTTPS dan tambahkan pembatasan percobaan login.
- Nomor pembayaran di `index.php` dan kontak di footer masih placeholder.

## Dokumentasi

Lihat folder [`docs/`](docs/): laporan proyek, presentasi, user guideline, dan lembar normalisasi (UNF → 3NF).

> 🇮🇩 Seluruh penjelasan di slide, laporan, dan user guideline ditulis dalam **Bahasa Indonesia**.
