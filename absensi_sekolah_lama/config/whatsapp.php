<?php
/**
 * Helper untuk kirim notifikasi WhatsApp via Fonnte
 * Daftar & dapatkan token di https://fonnte.com
 */

// Ganti dengan token API Fonnte kamu
define('FONNTE_TOKEN', 'z8q8vVvEdzNpMhAJWJdN');

/**
 * Kirim pesan WhatsApp
 *
 * @param string $nomor   Nomor tujuan, format: 628123456789 (tanpa + atau 0 di depan)
 * @param string $pesan   Isi pesan
 * @return array          ['success' => bool, 'response' => string]
 */
function kirim_wa($nomor, $pesan)
{
    // Validasi sederhana: kalau nomor kosong, jangan kirim
    if (empty($nomor)) {
        return ['success' => false, 'response' => 'Nomor WA kosong'];
    }

    // Rapikan nomor: hapus spasi, strip, dan ubah awalan 0 jadi 62
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 10, // jangan sampai bikin scan.php nge-hang lama
        CURLOPT_POSTFIELDS     => [
            'target'  => $nomor,
            'message' => $pesan,
        ],
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . FONNTE_TOKEN,
        ],
    ]);

    $response = curl_exec($curl);
    $error    = curl_error($curl);
    curl_close($curl);

    if ($error) {
        // Log error tapi jangan hentikan proses absensi
        error_log('Gagal kirim WA: ' . $error);
        return ['success' => false, 'response' => $error];
    }

    return ['success' => true, 'response' => $response];
}

/**
 * Format pesan notifikasi absensi
 */
function format_pesan_absensi($nama, $kelas, $status, $jam, $keterangan = '')
{
    $emoji = match (strtolower($status)) {
        'hadir'     => '✅',
        'terlambat' => '⏰',
        'izin'      => '📩',
        'sakit'     => '🤒',
        'alpa'      => '❌',
        default     => 'ℹ️',
    };

    $pesan = "$emoji *Notifikasi Absensi*\n\n";
    $pesan .= "Nama : $nama\n";
    $pesan .= "Kelas : $kelas\n";
    $pesan .= "Status : $status\n";
    $pesan .= "Jam : $jam\n";

    if (!empty($keterangan)) {
        $pesan .= "Keterangan : $keterangan\n";
    }

    $pesan .= "\nPesan ini dikirim otomatis oleh sistem E-Absensi sekolah.";

    return $pesan;
}