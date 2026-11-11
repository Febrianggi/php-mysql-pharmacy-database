<?php 
session_start();
include 'db_connect.php';

// Bukan admin
require_admin();

// Tangani error dari insert.php
$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'kode_exists') {
        $error = "Kode obat ".htmlspecialchars($_GET['kode'])." sudah ada dalam database!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Obat Baru - GFF Farma</title>
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
    .error-message {
      color: #f44336;
      padding: 10px;
      margin-bottom: 15px;
      background-color: #ffebee;
      border-radius: 4px;
      display: <?= $error ? 'block' : 'none' ?>;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><i class="fas fa-plus-circle"></i> Tambah Obat Baru</h1>
    
    <?php if ($error): ?>
      <div class="error-message" id="errorMessage">
        <i class="fas fa-exclamation-circle"></i> <?= h($error) ?>
      </div>
    <?php endif; ?>
    
    <form action="insert.php" method="post" onsubmit="return validateForm()">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="kode_obat">Kode Obat:</label>
        <input type="text" id="kode_obat" name="kode_obat" required>
      </div>
      
      <div class="form-group">
        <label for="nama_obat">Nama Obat:</label>
        <input type="text" id="nama_obat" name="nama_obat" required>
      </div>
      
      <div class="form-group">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" required>
      </div>
      
      <div class="form-group">
        <label for="harga">Harga:</label>
        <input type="number" id="harga" name="harga" required>
      </div>
      
      <div class="form-group">
        <label for="jenis">Jenis:</label>
        <select id="jenis" name="jenis" required>
          <option value="">Pilih Jenis</option>
          <option value="Tablet">Tablet</option>
          <option value="Kapsul">Kapsul</option>
          <option value="Suntik">Suntik</option>
          <option value="Cair">Cair</option>
          <option value="Salep">Salep</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="keterangan">Keterangan:</label>
        <select id="keterangan" name="keterangan" required>
          <option value="">Pilih Keterangan</option>
          <option value="Perlu resep">Perlu resep</option>
          <option value="Tidak perlu resep">Tidak perlu resep</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="gambar">URL Gambar:</label>
        <input type="text" id="gambar" name="gambar" placeholder="https://example.com/image.jpg">
      </div>
      
      <div class="form-group">
        <label for="kandungan">Kandungan:</label>
        <textarea id="kandungan" name="kandungan" required></textarea>
      </div>
      
      <div class="form-group">
        <label for="kategori">Kategori:</label>
        <select id="kategori" name="kategori" required>
          <option value="">Pilih Kategori</option>
          <option value="Flu & Batuk">Flu & Batuk</option>
          <option value="Nyeri & Demam">Nyeri & Demam</option>
          <option value="Alergi">Alergi</option>
          <option value="Jantung">Jantung</option>
          <option value="Saraf">Saraf</option>
          <option value="Pernapasan">Pernapasan</option>
          <option value="Antibiotik">Antibiotik</option>
          <option value="Luka luar">Luka luar</option>
        </select>
      </div>
      
      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
      </div>
    </form>
    
    <script>
    // Fungsi validasi form
    function validateForm() {
      const requiredFields = ['kode_obat', 'nama_obat', 'stock', 'jenis', 'kandungan', 'kategori', 'harga'];
      let isValid = true;
  
      requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (!element.value.trim()) {
          element.style.borderColor = 'red';
          isValid = false;
        } else {
          element.style.borderColor = '#ddd';
        }
      });
  
      if (!isValid) {
        alert('Harap isi semua kolom yang wajib diisi!');
        return false;
      }
  
      // Cek lagi kode obat sebelum submit untuk memastikan
      const kodeObat = document.getElementById('kode_obat').value;
      return checkMedicineCodeBeforeSubmit(kodeObat);
    }
    
    // Fungsi cek kode obat
    function checkMedicineCode() {
        const kodeObat = document.getElementById('kode_obat').value;
        const errorMessage = document.getElementById('errorMessage');
        
        if (!kodeObat) return;
        
        fetch(`check_medicine_code.php?kode_obat=${encodeURIComponent(kodeObat)}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    if (!errorMessage) {
                        // Buat elemen error jika belum ada
                        const container = document.querySelector('.container');
                        const newError = document.createElement('div');
                        newError.id = 'errorMessage';
                        newError.className = 'error-message';
                        newError.innerHTML = `<i class="fas fa-exclamation-circle"></i> Kode obat ${kodeObat} sudah ada dalam database!`;
                        container.insertBefore(newError, container.firstChild);
                    } else {
                        errorMessage.style.display = 'block';
                        errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> Kode obat ${kodeObat} sudah ada dalam database!`;
                    }
                    document.getElementById('kode_obat').style.borderColor = 'red';
                } else {
                    if (errorMessage) {
                        errorMessage.style.display = 'none';
                    }
                    document.getElementById('kode_obat').style.borderColor = '#ddd';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    // Fungsi cek kode obat sebelum submit (sync)
    function checkMedicineCodeBeforeSubmit(kodeObat) {
        if (!kodeObat) return true;
        
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `check_medicine_code.php?kode_obat=${encodeURIComponent(kodeObat)}`, false);
        xhr.send();
        
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.exists) {
                alert(`Kode obat ${kodeObat} sudah ada dalam database!`);
                document.getElementById('kode_obat').style.borderColor = 'red';
                return false;
            }
            return true;
        }
        return true;
    }
    
    // Event listener untuk cek kode obat
    document.getElementById('kode_obat').addEventListener('change', checkMedicineCode);
    document.getElementById('kode_obat').addEventListener('input', function() {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
        this.style.borderColor = '#ddd';
    });
    </script>
  </div>
</body>
</html>