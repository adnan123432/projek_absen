<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title = 'Scan Absensi';
include 'partials/header.php';
?>

<div class="scanner">
    <div id="reader"></div>

    <div class="scan-info">
        <h3>Scanner QR Code</h3>

        <p>
            Arahkan kamera ke QR Code siswa.
            Scan pertama mencatat <b>jam masuk</b>,
            scan berikutnya mencatat <b>jam pulang</b>.
        </p>

        <div id="result"></div>
    </div>
</div>

<!-- Library QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode/html5-qrcode.min.js"></script>

<script>
let busy = false;

/*
 * Mengambil waktu dari komputer/laptop
 */
function getWaktuWindows() {

    const sekarang = new Date();

    const tahun = sekarang.getFullYear();
    const bulan = String(sekarang.getMonth() + 1).padStart(2, '0');
    const tanggal = String(sekarang.getDate()).padStart(2, '0');

    const jam = String(sekarang.getHours()).padStart(2, '0');
    const menit = String(sekarang.getMinutes()).padStart(2, '0');
    const detik = String(sekarang.getSeconds()).padStart(2, '0');

    return {
        tanggal: tahun + '-' + bulan + '-' + tanggal,
        jam: jam + ':' + menit + ':' + detik
    };
}


/*
 * Ketika QR Code berhasil dibaca
 */
function onScanSuccess(decodedText, decodedResult) {

    if (busy) return;

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

    .then(response => response.json())

    .then(data => {

        const el = document.getElementById('result');

        el.innerHTML =
            '<div class="alert ' +
            (data.ok ? 'success' : 'error') +
            '">' +
            data.message +
            '</div>';

        setTimeout(() => {
            busy = false;
        }, 1800);

    })

    .catch(error => {

        console.error(error);

        document.getElementById('result').innerHTML =
            '<div class="alert error">' +
            'Gagal menghubungi server.' +
            '</div>';

        setTimeout(() => {
            busy = false;
        }, 1800);

    });
}


/*
 * Jika kamera gagal dibuka
 */
function onScanFailure(error) {
    // Tidak perlu menampilkan error setiap frame
}


/*
 * Jalankan scanner
 */
const scanner = new Html5QrcodeScanner(
    "reader",
    {
        fps: 10,
        qrbox: {
            width: 240,
            height: 240
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

<?php include 'partials/footer.php'; ?>