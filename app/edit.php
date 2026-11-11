<?php
session_start();
include 'db_connect.php';

// Bukan admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header("Location: login.php");
    exit();
}

$kode_obat = (string)($_GET['id'] ?? '');
$row = db_row($kon, "SELECT * FROM obat WHERE kode_obat = ?", 's', [$kode_obat]);

if (!$row) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Obat - GFF Farma</title>
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
    textarea {
      height: 100px;
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
    <h1><i class="fas fa-edit"></i> Edit Obat</h1>
    <form action="update.php" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id_obat" value="<?= h($row['kode_obat']) ?>">
      
      <div class="form-group">
        <label for="kode_obat">Kode Obat:</label>
        <input type="text" id="kode_obat" value="<?= h($row['kode_obat']) ?>" readonly>
      </div>
      
      <div class="form-group">
        <label for="nama_obat">Nama Obat:</label>
        <input type="text" id="nama_obat" name="nama_obat" value="<?php echo htmlspecialchars($row['nama_obat']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" value="<?= h($row['stok']) ?>" min="0" required>
      </div>
      
      <div class="form-group">
        <label for="harga">Harga:</label>
        <input type="number" id="harga" name="harga" value="<?= h($row['harga']) ?>" min="0" required>
      </div>
      
      <div class="form-group">
        <label for="jenis">Jenis:</label>
        <select id="jenis" name="jenis" required>
          <option value="Tablet" <?php echo $row['jenis'] == 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
          <option value="Kapsul" <?php echo $row['jenis'] == 'Kapsul' ? 'selected' : ''; ?>>Kapsul</option>
          <option value="Suntik" <?php echo $row['jenis'] == 'Suntik' ? 'selected' : ''; ?>>Suntik</option>
          <option value="Cair" <?php echo $row['jenis'] == 'Cair' ? 'selected' : ''; ?>>Cair</option>
          <option value="Salep" <?php echo $row['jenis'] == 'Salep' ? 'selected' : ''; ?>>Salep</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="keterangan">Keterangan:</label>
        <select id="keterangan" name="keterangan" required>
          <option value="Perlu resep" <?php echo$row['keterangan'] == 'Perlu resep' ? 'selected' : ''; ?>>Perlu resep</option>
          <option value="Tidak perlu resep" <?php echo$row['keterangan'] == 'Tidak perlu resep' ? 'selected' : ''; ?>>Tidak perlu resep</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="gambar">URL Gambar:</label>
        <input type="text" id="gambar" name="gambar" value="<?php echo htmlspecialchars($row['gambar']); ?>">
      </div>
      
      <div class="form-group">
        <label for="kandungan">Kandungan:</label>
        <textarea id="kandungan" name="kandungan" required><?php echo htmlspecialchars($row['kandungan']); ?></textarea>
      </div>
      
      <div class="form-group">
        <label for="kategori">Kategori:</label>
        <select id="kategori" name="kategori" required>
          <option value="Flu & Batuk" <?php echo $row['kategori'] == 'Flu & Batuk' ? 'selected' : ''; ?>>Flu & Batuk</option>
          <option value="Nyeri & Demam" <?php echo $row['kategori'] == 'Nyeri & Demam' ? 'selected' : ''; ?>>Nyeri & Demam</option>
          <option value="Alergi" <?php echo $row['kategori'] == 'Alergi' ? 'selected' : ''; ?>>Alergi</option>
          <option value="Jantung" <?php echo $row['kategori'] == 'Jantung' ? 'selected' : ''; ?>>Jantung</option>
          <option value="Saraf" <?php echo $row['kategori'] == 'Saraf' ? 'selected' : ''; ?>>Saraf</option>
          <option value="Pernapasan" <?php echo $row['kategori'] == 'Pernapasan' ? 'selected' : ''; ?>>Pernapasan</option>
          <option value="Antibiotik" <?php echo $row['kategori'] == 'Antibiotik' ? 'selected' : ''; ?>>Antibiotik</option>
          <option value="Luka luar" <?php echo $row['kategori'] == 'Luka luar' ? 'selected' : ''; ?>>Luka luar</option>
        </select>
      </div>
      
      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
      </div>
    </form>
  </div>
</body>
</html>