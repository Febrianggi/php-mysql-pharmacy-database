<?php
session_start();
include 'db_connect.php';

require_admin();

if (!csrf_valid($_GET['token'] ?? null)) {
    http_response_code(403);
    die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
}

$id = parse_uint($_GET['id'] ?? null);

try {
    $kon->begin_transaction();
    $transaction = $id === null ? null
        : db_row($kon, "SELECT kode_obat, jumlah FROM transactions WHERE id = ? FOR UPDATE", 'i', [$id]);

    if ($transaction) {
        // Kembalikan stok obat
        db_exec($kon, "UPDATE obat SET stok = stok + ? WHERE kode_obat = ?", 'is',
                [$transaction['jumlah'], $transaction['kode_obat']]);
        // Hapus transaksi
        db_exec($kon, "DELETE FROM transactions WHERE id = ?", 'i', [$id]);
        $kon->commit();
        header("Location: index.php?table=transaction&status=deleted");
    } else {
        $kon->rollback();
        header("Location: index.php?table=transaction");
    }
    exit();
} catch (mysqli_sql_exception $e) {
    $kon->rollback();
    error_log('Delete transaction failed: ' . $e->getMessage());
    die("Gagal menghapus transaksi.");
}
