<?php
// =========================================
// GENERATE QR CODE UNTUK SETIAP SISWA
// Jalankan lewat browser: http://localhost/absensi_qr/generate_qr.php
// atau lewat CLI: php generate_qr.php
//
// QR HANYA BERISI id_unik siswa, BUKAN data lengkap.
// Ini penting supaya data siswa tetap aman & mudah di-update
// tanpa harus cetak ulang QR.
// =========================================
require "db.php";

$folder_qr = __DIR__ . "/qrcodes";
if (!is_dir($folder_qr)) {
    mkdir($folder_qr, 0755, true);
}

// Ambil semua siswa yang belum punya file QR
$result = $koneksi->query("SELECT id, id_unik, nama FROM siswa WHERE qr_path IS NULL OR qr_path = ''");

if ($result->num_rows === 0) {
    echo "Semua siswa sudah punya QR code.\n";
    exit;
}

while ($siswa = $result->fetch_assoc()) {
    $id_unik = $siswa['id_unik'];
    $nama_file = preg_replace('/[^A-Za-z0-9_-]/', '_', $id_unik) . ".png";
    $path_file = $folder_qr . "/" . $nama_file;

    // Generate QR pakai API gratis (goqr.me).
    // Kalau nanti mau full offline, bisa ganti pakai library composer:
    // https://github.com/endroid/qr-code
    $url_qr = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($id_unik);

    $gambar = file_get_contents($url_qr);

    if ($gambar === false) {
        echo "Gagal generate QR untuk {$siswa['nama']}\n";
        continue;
    }

    file_put_contents($path_file, $gambar);

    // Simpan lokasi file ke database
    $path_relatif = "qrcodes/" . $nama_file;
    $stmt = $koneksi->prepare("UPDATE siswa SET qr_path = ? WHERE id = ?");
    $stmt->bind_param("si", $path_relatif, $siswa['id']);
    $stmt->execute();

    echo "QR untuk {$siswa['nama']} ({$id_unik}) berhasil dibuat: {$path_relatif}\n";
}

echo "\nSelesai. Silakan cetak QR dari folder /qrcodes untuk dibagikan ke siswa.\n";
