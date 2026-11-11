<?php
session_start();
include 'db_connect.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        http_response_code(403);
        die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
    }

    $id = parse_uint($_POST['id'] ?? null);
    $jumlah_baru = (int)($_POST['jumlah'] ?? 0);

    // Melarang jumlah nol
    if ($id === null || $jumlah_baru < 1) {
        die("Jumlah tidak valid");
    }

    try {
        $kon->begin_transaction();

        // Dapatkan data transaksi lama (baris dikunci)
        $oldData = db_row($kon, "SELECT kode_obat, jumlah FROM transactions WHERE id = ? FOR UPDATE", 'i', [$id]);
        if (!$oldData) {
            $kon->rollback();
            die("Transaksi tidak ditemukan");
        }

        // Hitung selisih jumlah
        $selisih = $jumlah_baru - $oldData['jumlah'];

        // Periksa stok tersedia
        if ($selisih > 0) {
            $stockData = db_row($kon, "SELECT stok FROM obat WHERE kode_obat = ? FOR UPDATE", 's', [$oldData['kode_obat']]);
            if (!$stockData || $stockData['stok'] < $selisih) {
                $kon->rollback();
                die("Stok tidak mencukupi untuk penambahan jumlah ini");
            }
        }

        // Update stok obat dan transaksi
        db_exec($kon, "UPDATE obat SET stok = stok - ? WHERE kode_obat = ?", 'is', [$selisih, $oldData['kode_obat']]);
        db_exec($kon, "UPDATE transactions SET jumlah = ? WHERE id = ?", 'ii', [$jumlah_baru, $id]);
        $kon->commit();

        header("Location: index.php?table=transaction&status=updated");
        exit();
    } catch (mysqli_sql_exception $e) {
        $kon->rollback();
        error_log('Update transaction failed: ' . $e->getMessage());
        die("Gagal memperbarui transaksi.");
    }
} else {
    $id = parse_uint($_GET['id'] ?? null);
    $row = $id === null ? null : db_row($kon, "SELECT * FROM transactions WHERE id = ?", 'i', [$id]);

    if (!$row) {
        header("Location: index.php?table=transaction");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Transaksi - GFF Farma</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 800px;
      margin: 50px auto;
      padding: 20px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1 {
      color: #4b5733;
      text-align: center;
    }
    .form-group {
      margin-bottom: 15px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    input[type="text"],
    input[type="number"],
    textarea,
    select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }
    input[readonly] {
      background-color: #f5f5f5;
      cursor: not-allowed;
    }
    .btn-group {
      margin-top: 20px;
      text-align: center;
    }
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }
    .btn-primary {
      background-color: #4b5733;
      color: white;
    }
    .btn-secondary {
      background-color: #6c757d;
      color: white;
      margin-left: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><i class="fas fa-edit"></i> Edit Jumlah Transaksi</h1>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
      
      <div class="form-group">
        <label>ID Transaksi</label>
        <input type="text" value="<?= htmlspecialchars($row['id']) ?>" readonly>
      </div>
      
      <div class="form-group">
        <label for="jumlah">Jumlah</label>
        <input type="number" id="jumlah" name="jumlah" value="<?= htmlspecialchars($row['jumlah']) ?>" required min="1">
      </div>
      
      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="index.php?table=transaction" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
      </div>
    </form>
  </div>
</body>
</html>