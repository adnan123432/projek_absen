<?php
require "db.php";

$folder_qr = __DIR__ . "/qrcodes";
if (!is_dir($folder_qr)) {
    mkdir($folder_qr, 0755, true);
}

$result = $koneksi->query("SELECT id, nis, nama FROM siswa WHERE qr_path IS NULL OR qr_path = ''");

if ($result->num_rows === 0) {
    echo "Semua siswa sudah punya QR code.\n";
    exit;
}

while ($siswa = $result->fetch_assoc()) {
    $nis = $siswa['nis'];
    $nama_file = preg_replace('/[^A-Za-z0-9_-]/', '_', $nis) . ".png";
    $path_file = $folder_qr . "/" . $nama_file;

    $url_qr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($nis);
    $gambar = file_get_contents($url_qr);

    if ($gambar === false) {
        echo "Gagal generate QR untuk {$siswa['nama']}\n";
        continue;
    }

    file_put_contents($path_file, $gambar);

    $path_relatif = "qrcodes/" . $nama_file;
    $stmt = $koneksi->prepare("UPDATE siswa SET qr_path = ? WHERE id = ?");
    $stmt->bind_param("si", $path_relatif, $siswa['id']);
    $stmt->execute();

    echo "QR untuk {$siswa['nama']} ({$nis}) berhasil dibuat: {$path_relatif}\n";
}

echo "\nSelesai.\n";