<?php
session_start();
include 'db_connect.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit();
}
if (!csrf_valid($_POST['csrf'] ?? null)) {
    http_response_code(403);
    die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
}

// Kode obat diambil dari field tersembunyi (primary key tidak boleh diubah)
$kode_obat  = (string)($_POST['id_obat'] ?? '');
$nama_obat  = trim((string)($_POST['nama_obat'] ?? ''));
$kandungan  = trim((string)($_POST['kandungan'] ?? ''));
$jenis      = (string)($_POST['jenis'] ?? '');
$keterangan = (string)($_POST['keterangan'] ?? '');
$kategori   = (string)($_POST['kategori'] ?? '');
$stok       = parse_uint($_POST['stock'] ?? null);
$harga      = parse_uint($_POST['harga'] ?? null);
$gambar     = parse_image_url($_POST['gambar'] ?? '');

$errors = [];
if ($nama_obat === '' || mb_strlen($nama_obat) > 100) $errors[] = "Nama obat wajib diisi (maks. 100 karakter)";
if ($kandungan === '') $errors[] = "Kandungan wajib diisi";
if ($stok === null) $errors[] = "Stok harus berupa bilangan bulat >= 0";
if ($harga === null) $errors[] = "Harga harus berupa bilangan bulat >= 0";
if (!in_array($jenis, JENIS_OPTIONS, true)) $errors[] = "Jenis tidak valid";
if (!in_array($keterangan, KETERANGAN_OPTIONS, true)) $errors[] = "Keterangan tidak valid";
if (!in_array($kategori, KATEGORI_OPTIONS, true)) $errors[] = "Kategori tidak valid";
if ($gambar === null) $errors[] = "URL gambar harus diawali http:// atau https:// (maks. 255 karakter)";

if (!empty($errors)) {
    die(implode("<br>", array_map('h', $errors)) . '<br><a href="edit.php?id=' . h(urlencode($kode_obat)) . '">Kembali</a>');
}

try {
    db_exec($kon, "UPDATE obat SET nama_obat = ?, stok = ?, harga = ?, jenis = ?, keterangan = ?,
                   gambar = ?, kandungan = ?, kategori = ? WHERE kode_obat = ?",
            'siissssss', [$nama_obat, $stok, $harga, $jenis, $keterangan, $gambar, $kandungan, $kategori, $kode_obat]);
    header("Location: index.php?status=success");
    exit();
} catch (mysqli_sql_exception $e) {
    error_log('Update obat failed: ' . $e->getMessage());
    die("Gagal memperbarui data obat.");
}
