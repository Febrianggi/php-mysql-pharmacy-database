<?php
session_start();
include 'db_connect.php';

require_admin();

if (!csrf_valid($_GET['token'] ?? null)) {
    http_response_code(403);
    die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
}

$kode_obat = (string)($_GET['id'] ?? '');

try {
    $kon->begin_transaction();
    // Menghapus transaksi yang terkait dengan obat ini
    db_exec($kon, "DELETE FROM transactions WHERE kode_obat = ?", 's', [$kode_obat]);
    // Hapus obat
    db_exec($kon, "DELETE FROM obat WHERE kode_obat = ?", 's', [$kode_obat]);
    $kon->commit();
    header("Location: index.php?status=deleted");
    exit();
} catch (mysqli_sql_exception $e) {
    $kon->rollback();
    error_log('Delete obat failed: ' . $e->getMessage());
    die("Gagal menghapus data obat.");
}
