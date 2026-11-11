<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
    exit();
}

if (!csrf_valid($_POST['csrf'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid, muat ulang halaman']);
    exit();
}

$username  = $_SESSION['user']['username'];
$kode_obat = (string)($_POST['kode_obat'] ?? '');
$jumlah    = (int)($_POST['jumlah'] ?? 0);

// Validasi jumlah
if ($jumlah < 1) {
    echo json_encode(['success' => false, 'message' => 'Jumlah tidak valid']);
    exit();
}

try {
    $kon->begin_transaction();

    // Kunci baris obat agar stok tidak bisa dibeli dua kali secara bersamaan
    $medicine = db_row($kon, "SELECT stok FROM obat WHERE kode_obat = ? FOR UPDATE", 's', [$kode_obat]);

    if (!$medicine) {
        $kon->rollback();
        echo json_encode(['success' => false, 'message' => 'Obat tidak ditemukan']);
    } elseif ($medicine['stok'] == 0) {
        $kon->rollback();
        echo json_encode(['success' => false, 'message' => 'Stok habis']);
    } elseif ($medicine['stok'] < $jumlah) {
        $kon->rollback();
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
    } else {
        db_exec($kon, "INSERT INTO transactions (username, kode_obat, jumlah) VALUES (?, ?, ?)",
                'ssi', [$username, $kode_obat, $jumlah]);
        db_exec($kon, "UPDATE obat SET stok = stok - ? WHERE kode_obat = ?", 'is', [$jumlah, $kode_obat]);
        $kon->commit();
        echo json_encode(['success' => true]);
    }
} catch (mysqli_sql_exception $e) {
    $kon->rollback();
    error_log('Purchase failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi']);
}
