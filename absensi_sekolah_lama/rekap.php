<?php
// =========================================
// HALAMAN REKAP ABSENSI HARI INI
// Menampilkan siapa saja yang sudah hadir/terlambat/tidak hadir
// =========================================
require "db.php";

$tanggal = $_GET['tanggal'] ?? date("Y-m-d"); // bisa lihat tanggal lain lewat ?tanggal=2026-08-03
$filter_kelas = $_GET['kelas'] ?? '';

$sql = "SELECT s.nama, s.kelas, s.nisn, a.jam_scan, a.status
        FROM siswa s
        LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
        WHERE 1=1";
$params = [$tanggal];
$types = "s";

if (!empty($filter_kelas)) {
    $sql .= " AND s.kelas = ?";
    $params[] = $filter_kelas;
    $types .= "s";
}
$sql .= " ORDER BY s.kelas, s.nama";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result();

// Ambil daftar kelas untuk dropdown filter + hitung jumlah siswa per kelas
$daftar_kelas_result = $koneksi->query("SELECT kelas, COUNT(*) AS jumlah FROM siswa GROUP BY kelas ORDER BY kelas");
$daftar_kelas = [];
$jumlah_per_kelas = [];
while ($k = $daftar_kelas_result->fetch_assoc()) {
    $daftar_kelas[] = $k['kelas'];
    $jumlah_per_kelas[$k['kelas']] = (int)$k['jumlah'];
}
$total_kelas = count($daftar_kelas);
$total_siswa = array_sum($jumlah_per_kelas);

// Hitung ringkasan keseluruhan + ringkasan per kelas
$total_hadir = 0; $total_terlambat = 0; $total_tidak_hadir = 0; $total_belum = 0;
$baris = [];
$rekap_kelas = []; // rekap per kelas: hadir/terlambat/tidak hadir/belum

while ($row = $data->fetch_assoc()) {
    $baris[] = $row;
    $k = $row['kelas'];
    if (!isset($rekap_kelas[$k])) {
        $rekap_kelas[$k] = ['hadir' => 0, 'terlambat' => 0, 'tidak hadir' => 0, 'belum' => 0, 'total' => 0];
    }
    $rekap_kelas[$k]['total']++;

    switch ($row['status']) {
        case 'hadir': $total_hadir++; $rekap_kelas[$k]['hadir']++; break;
        case 'terlambat': $total_terlambat++; $rekap_kelas[$k]['terlambat']++; break;
        case 'tidak hadir': $total_tidak_hadir++; $rekap_kelas[$k]['tidak hadir']++; break;
        default: $total_belum++; $rekap_kelas[$k]['belum']++; // NULL = belum ada aksi
    }
}
$total_ditampilkan = count($baris);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Absensi</title>
<meta http-equiv="refresh" content="30"> <!-- auto refresh tiap 30 detik -->
<style>
  :root {
    --bg: #f1f5f9;
    --card: #ffffff;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --primary: #4f46e5;
    --hadir: #16a34a;
    --terlambat: #d97706;
    --tidak-hadir: #dc2626;
    --belum: #94a3b8;
  }
  * { box-sizing: border-box; }
  body {
    font-family: "Segoe UI", Arial, sans-serif;
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 16px 60px;
    background: var(--bg);
    color: var(--text);
  }
  .header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-end;
    gap: 12px;
    margin-bottom: 20px;
  }
  .header h2 { margin: 0 0 4px; font-size: 22px; }
  .header .sub { color: var(--muted); font-size: 14px; }

  form.filter {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
    background: var(--card);
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    margin-bottom: 20px;
  }
  .filter label {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
    color: var(--muted);
    font-weight: 600;
  }
  select, input[type="date"] {
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    font-size: 14px;
    min-width: 160px;
  }
  button {
    padding: 9px 18px;
    border-radius: 8px;
    border: none;
    background: var(--primary);
    color: white;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
  }
  button:hover { background: #4338ca; }

  /* Ringkasan utama */
  .ringkasan {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
  }
  .kotak {
    background: var(--card);
    padding: 16px 18px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border-left: 4px solid var(--border);
  }
  .kotak .label { font-size: 13px; color: var(--muted); font-weight: 600; }
  .kotak b { display: block; font-size: 26px; margin-top: 4px; }
  .kotak.kelas   { border-left-color: var(--primary); }
  .kotak.siswa   { border-left-color: #0891b2; }
  .kotak.hadir   { border-left-color: var(--hadir); }
  .kotak.terlambat { border-left-color: var(--terlambat); }
  .kotak.tidak-hadir { border-left-color: var(--tidak-hadir); }
  .kotak.belum   { border-left-color: var(--belum); }
  .kotak.kelas b   { color: var(--primary); }
  .kotak.siswa b   { color: #0891b2; }
  .kotak.hadir b   { color: var(--hadir); }
  .kotak.terlambat b { color: var(--terlambat); }
  .kotak.tidak-hadir b { color: var(--tidak-hadir); }
  .kotak.belum b   { color: var(--belum); }

  /* Rekap per kelas */
  .section-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 28px 0 12px;
  }
  .grid-kelas {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin-bottom: 8px;
  }
  .kartu-kelas {
    background: var(--card);
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  }
  .kartu-kelas .nama-kelas {
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 2px;
  }
  .kartu-kelas .jumlah-siswa {
    font-size: 12.5px;
    color: var(--muted);
    margin-bottom: 10px;
  }
  .mini-stat {
    display: flex;
    justify-content: space-between;
    font-size: 12.5px;
    padding: 3px 0;
    border-top: 1px dashed var(--border);
  }
  .mini-stat:first-of-type { border-top: none; }
  .mini-stat .n { font-weight: 700; }

  /* Tabel detail */
  table {
    width: 100%;
    border-collapse: collapse;
    background: var(--card);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  }
  th, td { padding: 11px 14px; text-align: left; border-bottom: 1px solid var(--border); font-size: 14px; }
  th { background: #1e293b; color: white; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.03em; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: #f8fafc; }

  .badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
  }
  .badge.hadir { background: #dcfce7; color: var(--hadir); }
  .badge.terlambat { background: #fef3c7; color: var(--terlambat); }
  .badge.tidak-hadir { background: #fee2e2; color: var(--tidak-hadir); }
  .badge.belum { background: #f1f5f9; color: var(--belum); }

  .footer-note {
    color: #94a3b8;
    font-size: 12.5px;
    margin-top: 16px;
  }

  @media (max-width: 600px) {
    .header { flex-direction: column; align-items: flex-start; }
    th, td { padding: 8px 10px; font-size: 13px; }
  }
</style>
</head>
<body>

<div class="header">
  <div>
    <h2>📋 Rekap Absensi Siswa</h2>
    <div class="sub"><?= htmlspecialchars(date("l, d F Y", strtotime($tanggal))) ?></div>
  </div>
</div>

<form class="filter" method="get">
  <label>Tanggal
    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
  </label>
  <label>Kelas
    <select name="kelas">
      <option value="">Semua Kelas (<?= $total_kelas ?>)</option>
      <?php foreach ($daftar_kelas as $k): ?>
        <option value="<?= htmlspecialchars($k) ?>" <?= $filter_kelas === $k ? 'selected' : '' ?>>
          <?= htmlspecialchars($k) ?> (<?= $jumlah_per_kelas[$k] ?> siswa)
        </option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit">Tampilkan</button>
</form>

<!-- Ringkasan umum -->
<div class="ringkasan">
  <div class="kotak kelas">
    <div class="label">Jumlah Kelas</div>
    <b><?= $total_kelas ?></b>
  </div>
  <div class="kotak siswa">
    <div class="label">Jumlah Siswa<?= $filter_kelas ? ' (kelas ini)' : '' ?></div>
    <b><?= $total_ditampilkan ?></b>
  </div>
  <div class="kotak hadir">
    <div class="label">Hadir</div>
    <b><?= $total_hadir ?></b>
  </div>
  <div class="kotak terlambat">
    <div class="label">Terlambat</div>
    <b><?= $total_terlambat ?></b>
  </div>
  <div class="kotak tidak-hadir">
    <div class="label">Tidak Hadir</div>
    <b><?= $total_tidak_hadir ?></b>
  </div>
  <div class="kotak belum">
    <div class="label">Belum Diproses</div>
    <b><?= $total_belum ?></b>
  </div>
</div>

<!-- Rekap ringkas per kelas -->
<?php if (!$filter_kelas && count($rekap_kelas) > 0): ?>
<div class="section-title">Ringkasan per Kelas</div>
<div class="grid-kelas">
  <?php foreach ($rekap_kelas as $namaKelas => $r): ?>
    <div class="kartu-kelas">
      <div class="nama-kelas"><?= htmlspecialchars($namaKelas) ?></div>
      <div class="jumlah-siswa"><?= $r['total'] ?> siswa</div>
      <div class="mini-stat"><span>Hadir</span><span class="n" style="color:var(--hadir)"><?= $r['hadir'] ?></span></div>
      <div class="mini-stat"><span>Terlambat</span><span class="n" style="color:var(--terlambat)"><?= $r['terlambat'] ?></span></div>
      <div class="mini-stat"><span>Tidak Hadir</span><span class="n" style="color:var(--tidak-hadir)"><?= $r['tidak hadir'] ?></span></div>
      <div class="mini-stat"><span>Belum Diproses</span><span class="n" style="color:var(--belum)"><?= $r['belum'] ?></span></div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="section-title">Detail Kehadiran</div>
<table>
  <tr>
    <th>Nama</th>
    <th>Kelas</th>
    <th>NISN</th>
    <th>Jam Scan</th>
    <th>Status</th>
  </tr>
  <?php foreach ($baris as $row): ?>
  <tr>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td><?= htmlspecialchars($row['kelas']) ?></td>
    <td><?= htmlspecialchars($row['nisn']) ?></td>
    <td><?= $row['jam_scan'] ?? '-' ?></td>
    <td>
      <?php
      $kelas_css = str_replace(' ', '-', $row['status'] ?? 'belum');
      $label = $row['status'] ?? 'belum absen';
      ?>
      <span class="badge <?= $kelas_css ?>"><?= htmlspecialchars(strtoupper($label)) ?></span>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<p class="footer-note">
  Halaman ini otomatis refresh tiap 30 detik &middot; Status "BELUM ABSEN" muncul kalau siswa memang belum scan
  DAN script <code>cron_absen.php</code> belum jalan untuk menandai tidak hadir.
</p>

</body>
</html>
