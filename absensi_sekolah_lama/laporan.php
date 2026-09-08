<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title   = 'Laporan Absensi';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas   = $_GET['kelas'] ?? '';

// Ambil data absensi berdasarkan filter
$sql = "SELECT a.*, s.nis, s.nama, s.kelas 
        FROM absensi a 
        JOIN siswa s ON s.id = a.siswa_id 
        WHERE a.tanggal = ?";

$params = [$tanggal];

if ($kelas !== '') {
    $sql .= " AND s.kelas = ?";
    $params[] = $kelas;
}

$sql .= " ORDER BY s.kelas, s.nama";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Ambil daftar kelas untuk dropdown filter
$kelasRows = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas")->fetchAll();

include 'partials/header.php';
?>

<div class="section-head">
    <h3>Rekap Kehadiran</h3>
    <div>
        <a class="btn secondary" href="export_csv.php?tanggal=<?= e($tanggal) ?>&kelas=<?= urlencode($kelas) ?>">Export CSV</a>
        <button class="btn" onclick="window.print()">Cetak</button>
    </div>
</div>

<form class="filters">
    <input type="date" name="tanggal" value="<?= e($tanggal) ?>">
    
    <select name="kelas">
        <option value="">Semua Kelas</option>
        <?php foreach ($kelasRows as $k): ?>
            <option value="<?= e($k['kelas']) ?>" <?= ($kelas === $k['kelas'] ? 'selected' : '') ?>>
                <?= e($k['kelas']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <button class="btn">Tampilkan</button>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['kelas']) ?></td>
                    <td><?= e($r['jam_masuk'] ?: '-') ?></td>
                    <td><?= e($r['jam_pulang'] ?: '-') ?></td>
                    <td>
                        <span class="badge <?= strtolower(e($r['status'])) ?>">
                            <?= e($r['status']) ?>
                        </span>
                    </td>
                    <td><?= e($r['keterangan']) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #888;">
                        Belum ada absensi pada tanggal ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>