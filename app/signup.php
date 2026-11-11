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
    $fullname = trim((string)($_POST['fullname'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($username === '' || $fullname === '' || $phone === '' || $password === '') {
        $error = "All fields are required";
    } elseif (!preg_match('/^[A-Za-z0-9_. -]{3,50}$/', $username)) {
        $error = "Username must be 3-50 characters (letters, numbers, space, . _ -)";
    } elseif (mb_strlen($fullname) > 100) {
        $error = "Full name is too long (max 100 characters)";
    } elseif (!preg_match('/^[0-9+\- ]{6,20}$/', $phone)) {
        $error = "Phone number is not valid";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (db_row($kon, "SELECT 1 FROM users WHERE username = ?", 's', [$username])) {
        $error = "Username already exists";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            db_exec($kon, "INSERT INTO users (username, fullname, phone, password, is_admin) VALUES (?, ?, ?, ?, FALSE)",
                    'ssss', [$username, $fullname, $phone, $hashedPassword]);
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'username' => $username,
                'fullname' => $fullname,
                'phone' => $phone,
                'is_admin' => false
            ];
            header("Location: index.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            error_log('Signup failed: ' . $e->getMessage());
            $error = ($e->getCode() == 1062) ? "Username already exists" : "Registration failed, please try again";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - GFF Farma</title>
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
    .signup-container {
      background-color: white;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .signup-container h1 {
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
    .btn-signup {
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
    .btn-signup:hover {
      background-color: #3a4a34;
    }
    .error {
      color: #f44336;
      margin-bottom: 20px;
    }
    .login-link {
      margin-top: 20px;
      color: #555;
    }
    .login-link a {
      color: #4b5733;
      text-decoration: none;
      font-weight: bold;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
    .logo {
      width: 80px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <img src="https://web.mojokertokota.go.id/images/fasilitas/2ZN6llLIQDUKVNuMcgCm8f5AErD1MzhQiT11Usfk.png" alt="GFF Farma Logo" class="logo">
    <h1>Create an Account</h1>
    
    <?php if ($error): ?>
      <div class="error"><?= h($error) ?></div>
    <?php endif; ?>
    
    <form action="signup.php" method="post">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn-signup">Sign Up</button>
    </form>
    
    <div class="login-link">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>
</body>
</html>