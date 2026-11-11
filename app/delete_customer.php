<?php
session_start();
include 'db_connect.php';

require_admin();

if (!csrf_valid($_GET['token'] ?? null)) {
    http_response_code(403);
    die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
}

$username = (string)($_GET['id'] ?? '');

// Admin utama dan akun yang sedang dipakai tidak boleh dihapus
if ($username === 'admingff' || $username === $_SESSION['user']['username']) {
    header("Location: index.php?table=customer");
    exit();
}

try {
    $kon->begin_transaction();
    // Kembalikan stok dari transaksi milik user ini, lalu hapus transaksinya (foreign key)
    db_exec($kon, "UPDATE obat o JOIN transactions t ON t.kode_obat = o.kode_obat
                   SET o.stok = o.stok + t.jumlah WHERE t.username = ?", 's', [$username]);
    db_exec($kon, "DELETE FROM transactions WHERE username = ?", 's', [$username]);
    db_exec($kon, "DELETE FROM users WHERE username = ?", 's', [$username]);
    $kon->commit();
    header("Location: index.php?table=customer&status=deleted");
    exit();
} catch (mysqli_sql_exception $e) {
    $kon->rollback();
    error_log('Delete user failed: ' . $e->getMessage());
    die("Gagal menghapus user.");
}
