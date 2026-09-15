<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title   = 'Laporan Absensi';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas   = $_GET['kelas'] ?? '';

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

$kelasRows = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas")->fetchAll();

include 'partials/header.php';
?>

<style>
   
    .kop-surat,
    .judul-laporan,
    .ttd-wrap {
        display: none;
    }

    @media print {

        @page {
            size: A4 portrait;
            margin: 15mm 18mm;
        }

        .sidebar, .topbar, .filters, .section-head, .btn, nav {
            display: none !important;
        }

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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding-bottom: 8px;
            margin-bottom: 4px;
            border-bottom: 3px solid #000;
        }

        .kop-surat img {
            display: block;
            max-width: 480px;
            width: 65%;
            height: auto;
            margin: 0 auto;
        }

        .kop-surat + .garis-bawah-kop {
            display: block;
            border-bottom: 1px solid #000;
            margin-bottom: 16px;
        }

        .judul-laporan {
            display: block;
            text-align: center;
            margin: 14px 0 18px;
        }

        .judul-laporan h2 {
            margin: 0 0 4px;
            font-size: 15px;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .judul-laporan p {
            margin: 0;
            font-size: 11.5px;
        }

        .table-wrap {
            overflow: visible !important;
            margin: 0 auto !important;
            width: 100% !important;
            border: none !important;
            border-radius: 0 !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 0 auto !important;
            font-size: 11px !important;
        }

        table th, table td {
            border: 1px solid #000 !important;
            padding: 6px 8px !important;
        }

        table th {
            background: #eee !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .ttd-wrap {
            display: flex !important;
            justify-content: flex-end;
            margin-top: 45px;
            padding-right: 10px;
            width: 100%;
            font-size: 11.5px;
        }

        .ttd-box {
            text-align: center;
            width: 230px;
            line-height: 1.5;
        }

        .ttd-space {
            height: 70px;
        }
    }
</style>

<div class="kop-surat">
    <img src="picture/kop.png" alt="Kop Surat SMK Taruna Bangsa">
</div>
<span class="garis-bawah-kop"></span>

<div class="judul-laporan">
    <h2>Laporan Absensi Siswa</h2>
    <p>
        Tanggal: <?= e(date('d F Y', strtotime($tanggal))) ?>
        <?php if ($kelas !== ''): ?>
            &nbsp;|&nbsp; Kelas: <?= e($kelas) ?>
        <?php else: ?>
            &nbsp;|&nbsp; Kelas: Semua Kelas
        <?php endif; ?>
    </p>
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


<div class="ttd-wrap">
    <div class="ttd-box">
        <p style="margin: 0 0 2px;">Bekasi, <?= e(date('d F Y')) ?></p>
        <p style="margin: 0 0 2px;">Mengetahui,</p>
        <p style="margin: 0;">Kepala Sekolah</p>
        <div class="ttd-space"></div>
        <p style="margin: 0; font-weight: 600; text-decoration: underline;">..............................</p>
        <p style="margin: 2px 0 0;">NIP. ..........................</p>
    </div>
</div>

<?php include 'partials/footer.php'; ?>