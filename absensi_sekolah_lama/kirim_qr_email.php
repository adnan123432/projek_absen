<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/mail.php';

require_login();

$title = 'Kirim QR ke Email';

// Ambil semua siswa yang punya email diisi
$rows = $pdo->query("SELECT id, nis, nama, kelas, email, qr_path FROM siswa ORDER BY kelas, nama")->fetchAll();

$siapKirim   = []; // punya email dan qr_path
$tanpaQr     = []; // punya email tapi qr_path masih kosong
$tanpaEmail  = []; // email belum diisi

foreach ($rows as $r) {
    $adaEmail = !empty($r['email']);
    $adaQr    = !empty($r['qr_path']);

    if (!$adaEmail) {
        $tanpaEmail[] = $r;
    } elseif (!$adaQr) {
        $tanpaQr[] = $r;
    } else {
        $siapKirim[] = $r;
    }
}

$hasil = null;

// Proses pengiriman saat form dikonfirmasi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'kirim') {
    $terkirim = [];
    $gagal    = [];

    foreach ($siapKirim as $s) {
        $pathFile = __DIR__ . '/' . ltrim($s['qr_path'], '/');

        if (!file_exists($pathFile)) {
            $gagal[] = $s['nama'] . ' (file QR tidak ditemukan: ' . $s['qr_path'] . ')';
            continue;
        }

        try {
            $mail = buatMailer();
            $mail->addAddress($s['email'], $s['nama']);
            $mail->addAttachment($pathFile, 'QR-' . $s['nis'] . '.png');

            $mail->isHTML(true);
            $mail->Subject = 'QR Code Absensi - ' . $s['nama'];
            $mail->Body    = "
                <p>Halo <b>" . htmlspecialchars($s['nama']) . "</b>,</p>
                <p>Berikut QR Code untuk absensi kamu di kelas <b>" . htmlspecialchars($s['kelas']) . "</b>.</p>
                <p>Simpan gambar terlampir ini dan tunjukkan saat scan absensi setiap hari.</p>
                <p>Terima kasih.</p>
            ";
            $mail->AltBody = "Halo {$s['nama']}, berikut QR Code absensi kamu (terlampir).";

            $mail->send();
            $terkirim[] = $s['nama'];
        } catch (Exception $e) {
            $gagal[] = $s['nama'] . ' (' . $e->getMessage() . ')';
        }
    }

    $hasil = ['terkirim' => $terkirim, 'gagal' => $gagal];
}

include 'partials/header.php';
?>

<div class="section-head">
    <h3>Kirim QR ke Email Siswa</h3>
    <a class="btn secondary" href="siswa.php">Kembali ke Data Siswa</a>
</div>

<?php if ($hasil): ?>
    <div class="section">
        <p><strong><?= count($hasil['terkirim']) ?> email berhasil dikirim.</strong></p>
        <?php if ($hasil['terkirim']): ?>
            <p>Terkirim ke: <?= e(implode(', ', $hasil['terkirim'])) ?></p>
        <?php endif; ?>

        <?php if ($hasil['gagal']): ?>
            <p style="color:#c0392b;"><strong><?= count($hasil['gagal']) ?> gagal terkirim:</strong></p>
            <ul>
                <?php foreach ($hasil['gagal'] as $g): ?>
                    <li style="color:#c0392b;"><?= e($g) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php else: ?>

    <div class="section">
        <p><strong><?= count($siapKirim) ?></strong> siswa siap dikirimi email (punya email &amp; QR).</p>

        <?php if ($tanpaQr): ?>
            <p style="color:#b45309;">
                <strong><?= count($tanpaQr) ?></strong> siswa punya email tapi QR-nya belum digenerate
                (jalankan <code>generate_qr.php</code> dulu):
                <?= e(implode(', ', array_column($tanpaQr, 'nama'))) ?>
            </p>
        <?php endif; ?>

        <?php if ($tanpaEmail): ?>
            <p style="color:#888;">
                <strong><?= count($tanpaEmail) ?></strong> siswa belum diisi emailnya, jadi dilewati.
            </p>
        <?php endif; ?>
    </div>

    <?php if ($siapKirim): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siapKirim as $s): ?>
                        <tr>
                            <td><?= e($s['nis']) ?></td>
                            <td><?= e($s['nama']) ?></td>
                            <td><?= e($s['kelas']) ?></td>
                            <td><?= e($s['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form method="post" style="margin-top:16px;" onsubmit="return confirm('Kirim QR ke ' + <?= count($siapKirim) ?> + ' siswa sekarang?');">
            <input type="hidden" name="aksi" value="kirim">
            <button type="submit" class="btn">Kirim Sekarang ke <?= count($siapKirim) ?> Siswa</button>
        </form>
    <?php else: ?>
        <p>Tidak ada siswa yang siap dikirimi email saat ini.</p>
    <?php endif; ?>

<?php endif; ?>

<?php include 'partials/footer.php'; ?>