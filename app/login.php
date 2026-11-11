<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $user = db_row($kon, "SELECT * FROM users WHERE username = ?", 's', [$username]);

    // Pesan error sama untuk username salah / password salah (mencegah user enumeration)
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']); // jangan simpan hash password di session
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - GFF Farma</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .login-container {
      background-color: white;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .login-container h1 {
      color: #4b5733;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #555;
    }
    .form-group input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 16px;
    }
    .btn-login {
      width: 100%;
      padding: 12px;
      background-color: #4b5733;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s;
    }
    .btn-login:hover {
      background-color: #3a4a34;
    }
    .error {
      color: #f44336;
      margin-bottom: 20px;
    }
    .signup-link {
      margin-top: 20px;
      color: #555;
    }
    .signup-link a {
      color: #4b5733;
      text-decoration: none;
      font-weight: bold;
    }
    .signup-link a:hover {
      text-decoration: underline;
    }
    .logo {
      width: 80px;
      margin-bottom: 20px;
    }
    .success {
      color: #4CAF50;
      margin-bottom: 20px;
      text-align: center;
      padding: 10px;
      background-color: #e8f5e9;
      border-radius: 4px;
    }

  </style>
</head>
<body>
  <div class="login-container">
    <img src="https://web.mojokertokota.go.id/images/fasilitas/2ZN6llLIQDUKVNuMcgCm8f5AErD1MzhQiT11Usfk.png" alt="GFF Farma Logo" class="logo">
    <h1>Login ke GFF Farma</h1>
    
    <?php if ($error): ?>
      <div class="error"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'loggedout'): ?>
      <div class="success">Anda telah berhasil logout.</div>
    <?php endif; ?>
    
    <form action="login.php" method="post">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn-login">Login</button>
    </form>
    
    <div class="signup-link">
      Tidak punya akun? <a href="signup.php">Daftar</a>
    </div>
  </div>
</body>
</html>