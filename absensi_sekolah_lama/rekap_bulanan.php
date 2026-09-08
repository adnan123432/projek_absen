<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title = 'Rekap Bulanan';
$bulan = $_GET['bulan'] ?? date('Y-m');
$kelas = $_GET['kelas'] ?? '';

// Ambil data akumulasi rekap bulanan siswa
$sql = "SELECT 
            s.nis,
            s.nama,
            s.kelas,
            SUM(a.status = 'Hadir') AS hadir,
            SUM(a.status = 'Izin')  AS izin,
            SUM(a.status = 'Sakit') AS sakit,
            SUM(a.status = 'Alpa')  AS alpa
        FROM siswa s 
        LEFT JOIN absensi a 
               ON a.siswa_id = s.id 
              AND DATE_FORMAT(a.tanggal, '%Y-%m') = ?
        WHERE 1 = 1";

$params = [$bulan];

if ($kelas !== '') {
    $sql .= " AND s.kelas = ?";
    $params[] = $kelas;
}

$sql .= " GROUP BY s.id ORDER BY s.kelas, s.nama";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Ambil daftar kelas untuk dropdown filter
$kelasRows = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas")->fetchAll();

include 'partials/header.php';
?>

<div class="section-head">
    <h3>Rekap Bulanan</h3>
    <a class="btn" href="export_csv.php?tanggal=<?= date('Y-m-d') ?>">Export CSV</a>
</div>

<form class="filters">
    <input type="month" name="bulan" value="<?= e($bulan) ?>">
    
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
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['kelas']) ?></td>
                    <td><?= e($r['hadir'] ?? 0) ?></td>
                    <td><?= e($r['izin'] ?? 0) ?></td>
                    <td><?= e($r['sakit'] ?? 0) ?></td>
                    <td><?= e($r['alpa'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #888;">
                        Tidak ada data siswa.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>