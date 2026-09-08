<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title = 'Pengaturan Absensi';

// Ambil Konfigurasi Pengaturan
$cfg = $pdo->query("SELECT * FROM pengaturan WHERE id = 1")->fetch();

// Simpan Pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama_sekolah']);
    $masuk     = $_POST['jam_masuk'];
    $pulang    = $_POST['jam_pulang'];
    $toleransi = max(0, (int) $_POST['toleransi']);

    $pdo->prepare("UPDATE pengaturan SET nama_sekolah = ?, jam_masuk = ?, jam_pulang = ?, toleransi_menit = ? WHERE id = 1")
        ->execute([$nama, $masuk, $pulang, $toleransi]);

    flash('success', 'Pengaturan disimpan.');
    header('Location: pengaturan.php');
    exit;
}

include 'partials/header.php';
?>

<div class="form-card">
    <form method="post">
        <div class="field">
            <label>Nama Sekolah</label>
            <input name="nama_sekolah" value="<?= e($cfg['nama_sekolah']) ?>" required>
        </div>

        <div class="grid2">
            <div class="field">
                <label>Jam Masuk Normal</label>
                <input type="time" name="jam_masuk" value="<?= e($cfg['jam_masuk']) ?>">
            </div>

            <div class="field">
                <label>Jam Pulang Normal</label>
                <input type="time" name="jam_pulang" value="<?= e($cfg['jam_pulang']) ?>">
            </div>
        </div>

        <div class="field">
            <label>Toleransi Keterlambatan (Menit)</label>
            <input type="number" min="0" name="toleransi" value="<?= e($cfg['toleransi_menit']) ?>">
        </div>

        <button type="submit" class="btn">Simpan Pengaturan</button>
    </form>
</div>

<?php include 'partials/footer.php'; ?>