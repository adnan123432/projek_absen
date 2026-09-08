<?php
// =========================================
// API: PROSES HASIL SCAN QR
// Menerima id_unik dari QR, cari siswa,
// tentukan status (hadir/terlambat), simpan ke tabel absensi
// =========================================
header("Content-Type: application/json");
require "../db.php";

$input = json_decode(file_get_contents("php://input"), true);
$id_unik = trim($input['id_unik'] ?? '');

if (empty($id_unik)) {
    echo json_encode(["status" => "error", "pesan" => "QR tidak terbaca / kosong"]);
    exit;
}

// 1. Cari siswa berdasarkan id_unik dari QR
$stmt = $koneksi->prepare("SELECT id, nama, kelas, nisn FROM siswa WHERE id_unik = ?");
$stmt->bind_param("s", $id_unik);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();

if (!$siswa) {
    echo json_encode(["status" => "error", "pesan" => "QR tidak dikenali / siswa tidak ditemukan"]);
    exit;
}

$siswa_id = $siswa['id'];
$tanggal_hari_ini = date("Y-m-d");
$jam_sekarang = date("H:i:s");
$nama_hari = strtolower(jddayofweek(cal_to_jd(CAL_GREGORIAN, date("n"), date("j"), date("Y")), 2));
// jddayofweek dgn mode 2 hasilnya bahasa Inggris (Sunday, Monday, ...), kita mapping ke Indonesia
$map_hari = [
    "sunday" => "minggu", "monday" => "senin", "tuesday" => "selasa",
    "wednesday" => "rabu", "thursday" => "kamis", "friday" => "jumat", "saturday" => "sabtu"
];
$nama_hari = $map_hari[$nama_hari] ?? $nama_hari;

// 2. Cek apakah siswa ini sudah absen hari ini
$stmt2 = $koneksi->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
$stmt2->bind_param("is", $siswa_id, $tanggal_hari_ini);
$stmt2->execute();
if ($stmt2->get_result()->fetch_assoc()) {
    echo json_encode([
        "status" => "error",
        "pesan" => "{$siswa['nama']} sudah absen hari ini."
    ]);
    exit;
}

// 3. Ambil jadwal kelas siswa untuk hari ini
// Catatan penting: kelas di tabel `siswa` dan `jadwal` HARUS sama persis
// (nama kelas, spasi, huruf besar/kecil). Supaya tidak gagal gara-gara
// selisih spasi/huruf besar-kecil, di sini dibandingkan pakai TRIM + LOWER.
$kelas_siswa = trim($siswa['kelas']);
$stmt3 = $koneksi->prepare(
    "SELECT jam_masuk, jam_batas_absen FROM jadwal
     WHERE LOWER(TRIM(kelas)) = LOWER(?) AND hari = ?"
);
$stmt3->bind_param("ss", $kelas_siswa, $nama_hari);
$stmt3->execute();
$jadwal = $stmt3->get_result()->fetch_assoc();

if (!$jadwal) {
    // Cek dulu apakah memang kelas ini sama sekali tidak ada di tabel jadwal
    // (beda dengan: kelas ada, tapi cuma hari ini yang kosong)
    $stmtCek = $koneksi->prepare("SELECT COUNT(*) AS jumlah FROM jadwal WHERE LOWER(TRIM(kelas)) = LOWER(?)");
    $stmtCek->bind_param("s", $kelas_siswa);
    $stmtCek->execute();
    $adaKelas = $stmtCek->get_result()->fetch_assoc()['jumlah'] > 0;

    if (!$adaKelas) {
        $pesan = "Kelas \"{$siswa['kelas']}\" belum ada jadwalnya sama sekali di tabel jadwal. "
                . "Tambahkan dulu jadwal untuk kelas ini di database.";
    } else {
        $pesan = "Kelas \"{$siswa['kelas']}\" tidak ada jadwal untuk hari {$nama_hari}. "
                . "Cek kembali data di tabel jadwal untuk hari ini.";
    }

    echo json_encode(["status" => "error", "pesan" => $pesan]);
    exit;
}

// 4. Tentukan status: hadir atau terlambat
// (kalau scan sudah lewat jam_batas_absen, sebenarnya siswa sudah otomatis
//  akan ditandai "tidak hadir" oleh cron_absen.php, jadi kasus ini jarang terjadi
//  kecuali dia scan tepat sebelum cron jalan)
$absen_status = ($jam_sekarang > $jadwal['jam_masuk']) ? "terlambat" : "hadir";

// 5. Simpan ke tabel absensi
$stmt4 = $koneksi->prepare("INSERT INTO absensi (siswa_id, tanggal, jam_scan, status) VALUES (?, ?, ?, ?)");
$stmt4->bind_param("isss", $siswa_id, $tanggal_hari_ini, $jam_sekarang, $absen_status);
$stmt4->execute();

// 6. Kembalikan data siswa + status ke halaman scan
echo json_encode([
    "status" => "sukses",
    "nama" => $siswa['nama'],
    "kelas" => $siswa['kelas'],
    "nisn" => $siswa['nisn'],
    "jam_scan" => $jam_sekarang,
    "absen_status" => $absen_status
]);
