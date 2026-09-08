<?php
// =========================================
// KONEKSI DATABASE
// Sesuaikan host, user, password, nama database
// =========================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "absensi_qr";

$koneksi = new mysqli($host, $user, $pass, $dbname);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

$koneksi->set_charset("utf8mb4");
