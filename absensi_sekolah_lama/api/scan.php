<?php
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/whatsapp.php';

require_login();

header('Content-Type: application/json; charset=utf-8');

function responseJson($ok, $message, $extra = [])
{
    echo json_encode(array_merge([
        'ok' => $ok,
        'message' => $message
    ], $extra));
    exit;
}

try {

    // =====================================================
    // INPUT DARI SCANNER
    // =====================================================

    $nis     = trim($_POST['nis'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');
    $jam     = trim($_POST['jam'] ?? '');

    if ($nis === '') {
        responseJson(false, 'QR Code kosong atau tidak terbaca.');
    }

    if ($tanggal === '' || $jam === '') {
        responseJson(false, 'Tanggal atau jam tidak diterima.');
    }


    // =====================================================
    // AMBIL PENGATURAN
    // =====================================================

    $stmt = $pdo->query("
        SELECT jam_masuk, jam_pulang, toleransi_menit
        FROM pengaturan
        WHERE id = 1
        LIMIT 1
    ");

    $pengaturan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pengaturan) {
        responseJson(
            false,
            'Pengaturan absensi belum tersedia.'
        );
    }

    $jamMasukNormal = $pengaturan['jam_masuk'];
    $jamPulangNormal = $pengaturan['jam_pulang'];
    $toleransiMenit = (int) $pengaturan['toleransi_menit'];


    // =====================================================
    // HITUNG BATAS TOLERANSI
    // =====================================================

    $jamMasukTimestamp = strtotime(
        $tanggal . ' ' . $jamMasukNormal
    );

    $batasTerlambatTimestamp = strtotime(
        '+' . $toleransiMenit . ' minutes',
        $jamMasukTimestamp
    );

    $batasTerlambat = date(
        'H:i:s',
        $batasTerlambatTimestamp
    );


    // =====================================================
    // CARI SISWA BERDASARKAN NIS
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT id, nis, nama, kelas, no_wa_ortu
        FROM siswa
        WHERE nis = ?
        LIMIT 1
    ");

    $stmt->execute([$nis]);

    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

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

    $absensi = $stmt->fetch(PDO::FETCH_ASSOC);


    // =====================================================
    // SCAN PERTAMA = ABSEN MASUK
    // =====================================================

    if (!$absensi) {

        $jamScanTimestamp = strtotime(
            $tanggal . ' ' . $jam
        );


        // -----------------------------
        // TEPAT WAKTU
        // -----------------------------

        if ($jamScanTimestamp <= $batasTerlambatTimestamp) {

            $status = 'Hadir';

            $keterangan =
                'Hadir tepat waktu. Jam scan: ' .
                $jam . '.';

        }

        // -----------------------------
        // TERLAMBAT
        // -----------------------------

        else {

            $status = 'Terlambat';

            $keterangan =
                'Terlambat. Jam masuk normal: ' .
                $jamMasukNormal .
                ', batas toleransi: ' .
                $batasTerlambat . '.';
        }


        // -----------------------------
        // SIMPAN
        // -----------------------------

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


        // -----------------------------
        // KIRIM NOTIFIKASI WHATSAPP KE ORANG TUA
        // -----------------------------

        if (!empty($siswa['no_wa_ortu'])) {
            $pesanWa = format_pesan_absensi(
                $siswa['nama'],
                $siswa['kelas'],
                $status,
                $jam,
                'Absen masuk'
            );

            kirim_wa($siswa['no_wa_ortu'], $pesanWa);
        }


        // -----------------------------
        // RESPONSE
        // -----------------------------

        responseJson(
            true,
            'Absensi masuk berhasil.',
            [
                'mode' => 'masuk',
                'nama' => $siswa['nama'],
                'nis' => $siswa['nis'],
                'kelas' => $siswa['kelas'],
                'jam' => $jam,
                'tanggal' => $tanggal,
                'status' => $status,
                'jam_masuk_normal' => $jamMasukNormal,
                'batas_terlambat' => $batasTerlambat,
                'toleransi_menit' => $toleransiMenit
            ]
        );
    }


    // =====================================================
    // SCAN KEDUA = ABSEN PULANG
    // =====================================================

    if (empty($absensi['jam_pulang'])) {

        // -----------------------------
        // CEK JAM PULANG
        // -----------------------------

        if (!empty($jamPulangNormal)) {

            $jamSekarangTimestamp = strtotime(
                $tanggal . ' ' . $jam
            );

            $jamPulangTimestamp = strtotime(
                $tanggal . ' ' . $jamPulangNormal
            );


            // Belum waktunya pulang
            if ($jamSekarangTimestamp < $jamPulangTimestamp) {

                responseJson(
                    false,
                    'Belum waktunya pulang, silakan kembali ke kelas.',
                    [
                        'mode' => 'belum_waktu_pulang',
                        'nama' => $siswa['nama'],
                        'nis' => $siswa['nis'],
                        'jam' => $jam,
                        'tanggal' => $tanggal,
                        'status' => $absensi['status'],
                        'jam_pulang_normal' => $jamPulangNormal
                    ]
                );
            }
        }


        // -----------------------------
        // SIMPAN JAM PULANG
        // -----------------------------

        $stmt = $pdo->prepare("
            UPDATE absensi
            SET jam_pulang = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $jam,
            $absensi['id']
        ]);


        // -----------------------------
        // KIRIM NOTIFIKASI WHATSAPP KE ORANG TUA
        // -----------------------------

        if (!empty($siswa['no_wa_ortu'])) {
            $pesanWa = format_pesan_absensi(
                $siswa['nama'],
                $siswa['kelas'],
                'Pulang',
                $jam,
                'Absen pulang'
            );

            kirim_wa($siswa['no_wa_ortu'], $pesanWa);
        }


        responseJson(
            true,
            'Absensi pulang berhasil.',
            [
                'mode' => 'pulang',
                'nama' => $siswa['nama'],
                'nis' => $siswa['nis'],
                'kelas' => $siswa['kelas'],
                'jam' => $jam,
                'tanggal' => $tanggal,
                'status' => $absensi['status'],
                'jam_pulang_normal' => $jamPulangNormal
            ]
        );
    }


    // =====================================================
    // SUDAH ABSEN MASUK + PULANG
    // =====================================================

    responseJson(
        false,
        'Anda sudah absen hari ini.',
        [
            'mode' => 'lengkap',
            'nama' => $siswa['nama'],
            'nis' => $siswa['nis'],
            'kelas' => $siswa['kelas']
        ]
    );


} catch (PDOException $e) {

    // =====================================================
    // ERROR DATABASE
    // =====================================================

    responseJson(
        false,
        'Database error: ' . $e->getMessage(),
        [
            'mode' => 'database_error'
        ]
    );

} catch (Throwable $e) {

    // =====================================================
    // ERROR PHP LAINNYA
    // =====================================================

    responseJson(
        false,
        'Server error: ' . $e->getMessage(),
        [
            'mode' => 'server_error'
        ]
    );
}