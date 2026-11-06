-- GFF Farma — database schema + sample medicines
-- Optional: db_connect.php already creates the tables automatically.
-- Use this file if you prefer to import manually (phpMyAdmin > Import) or want the sample data.
-- The default admin account is created by db_connect.php on the first page load.

CREATE DATABASE IF NOT EXISTS gff_farma CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gff_farma;

CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(50) PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS obat (
    kode_obat VARCHAR(50) PRIMARY KEY,
    nama_obat VARCHAR(100) NOT NULL,
    stok INT NOT NULL,
    harga INT NOT NULL,
    jenis VARCHAR(50) NOT NULL,
    keterangan TEXT,
    gambar VARCHAR(255),
    kandungan TEXT NOT NULL,
    kategori VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    kode_obat VARCHAR(50) NOT NULL,
    tanggal_pembelian DATETIME DEFAULT CURRENT_TIMESTAMP,
    jumlah INT NOT NULL,
    FOREIGN KEY (username) REFERENCES users(username),
    FOREIGN KEY (kode_obat) REFERENCES obat(kode_obat)
);

-- Sample medicines (kode_obat, nama_obat, stok, harga, jenis, keterangan, gambar, kandungan, kategori)
INSERT IGNORE INTO obat (kode_obat, nama_obat, stok, harga, jenis, keterangan, gambar, kandungan, kategori) VALUES
  ('001', 'Intunal', 151, 5000, 'Tablet', 'Tidak perlu resep', 'https://d3bbrrd0qs69m4.cloudfront.net/images/product/apotek_online_k24klik_20201222093945359225_INTUNAL-KAP.jpg', 'Paracetamol, Phenylpropanolamine, Guaifenesin', 'Flu & Batuk'),
  ('002', 'Betadine 5ml', 49, 8000, 'Cair', 'Tidak perlu resep', 'https://encrypted-tbn2.gstatic.com/shopping?q=tbn:ANd9GcRrD3Q34A5cRZPy_5tMR60X_aeI8GjyOfjqBmxQMs8nUCeGPSCG-azu6ZxUk-Y3knTXfYjBJw4QPKvm5c1O2yt6qQ2mvrZEpwVUXEiVAiRg5Th9GlOluNkE', 'Povidone Iodine', 'Luka luar'),
  ('003', 'Bodrex', 35, 11500, 'Tablet', 'Tidak perlu resep', 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcTrbNl_3x8RMxL2E6AgEN2bERqUuZTwdxeoBeQSvQmL1B_OxY794DHEXrJbfJb07lMBnUEhe7SZissU9qBsMRstQomtlLuQIPkvFfbl4m7tHxMaWS4f3TSrqQ', 'Paracetamol, Caffein', 'Nyeri & Demam'),
  ('004', 'Amlodipine 5mg', 11, 5500, 'Tablet', 'Perlu resep', 'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/medium/catalog-image/100/MTA-181812628/amlodipine_amlodipine_full01.jpg', 'Diuretik Thiazide', 'Jantung'),
  ('005', 'Zyrtec', 5, 285000, 'Kapsul', 'Perlu resep', 'https://dev.spirit.com.kw/storage/product/1145/1737033413_31_0.webp', 'Cetirizine Dihydrochloride', 'Alergi'),
  ('006', 'Tempra Forte', 9, 50000, 'Cair', 'Perlu resep', 'https://encrypted-tbn0.gstatic.com/shopping?q=tbn:ANd9GcQ4KmTl3ImJMoizP4MfioQrap8pIE7vS9wCKujEJYIU_zDfTEfDjDnAKscuIXN5YNQkg-apNoARHzR-izh3_hQfqhriTBK3u7aOdewKOh3_0qaJN3LvURGm', 'Paracetamol', 'Nyeri & Demam'),
  ('007', 'Panadol', 37, 13000, 'Tablet', 'Tidak perlu resep', 'https://d2qjkwm11akmwu.cloudfront.net/products/695343_19-11-2024_13-42-19.png', 'Paracetamol', 'Nyeri & Demam'),
  ('008', 'Cetirizine', 17, 55500, 'Tablet', 'Perlu resep', 'https://d3bbrrd0qs69m4.cloudfront.net/images/product/apotek_online_k24klik_20230406105349359225_CETIRIZINE-KF.png', 'Hydrochloride', 'Alergi'),
  ('009', 'OBH Combi', 54, 16000, 'Cair', 'Tidak perlu resep', 'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full//catalog-image/104/MTA-96823535/obh_obat-batuk-obh-combi-reguler-100ml_full01.jpg', 'Succus Liquiritiae, Paracetamol, Ammonium Chloride', 'Flu & Batuk'),
  ('010', 'Promag', 43, 9000, 'Tablet', 'Tidak perlu resep', 'https://d3bbrrd0qs69m4.cloudfront.net/images/product/0101a0147.jpg', 'Magnesium Hidroksida, Hydrotalcite', 'Nyeri & Demam'),
  ('011', 'Ampicilin 500mg', 7, 12500, 'Tablet', 'Perlu resep', 'https://d3bbrrd0qs69m4.cloudfront.net/images/product/apotek_online_k24klik_201608180558471922_726-Ampicillin.jpg', 'Ampicillin', 'Antibiotik'),
  ('012', 'Clobazam', 0, 30000, 'Tablet', 'Perlu resep', 'https://kalbemed.com/storage/images/products/a3e4db7dec789c91465d604c62040956.jpg', 'Clobazam', 'Saraf');
