<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan Absensi QR</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        /* Header */
        .scan-header {
            width: 100%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .scan-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .scan-title-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
        }

        .scan-title-text h2 {
            margin: 0;
            font-size: 20px;
        }

        .scan-title-text span {
            font-size: 13px;
            color: #6b7280;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .back-button:hover {
            background: #f3f4f6;
        }

        /* Scanner */
        .scanner-page {
            min-height: calc(100vh - 70px);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 25px 20px 40px;
        }

        .scanner-heading {
            text-align: center;
            margin-bottom: 18px;
        }

        .scanner-heading h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .scanner-heading p {
            margin: 0;
            color: #6b7280;
            font-size: 15px;
        }

        .scanner-card {
            width: 100%;
            max-width: 760px;
            background: #ffffff;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        /* QR Reader */
        #reader {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
        }

        #reader video {
            width: 100% !important;
            height: auto !important;
            min-height: 450px;
            object-fit: cover;
            border-radius: 12px;
        }

        #reader__scan_region {
            min-height: 450px;
        }

        #reader button {
            border: none;
            background: #2563eb;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        #reader button:hover {
            background: #1d4ed8;
        }

        #reader select {
            padding: 8px;
            border-radius: 7px;
            border: 1px solid #d1d5db;
        }

        /* Informasi */
        .scan-info {
            text-align: center;
            margin-top: 20px;
        }

        .scan-info h3 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .scan-info p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        #result {
            margin-top: 18px;
        }

        /* Notifikasi */
        .attendance-notif {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.70);
            animation: backgroundBlink 0.7s infinite alternate;
        }

        .notif-box {
            width: min(700px, 92vw);
            padding: 40px 35px;
            text-align: center;
            background: #ffffff;
            border-radius: 25px;
            box-shadow:
                0 0 25px rgba(255, 255, 255, 0.8),
                0 0 70px rgba(37, 99, 235, 0.8);
            animation:
                notifZoom 0.3s ease-out,
                notifPulse 0.8s infinite alternate;
        }

        .notif-icon {
            font-size: 70px;
            margin-bottom: 15px;
        }

        .notif-title {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .notif-message {
            font-size: 21px;
            line-height: 1.5;
            font-weight: 600;
        }

        .notif-time {
            margin-top: 18px;
            font-size: 16px;
            color: #6b7280;
        }

        /* Warna Notifikasi */
        .notif-hadir .notif-box {
            border: 6px solid #22c55e;
        }

        .notif-hadir .notif-title {
            color: #16a34a;
        }

        .notif-terlambat .notif-box {
            border: 6px solid #f59e0b;
        }

        .notif-terlambat .notif-title {
            color: #d97706;
        }

        .notif-ditolak .notif-box {
            border: 6px solid #ef4444;
        }

        .notif-ditolak .notif-title {
            color: #dc2626;
        }

        .notif-sudah .notif-box {
            border: 6px solid #2563eb;
        }

        .notif-sudah .notif-title {
            color: #2563eb;
        }

        .notif-error .notif-box {
            border: 6px solid #ef4444;
        }

        .notif-error .notif-title {
            color: #dc2626;
        }

        /* Animasi */
        @keyframes backgroundBlink {
            from {
                background: rgba(0, 0, 0, 0.50);
            }

            to {
                background: rgba(0, 0, 0, 0.85);
            }
        }

        @keyframes notifPulse {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.025);
            }
        }

        @keyframes notifZoom {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 700px) {
            .scan-header {
                padding: 0 15px;
            }

            .scan-title-text h2 {
                font-size: 16px;
            }

            .scan-title-text span {
                display: none;
            }

            .scanner-page {
                padding: 20px 10px 30px;
            }

            .scanner-heading h1 {
                font-size: 23px;
            }

            .scanner-card {
                padding: 12px;
                border-radius: 12px;
            }

            #reader video {
                min-height: 350px;
            }

            #reader__scan_region {
                min-height: 350px;
            }

            .back-button {
                padding: 8px 11px;
                font-size: 13px;
            }

            .notif-box {
                padding: 30px 20px;
            }

            .notif-icon {
                font-size: 55px;
            }

            .notif-title {
                font-size: 25px;
            }

            .notif-message {
                font-size: 17px;
            }
        }
    </style>
</head>

<body>

<header class="scan-header">
    <div class="scan-title">
        <div class="scan-title-icon">QR</div>

        <div class="scan-title-text">
            <h2>E-ABSENSI</h2>
            <span>Scanner Absensi Siswa</span>
        </div>
    </div>

    <a href="index.php" class="back-button">
        ← Kembali ke Dashboard
    </a>
</header>

<main class="scanner-page">
    <div class="scanner-heading">
        <h1>Scan QR Code</h1>
        <p>Arahkan QR Code siswa ke kamera untuk melakukan absensi.</p>
    </div>

    <div class="scanner-card">
        <div id="reader"></div>

        <div class="scan-info">
            <h3>Scanner QR Code</h3>
            <p>
                Scan pertama mencatat <b>jam masuk</b>.
                Scan berikutnya mencatat <b>jam pulang</b>.
            </p>

            <div id="result"></div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/html5-qrcode/html5-qrcode.min.js"></script>

<script>
let busy = false;

function getWaktuWindows() {
    const sekarang = new Date();

    const tahun = sekarang.getFullYear();
    const bulan = String(sekarang.getMonth() + 1).padStart(2, '0');
    const tanggal = String(sekarang.getDate()).padStart(2, '0');

    const jam = String(sekarang.getHours()).padStart(2, '0');
    const menit = String(sekarang.getMinutes()).padStart(2, '0');
    const detik = String(sekarang.getSeconds()).padStart(2, '0');

    return {
        tanggal: `${tahun}-${bulan}-${tanggal}`,
        jam: `${jam}:${menit}:${detik}`
    };
}

function speakNotification(text) {
    if (!('speechSynthesis' in window)) {
        return;
    }

    window.speechSynthesis.cancel();

    const suara = new SpeechSynthesisUtterance(text);

    suara.lang = 'id-ID';
    suara.rate = 0.95;
    suara.pitch = 1;
    suara.volume = 1;

    window.speechSynthesis.speak(suara);
}

function getIcon(jenis) {
    if (jenis === 'notif-hadir') return '✅';
    if (jenis === 'notif-terlambat') return '⚠️';
    if (jenis === 'notif-ditolak') return '🚫';
    if (jenis === 'notif-error') return '❌';

    return 'ℹ️';
}

function showNotification(jenis, title, message, jam = '') {
    const el = document.getElementById('result');

    el.innerHTML = `
        <div class="attendance-notif ${jenis}">
            <div class="notif-box">
                <div class="notif-icon">${getIcon(jenis)}</div>
                <div class="notif-title">${title}</div>
                <div class="notif-message">${message}</div>
                ${jam ? `<div class="notif-time">${jam}</div>` : ''}
            </div>
        </div>
    `;
}

function onScanSuccess(decodedText) {
    if (busy) {
        return;
    }

    busy = true;

    const waktu = getWaktuWindows();
    const data = new URLSearchParams();

    data.append('nis', decodedText);
    data.append('tanggal', waktu.tanggal);
    data.append('jam', waktu.jam);

    fetch('api/scan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data.toString()
    })
    .then(response => response.text())
    .then(text => {
        console.log('RESPONS SERVER:', text);

        let data;

        try {
            data = JSON.parse(text);
        } catch (error) {
            console.error('JSON ERROR:', error);
            console.error('ISI SERVER:', text);

            showNotification(
                'notif-error',
                'SERVER ERROR',
                'Absensi mungkin sudah tersimpan, tetapi respons server tidak dapat dibaca.'
            );

            speakNotification(
                'Server error. Respons server tidak dapat dibaca.'
            );

            resetScanner();

            return;
        }

        const nama = data.nama || 'Siswa';

        let jenis = 'notif-sudah';
        let title = 'INFORMASI';
        let message = data.message || 'Absensi diproses.';

        if (
            data.ok === true &&
            data.mode === 'masuk' &&
            data.status === 'Hadir'
        ) {
            jenis = 'notif-hadir';
            title = 'ABSENSI BERHASIL';
            message = `Terima kasih sudah hadir tepat waktu, ${nama}!`;
        }

        else if (
            data.ok === true &&
            data.mode === 'masuk' &&
            data.status === 'Terlambat'
        ) {
            jenis = 'notif-terlambat';
            title = 'TERLAMBAT';
            message = `Keterlambatan tercatat. Besok usahakan lebih awal ya, ${nama}.`;
        }

        else if (
            data.ok === true &&
            data.mode === 'pulang'
        ) {
            jenis = 'notif-hadir';
            title = 'ABSENSI PULANG BERHASIL';
            message = `Absensi pulang berhasil, ${nama}.`;
        }

        else if (
            data.ok === false &&
            data.mode === 'belum_waktu_pulang'
        ) {
            jenis = 'notif-ditolak';
            title = 'AKSES DITOLAK';
            message = `Belum waktunya pulang. Silakan kembali ke kelas, ${nama}.`;
        }

        else if (
            data.ok === false &&
            data.mode === 'lengkap'
        ) {
            jenis = 'notif-sudah';
            title = 'SUDAH ABSEN';
            message = `Anda sudah absen hari ini, ${nama}.`;
        }

        else if (data.ok === false) {
            jenis = 'notif-error';
            title = 'ABSENSI GAGAL';
            message = data.message || 'Absensi tidak dapat diproses.';
        }

        showNotification(
            jenis,
            title,
            message,
            data.jam || ''
        );

        speakNotification(`${title}. ${message}`);

        resetScanner();
    })
    .catch(error => {
        console.error('FETCH ERROR:', error);

        showNotification(
            'notif-error',
            'KESALAHAN KONEKSI',
            'Terjadi kesalahan saat menghubungi server.'
        );

        speakNotification(
            'Terjadi kesalahan saat menghubungi server.'
        );

        resetScanner();
    });
}

function resetScanner() {
    setTimeout(() => {
        document.getElementById('result').innerHTML = '';
        busy = false;
    }, 3500);
}

function onScanFailure(error) {
    // Scanner tetap berjalan sampai QR berhasil terbaca.
}

const scanner = new Html5QrcodeScanner(
    'reader',
    {
        fps: 10,
        qrbox: {
            width: 450,
            height: 450
        },
        rememberLastUsedCamera: true,
        showTorchButtonIfSupported: true
    },
    false
);

scanner.render(
    onScanSuccess,
    onScanFailure
);
</script>

</body>
</html>