<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title   = 'Laporan Absensi';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas   = $_GET['kelas'] ?? '';
$status  = $_GET['status'] ?? ''; // '', 'hadir', 'terlambat', 'tidak_masuk'

// LEFT JOIN dari siswa supaya siswa yang belum absen tetap muncul
$sql = "SELECT s.nis, s.nama, s.kelas,
               a.id AS absen_id, a.jam_masuk, a.jam_pulang, a.status, a.keterangan
        FROM siswa s
        LEFT JOIN absensi a ON a.siswa_id = s.id AND a.tanggal = ?
        WHERE 1=1";

$params = [$tanggal];

if ($kelas !== '') {
    $sql .= " AND s.kelas = ?";
    $params[] = $kelas;
}

if ($status === 'tidak_masuk') {
    // Belum ada record absensi sama sekali di tanggal ini
    $sql .= " AND a.id IS NULL";
} elseif ($status !== '') {
    // 'hadir' atau 'terlambat' -> cocokkan ke kolom status yang tersimpan
    $sql .= " AND a.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY s.kelas, s.nama";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$kelasRows = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas")->fetchAll();

include 'partials/header.php';
?>

<style>
    .table-wrap {
        overflow-x: auto;
    }

    .table-wrap table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .table-wrap table th,
    .table-wrap table td {
        vertical-align: middle;
        padding: 12px 16px;
        text-align: left;
    }

    .table-wrap table th.col-tight,
    .table-wrap table td.col-tight {
        white-space: nowrap;
        width: 1%;
    }

    .table-wrap table thead th {
        font-size: 12.5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .table-wrap table tbody tr {
        border-bottom: 1px solid #f1f1f4;
    }

    .table-wrap table tbody tr:hover {
        background: #fafafa;
    }

    .badge {
        display: inline-block;
        white-space: nowrap;
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 12.5px;
        background: #f1f1f4;
        color: #4b5563;
    }

    .badge.hadir {
        background: #e6f6ea;
        color: #1e8e3e;
    }

    .badge.terlambat {
        background: #fef3e2;
        color: #b45309;
    }

    .badge.tidak_masuk {
        background: #fdecec;
        color: #c0392b;
    }

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
        <?php if ($status !== ''): ?>
            &nbsp;|&nbsp; Status: <?= e(ucfirst(str_replace('_', ' ', $status))) ?>
        <?php endif; ?>
    </p>
</div>

<div class="section-head">
    <h3>Rekap Kehadiran</h3>
    <div>
        <a class="btn secondary" href="export_csv.php?tanggal=<?= e($tanggal) ?>&kelas=<?= urlencode($kelas) ?>&status=<?= urlencode($status) ?>">Export CSV</a>
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

    <select name="status">
        <option value="">Semua Status</option>
        <option value="hadir" <?= ($status === 'hadir' ? 'selected' : '') ?>>Hadir</option>
        <option value="terlambat" <?= ($status === 'terlambat' ? 'selected' : '') ?>>Terlambat</option>
        <option value="tidak_masuk" <?= ($status === 'tidak_masuk' ? 'selected' : '') ?>>Tidak Masuk / Belum Absen</option>
    </select>
    
    <button class="btn">Tampilkan</button>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="col-tight">No</th>
                <th class="col-tight">NIS</th>
                <th>Nama</th>
                <th class="col-tight">Kelas</th>
                <th class="col-tight">Jam Masuk</th>
                <th class="col-tight">Jam Pulang</th>
                <th class="col-tight">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <?php
                    $belumAbsen  = ($r['absen_id'] === null);
                    $statusLower = $belumAbsen ? 'tidak_masuk' : strtolower($r['status']);
                    $statusLabel = $belumAbsen ? 'Tidak Masuk' : $r['status'];
                    $badgeClass  = $statusLower;

                    if ($belumAbsen) {
                        $ket = 'Belum melakukan absensi.';
                    } elseif ($statusLower === 'hadir') {
                        $ket = $r['keterangan'] ?: 'Hadir tepat waktu.';
                    } else {
                        $ket = $r['keterangan'] ?: '-';
                    }
                ?>
                <tr>
                    <td class="col-tight"><?= $i + 1 ?></td>
                    <td class="col-tight"><?= e($r['nis']) ?></td>
                    <td><?= e($r['nama']) ?></td>
                    <td class="col-tight"><?= e($r['kelas']) ?></td>
                    <td class="col-tight"><?= e($r['jam_masuk'] ?: '-') ?></td>
                    <td class="col-tight"><?= e($r['jam_pulang'] ?: '-') ?></td>
                    <td class="col-tight">
                        <span class="badge <?= e($badgeClass) ?>">
                            <?= e($statusLabel) ?>
                        </span>
                    </td>
                    <td><?= e($ket) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #888;">
                        Belum ada data siswa untuk ditampilkan.
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