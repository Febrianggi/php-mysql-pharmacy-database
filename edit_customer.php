<?php
session_start();
include 'db_connect.php';

require_admin();

$username = (string)($_GET['id'] ?? '');
$row = db_row($kon, "SELECT username, fullname, phone, is_admin FROM users WHERE username = ?", 's', [$username]);

if (!$row) {
    header("Location: index.php?table=customer");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        http_response_code(403);
        die("Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.");
    }

    $fullname = trim((string)($_POST['fullname'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    // Admin utama selalu tetap admin
    $is_admin = ($username === 'admingff' || isset($_POST['is_admin'])) ? 1 : 0;

    if ($fullname === '' || mb_strlen($fullname) > 100 || !preg_match('/^[0-9+\- ]{6,20}$/', $phone)) {
        die("Nama lengkap atau nomor telepon tidak valid. <a href=\"edit_customer.php?id=" . h(urlencode($username)) . "\">Kembali</a>");
    }

    try {
        db_exec($kon, "UPDATE users SET fullname = ?, phone = ?, is_admin = ? WHERE username = ?",
                'ssis', [$fullname, $phone, $is_admin, $username]);
        header("Location: index.php?table=customer&status=success");
        exit();
    } catch (mysqli_sql_exception $e) {
        error_log('Update user failed: ' . $e->getMessage());
        die("Gagal memperbarui user.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pelanggan - GFF Farma</title>
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
    .checkbox-label {
      display: flex;
      align-items: center;
    }
    .checkbox-label input {
      width: auto;
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><i class="fas fa-edit"></i> Edit Pelanggan</h1>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($row['username']) ?>" readonly>
      </div>
      <div class="form-group">
        <label for="fullname">Nama Lengkap</label>
        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($row['fullname']) ?>" required>
      </div>
      <div class="form-group">
        <label for="phone">Nomor Telepon</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" required>
      </div>
      <?php if ($row['username'] != 'admingff'): ?>
      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" id="is_admin" name="is_admin" <?= $row['is_admin'] ? 'checked' : '' ?>>
          Hak Admin
        </label>
      </div>
      <?php endif; ?>
      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="index.php?table=customer" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
      </div>
    </form>
  </div>
</body>
</html>