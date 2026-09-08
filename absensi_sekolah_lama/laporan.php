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

<style>
    /* Kop surat & TTD disembunyikan di layar biasa, hanya muncul saat print */
    .kop-surat {
        display: none;
    }

    .ttd-wrap {
        display: none;
    }

    @media print {
        /* Sembunyikan elemen dashboard yang tidak perlu saat dicetak */
        .sidebar, .topbar, .filters, .section-head, .btn, nav {
            display: none !important;
        }

        /* Reset margin/padding container utama supaya tidak mengikuti layout sidebar */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #fff !important;
        }

        .app {
            display: block !important;
        }

        .main {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .content {
            padding: 0 !important;
            margin: 0 auto !important;
            max-width: 100% !important;
        }

        .kop-surat {
            display: block;
            text-align: center;
            margin: 0 auto 20px auto;
        }

        .kop-surat img {
            display: block;
            width: 100%;
            max-width: 800px;
            height: auto;
            margin: 0 auto;
        }

        .ttd-wrap {
            display: flex !important;
            justify-content: flex-end;
            margin-top: 60px;
            padding-right: 40px;
            width: 100%;
        }

        .ttd-box {
            text-align: center;
            width: 250px;
        }

        .ttd-space {
            height: 80px;
        }

        .table-wrap {
            overflow: visible !important;
            margin: 0 auto !important;
            width: 100% !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 0 auto !important;
        }

        table th, table td {
            border: 1px solid #000 !important;
        }
    }
</style>

<!-- KOP SURAT: hanya tampil saat cetak -->
<div class="kop-surat">
    <img src="picture/kop.png" alt="Kop Surat SMK Taruna Bangsa">
</div>

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

<!-- TTD KEPALA SEKOLAH: hanya tampil saat cetak -->
<div class="ttd-wrap">
    <div class="ttd-box">
        <p><?= date('d F Y') ?></p>
        <p>Mengetahui,<br>Kepala Sekolah</p>
        <div class="ttd-space"></div>
        <p><strong><u>____________________</u></strong><br>
        NIP. ..........................</p>
    </div>
</div>

<?php include 'partials/footer.php'; ?>