<?php
// =========================================
// CRON JOB: TANDAI "TIDAK HADIR" OTOMATIS
//
// Jalankan script ini SETIAP HARI setelah jam_batas_absen terlewat,
// misal dijadwalkan jalan jam 09:15 tiap hari sekolah.
//
// Cara setting cron job di server (Linux), edit dengan: crontab -e
// lalu tambahkan baris (contoh jalan tiap jam 09:15):
// 15 9 * * 1-6 php /path/ke/project/cron_absen.php
//
// Kalau pakai hosting/cPanel, cari menu "Cron Jobs" dan masukkan
// perintah yang sama di sana.
// =========================================
require "db.php";

$tanggal_hari_ini = date("Y-m-d");
$nama_hari_en = strtolower(date("l")); // contoh: "monday"
$map_hari = [
    "sunday" => "minggu", "monday" => "senin", "tuesday" => "selasa",
    "wednesday" => "rabu", "thursday" => "kamis", "friday" => "jumat", "saturday" => "sabtu"
];
$nama_hari = $map_hari[$nama_hari_en] ?? $nama_hari_en;

// Ambil semua kelas yang ada jadwal hari ini
$stmt = $koneksi->prepare("SELECT DISTINCT kelas FROM jadwal WHERE hari = ?");
$stmt->bind_param("s", $nama_hari);
$stmt->execute();
$daftar_kelas = $stmt->get_result();

$total_ditandai = 0;

while ($row_kelas = $daftar_kelas->fetch_assoc()) {
    $kelas = $row_kelas['kelas'];

    // Ambil semua siswa di kelas ini
    $stmt2 = $koneksi->prepare("SELECT id, nama FROM siswa WHERE kelas = ?");
    $stmt2->bind_param("s", $kelas);
    $stmt2->execute();
    $siswa_list = $stmt2->get_result();

    while ($siswa = $siswa_list->fetch_assoc()) {
        // Cek apakah siswa ini SUDAH punya record absensi hari ini
        $stmt3 = $koneksi->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $stmt3->bind_param("is", $siswa['id'], $tanggal_hari_ini);
        $stmt3->execute();
        $sudah_absen = $stmt3->get_result()->fetch_assoc();

        if (!$sudah_absen) {
            // Belum ada record sama sekali -> tandai tidak hadir
            $stmt4 = $koneksi->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_scan, status) VALUES (?, ?, NULL, 'tidak hadir')");
            $stmt4->bind_param("is", $siswa['id'], $tanggal_hari_ini);
            $stmt4->execute();
            $total_ditandai++;
            echo "{$siswa['nama']} ditandai TIDAK HADIR\n";
        }
    }
}

echo "\nSelesai. Total {$total_ditandai} siswa ditandai tidak hadir untuk tanggal {$tanggal_hari_ini}.\n";
