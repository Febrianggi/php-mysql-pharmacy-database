<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['kode_obat'])) {
    $row = db_row($kon, "SELECT nama_obat, harga FROM obat WHERE kode_obat = ?", 's', [(string)$_GET['kode_obat']]);
    echo json_encode($row);   // null bila tidak ditemukan
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Kode obat tidak diberikan']);
}
