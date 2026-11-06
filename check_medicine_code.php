<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['kode_obat'])) {
    $row = db_row($kon, "SELECT COUNT(*) AS count FROM obat WHERE kode_obat = ?", 's', [(string)$_GET['kode_obat']]);
    echo json_encode(['exists' => $row['count'] > 0]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Kode obat tidak diberikan']);
}
