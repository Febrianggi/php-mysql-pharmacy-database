<?php
session_start();
include 'db_connect.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: add.php");
    exit();
}
if (!csrf_valid($_POST['csrf'] ?? null)) {
    http_response_code(403);
    die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
}

// Validasi input
$kode_obat  = trim((string)($_POST['kode_obat'] ?? ''));
$nama_obat  = trim((string)($_POST['nama_obat'] ?? ''));
$kandungan  = trim((string)($_POST['kandungan'] ?? ''));
$jenis      = (string)($_POST['jenis'] ?? '');
$keterangan = (string)($_POST['keterangan'] ?? '');
$kategori   = (string)($_POST['kategori'] ?? '');
$stok       = parse_uint($_POST['stock'] ?? null);
$harga      = parse_uint($_POST['harga'] ?? null);
$gambar     = parse_image_url($_POST['gambar'] ?? '');

$errors = [];
if ($kode_obat === '' || strlen($kode_obat) > 50) $errors[] = "Kode obat wajib diisi (maks. 50 karakter)";
if ($nama_obat === '' || mb_strlen($nama_obat) > 100) $errors[] = "Nama obat wajib diisi (maks. 100 karakter)";
if ($kandungan === '') $errors[] = "Kandungan wajib diisi";
if ($stok === null) $errors[] = "Stok harus berupa bilangan bulat >= 0";
if ($harga === null) $errors[] = "Harga harus berupa bilangan bulat >= 0";
if (!in_array($jenis, JENIS_OPTIONS, true)) $errors[] = "Jenis tidak valid";
if (!in_array($keterangan, KETERANGAN_OPTIONS, true)) $errors[] = "Keterangan tidak valid";
if (!in_array($kategori, KATEGORI_OPTIONS, true)) $errors[] = "Kategori tidak valid";
if ($gambar === null) $errors[] = "URL gambar harus diawali http:// atau https:// (maks. 255 karakter)";

if (!empty($errors)) {
    die(implode("<br>", array_map('h', $errors)) . '<br><a href="add.php">Kembali</a>');
}

// Cek apakah kode obat sudah ada
if (db_row($kon, "SELECT 1 FROM obat WHERE kode_obat = ?", 's', [$kode_obat])) {
    header("Location: add.php?error=kode_exists&kode=" . urlencode($kode_obat));
    exit();
}

try {
    db_exec($kon, "INSERT INTO obat (kode_obat, nama_obat, stok, jenis, keterangan, gambar, kandungan, kategori, harga)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'ssisssssi', [$kode_obat, $nama_obat, $stok, $jenis, $keterangan, $gambar, $kandungan, $kategori, $harga]);
    header("Location: index.php?status=success");
    exit();
} catch (mysqli_sql_exception $e) {
    error_log('Insert obat failed: ' . $e->getMessage());
    if ($e->getCode() == 1062) {
        header("Location: add.php?error=kode_exists&kode=" . urlencode($kode_obat));
        exit();
    }
    die("Gagal menyimpan data obat.");
}
