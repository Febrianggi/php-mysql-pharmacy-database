<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'gff_farma';

// Membuat koneksi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $kon = mysqli_connect($host, $username, $password, $database);
    mysqli_set_charset($kon, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die("Koneksi gagal. Pastikan MySQL berjalan dan database '$database' sudah dibuat.");
}

$createObatTableQuery = "CREATE TABLE IF NOT EXISTS obat (  
    kode_obat VARCHAR(50) PRIMARY KEY,
    nama_obat VARCHAR(100) NOT NULL,
    stok INT NOT NULL,
    harga INT NOT NULL,
    jenis VARCHAR(50) NOT NULL,
    keterangan TEXT,
    gambar VARCHAR(255),
    kandungan TEXT NOT NULL,
    kategori VARCHAR(50) NOT NULL
)";

$createTableQuery = "CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(50) PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$createTransactionsTableQuery = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    kode_obat VARCHAR(50) NOT NULL,
    tanggal_pembelian DATETIME DEFAULT CURRENT_TIMESTAMP,
    jumlah INT NOT NULL,
    FOREIGN KEY (username) REFERENCES users(username),
    FOREIGN KEY (kode_obat) REFERENCES obat(kode_obat)
)";

mysqli_query($kon, $createTableQuery);
mysqli_query($kon, $createObatTableQuery);
mysqli_query($kon, $createTransactionsTableQuery);

$adminCheck = mysqli_query($kon, "SELECT 1 FROM users WHERE username = 'admingff'");
if (mysqli_num_rows($adminCheck) == 0) {
    $hashedPassword = password_hash('ambalabu', PASSWORD_DEFAULT);
    $stmt = $kon->prepare("INSERT INTO users (username, fullname, phone, password, is_admin) VALUES ('admingff', 'Admin GFF', '1234567890', ?, TRUE)");
    $stmt->bind_param('s', $hashedPassword);
    $stmt->execute();
}

// ---------------------------------------------------------------
// Helper functions
// ---------------------------------------------------------------

// Nilai yang diperbolehkan untuk kolom pilihan
const JENIS_OPTIONS      = ['Tablet', 'Kapsul', 'Suntik', 'Cair', 'Salep'];
const KETERANGAN_OPTIONS = ['Perlu resep', 'Tidak perlu resep'];
const KATEGORI_OPTIONS   = ['Flu & Batuk', 'Nyeri & Demam', 'Alergi', 'Jantung', 'Saraf', 'Pernapasan', 'Antibiotik', 'Luka luar'];

// Gambar pengganti bila URL gambar kosong / gagal dimuat (tidak disimpan di database)
const PLACEHOLDER_IMG = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23e0e0e0'/%3E%3Cpath d='M30 62l14-18 10 12 8-9 18 23H30z' fill='%239e9e9e'/%3E%3Ccircle cx='38' cy='34' r='7' fill='%239e9e9e'/%3E%3C/svg%3E";

// Escape output HTML
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Menjalankan prepared statement
function db_exec(mysqli $kon, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = $kon->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

// Mengambil semua baris hasil SELECT
function db_all(mysqli $kon, string $sql, string $types = '', array $params = []): array
{
    return db_exec($kon, $sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Mengambil satu baris (atau null)
function db_row(mysqli $kon, string $sql, string $types = '', array $params = []): ?array
{
    return db_all($kon, $sql, $types, $params)[0] ?? null;
}

// CSRF token (disimpan di session)
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid($token): bool
{
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

// Hanya admin yang boleh mengakses halaman ini
function require_admin(): void
{
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
        header("Location: login.php");
        exit();
    }
}

// Validasi bilangan bulat >= 0, mengembalikan null bila tidak valid
function parse_uint($value): ?int
{
    $v = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
    return $v === false ? null : $v;
}

// Validasi URL gambar: kosong diperbolehkan, selain itu harus http(s) dan maks 255 karakter
function parse_image_url($value): ?string
{
    $v = trim((string)$value);
    if ($v === '') {
        return '';
    }
    if (strlen($v) > 255 || !filter_var($v, FILTER_VALIDATE_URL)) {
        return null;
    }
    $scheme = strtolower((string)parse_url($v, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $v : null;
}
?>