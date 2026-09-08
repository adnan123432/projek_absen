<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die('Siswa tidak ditemukan.');
}

$title = 'Kartu QR Siswa';
include 'partials/header.php';
?>

<div class="form-card" style="text-align: center; margin: auto;">
    <h3><?= e($siswa['nama']) ?></h3>
    <p><?= e($siswa['nis']) ?> • <?= e($siswa['kelas']) ?></p>
    
    <div class="qrbox">
        <div id="qrcode"></div>
    </div>
    
    <p style="font-size: 10px; color: #888;">Tunjukkan QR ini saat melakukan absensi.</p>
    <button class="btn" onclick="window.print()">Cetak Kartu</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= e($siswa['nis']) ?>",
        width: 170,
        height: 170
    });
</script>

<?php include 'partials/footer.php'; ?>