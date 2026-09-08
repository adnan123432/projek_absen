<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas   = $_GET['kelas'] ?? '';

// Query Data Absensi
$sql = "SELECT a.tanggal, s.nis, s.nama, s.kelas, a.jam_masuk, a.jam_pulang, a.status, a.keterangan 
        FROM absensi a 
        JOIN siswa s ON s.id = a.siswa_id 
        WHERE a.tanggal = ?";
$params = [$tanggal];

if ($kelas !== '') {
    $sql .= " AND s.kelas = ?";
    $params[] = $kelas;
}

$sql .= " ORDER BY s.kelas, s.nama";

$st = $pdo->prepare($sql);
$st->execute($params);
// Output Header CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan-' . $tanggal . '.csv"');
// Tulis Stream CSV
$out = fopen('php://output', 'w');
// Header Kolom CSV
fputcsv($out, ['Tanggal', 'NIS', 'Nama', 'Kelas', 'Jam Masuk', 'Jam Pulang', 'Status', 'Keterangan']);
// Baris Data
while ($r = $st->fetch()) {
    fputcsv($out, $r);
}

fclose($out);
exit;