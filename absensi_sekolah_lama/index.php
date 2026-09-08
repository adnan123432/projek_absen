<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title = 'Dashboard';

// =====================================================
// DATA STATISTIK
// =====================================================
// Total siswa
$stmt = $pdo->query("SELECT COUNT(*) FROM siswa");
$totalSiswa = (int) $stmt->fetchColumn();

// Total guru
$stmt = $pdo->query("SELECT COUNT(*) FROM guru");
$totalGuru = (int) $stmt->fetchColumn();

// Tanggal hari ini
$today = date('Y-m-d');

// =====================================================
// ABSENSI HARI INI
// =====================================================
// Jumlah siswa yang hadir
$stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status = 'Hadir'");
$stmt->execute([$today]);
$hadir = (int) $stmt->fetchColumn();

// Jumlah siswa dengan status selain Hadir
$stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status <> 'Hadir'");
$stmt->execute([$today]);
$lain = (int) $stmt->fetchColumn();

// Jumlah siswa yang belum melakukan absensi
$belum = max(0, $totalSiswa - $hadir - $lain);

// =====================================================
// ABSENSI TERBARU HARI INI
// =====================================================
$stmt = $pdo->query("
    SELECT a.*, s.nis, s.nama, s.kelas
    FROM absensi a
    INNER JOIN siswa s ON s.id = a.siswa_id
    WHERE a.tanggal = CURDATE()
    ORDER BY a.jam_masuk DESC
    LIMIT 10
");
$recent = $stmt->fetchAll();

include 'partials/header.php';
?>

<!-- KARTU STATISTIK -->
<div class="cards">
    <!-- Total Siswa -->
    <div class="card stat">
        <div>
            <small>Total Siswa</small>
            <h2><?= e($totalSiswa); ?></h2>
        </div>
        <div class="stat-icon"></div>
    </div>

    <!-- Total Guru -->
    <div class="card stat">
        <div>
            <small>Total Guru</small>
            <h2><?= e($totalGuru); ?></h2>
        </div>
        <div class="stat-icon"></div>
    </div>

    <!-- Hadir Hari Ini -->
    <div class="card stat">
        <div>
            <small>Hadir Hari Ini</small>
            <h2><?= e($hadir); ?></h2>
        </div>
        <div class="stat-icon"></div>
    </div>

    <!-- Belum Hadir -->
    <div class="card stat">
        <div>
            <small>Belum Hadir</small>
            <h2><?= e($belum); ?></h2>
        </div>
        <div class="stat-icon"></div>
    </div>
</div>

<!-- TABEL ABSENSI HARI INI -->
<div class="section">
    <div class="section-head">
        <h3>Absensi Hari Ini</h3>
        <a class="btn" href="scan.php">Scan QR</a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><?= e($r['nis']); ?></td>
                        <td><?= e($r['nama']); ?></td>
                        <td><?= e($r['kelas']); ?></td>
                        <td><?= e($r['jam_masuk'] ?: '-'); ?></td>
                        <td><?= e($r['jam_pulang'] ?: '-'); ?></td>
                        <td>
                            <span class="badge <?= strtolower(e($r['status'])); ?>">
                                <?= e($r['status']); ?>
                            </span>
                        </td>
                        <td><?= e($r['keterangan']); ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$recent): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #888;">
                            Belum ada absensi hari ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'partials/footer.php'; ?>