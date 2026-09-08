<?php
require_once '../config/database.php';
require_once '../config/auth.php';

require_login();

header('Content-Type: application/json; charset=utf-8');

function responseJson($ok, $message, $extra = [])
{
    echo json_encode(array_merge([
        'ok'      => $ok,
        'message' => $message
    ], $extra));
    exit;
}


// =====================================================
// AMBIL DATA DARI INPUT
// =====================================================

$nis     = trim($_POST['nis'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$jam     = trim($_POST['jam'] ?? '');


// =====================================================
// VALIDASI INPUT
// =====================================================

if ($nis === '') {
    responseJson(false, 'QR Code kosong atau tidak terbaca.');
}

if ($tanggal === '' || $jam === '') {
    responseJson(false, 'Waktu komputer tidak berhasil dibaca.');
}


// =====================================================
// AMBIL PENGATURAN ABSENSI
// =====================================================

$cfg = $pdo->query("
    SELECT jam_masuk, jam_pulang, toleransi_menit
    FROM pengaturan
    WHERE id = 1
    LIMIT 1
")->fetch();

if (!$cfg) {
    responseJson(
        false,
        'Pengaturan absensi belum tersedia. Silakan buka menu Pengaturan.'
    );
}

$jamMasukNormal = $cfg['jam_masuk'];
$jamPulangNormal = $cfg['jam_pulang'];
$toleransiMenit = (int) $cfg['toleransi_menit'];


// =====================================================
// HITUNG BATAS TERLAMBAT
// =====================================================
//
// Contoh:
// Jam masuk  = 07:00
// Toleransi  = 15 menit
// Batas      = 07:15
//

$jamMasukTimestamp = strtotime($tanggal . ' ' . $jamMasukNormal);

$batasTerlambatTimestamp = strtotime(
    '+' . $toleransiMenit . ' minutes',
    $jamMasukTimestamp
);

$batasTerlambat = date('H:i:s', $batasTerlambatTimestamp);


// =====================================================
// CARI DATA SISWA
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM siswa
    WHERE nis = ?
    LIMIT 1
");

$stmt->execute([$nis]);

$siswa = $stmt->fetch();

if (!$siswa) {
    responseJson(
        false,
        'Siswa dengan NIS ' . $nis . ' tidak ditemukan.'
    );
}


// =====================================================
// CEK ABSENSI HARI INI
// =====================================================

$stmt = $pdo->prepare("
    SELECT *
    FROM absensi
    WHERE siswa_id = ?
    AND tanggal = ?
    LIMIT 1
");

$stmt->execute([
    $siswa['id'],
    $tanggal
]);

$absensi = $stmt->fetch();


// =====================================================
// SCAN PERTAMA = JAM MASUK
// =====================================================

if (!$absensi) {

    /*
     * Tentukan status berdasarkan:
     *
     * jam masuk normal
     * +
     * toleransi keterlambatan
     */

    $jamScanTimestamp = strtotime(
        $tanggal . ' ' . $jam
    );


    // ---------------------------------------------
    // HADIR
    // ---------------------------------------------

    if ($jamScanTimestamp <= $batasTerlambatTimestamp) {

        $status = 'Hadir';

        $keterangan =
            'Scan masuk ' .
            'pukul ' . $jam .
            '. Batas toleransi sampai ' .
            $batasTerlambat . '.';

    }

    // ---------------------------------------------
    // TERLAMBAT
    // ---------------------------------------------

    else {

        $status = 'Terlambat';

        $keterangan =
            'Terlambat. Jam masuk normal ' .
            $jamMasukNormal .
            ', batas toleransi ' .
            $batasTerlambat .
            '.';
    }


    // ---------------------------------------------
    // SIMPAN ABSENSI
    // ---------------------------------------------

    $stmt = $pdo->prepare("
        INSERT INTO absensi
        (
            siswa_id,
            tanggal,
            jam_masuk,
            status,
            keterangan
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $siswa['id'],
        $tanggal,
        $jam,
        $status,
        $keterangan
    ]);


    // ---------------------------------------------
    // HASIL
    // ---------------------------------------------

    responseJson(
        true,
        'Absensi masuk berhasil: ' .
        $siswa['nama'] .
        ' • ' .
        $jam .
        ' • ' .
        $status,
        [
            'mode'              => 'masuk',
            'nama'              => $siswa['nama'],
            'jam'               => $jam,
            'tanggal'           => $tanggal,
            'status'             => $status,
            'jam_masuk_normal'  => $jamMasukNormal,
            'batas_terlambat'   => $batasTerlambat,
            'toleransi_menit'   => $toleransiMenit
        ]
    );
}


// =====================================================
// SCAN KEDUA = JAM PULANG
// =====================================================

if (empty($absensi['jam_pulang'])) {

    // ---------------------------------------------
    // CEK WAKTU PULANG
    // ---------------------------------------------

    if ($jamPulangNormal) {

        $jamSekarangTimestamp = strtotime(
            $tanggal . ' ' . $jam
        );

        $jamPulangTimestamp = strtotime(
            $tanggal . ' ' . $jamPulangNormal
        );


        if ($jamSekarangTimestamp < $jamPulangTimestamp) {

            responseJson(
                false,
                $siswa['nama'] .
                ' sudah absen masuk dan belum waktunya pulang.',
                [
                    'mode'              => 'belum_waktu_pulang',
                    'nama'              => $siswa['nama'],
                    'jam'               => $jam,
                    'tanggal'           => $tanggal,
                    'status'            => $absensi['status'],
                    'jam_pulang_normal' => $jamPulangNormal
                ]
            );
        }
    }


    // ---------------------------------------------
    // SIMPAN JAM PULANG
    // ---------------------------------------------

    $stmt = $pdo->prepare("
        UPDATE absensi
        SET jam_pulang = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $jam,
        $absensi['id']
    ]);


    responseJson(
        true,
        'Absensi pulang berhasil: ' .
        $siswa['nama'] .
        ' • ' .
        $jam,
        [
            'mode'              => 'pulang',
            'nama'              => $siswa['nama'],
            'jam'               => $jam,
            'tanggal'           => $tanggal,
            'status'            => $absensi['status'],
            'jam_pulang_normal' => $jamPulangNormal
        ]
    );
}


// =====================================================
// SUDAH MASUK & PULANG
// =====================================================

responseJson(
    false,
    'Absensi hari ini sudah lengkap untuk ' .
    $siswa['nama'] .
    '.',
    [
        'mode' => 'lengkap'
    ]
);