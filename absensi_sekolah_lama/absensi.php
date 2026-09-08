<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_login();

$title = 'Kelola Absensi';

// =====================================================
// HAPUS DATA ABSENSI
// =====================================================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM absensi WHERE id = ?");
    $stmt->execute([$id]);

    flash('success', 'Data absensi dihapus.');
    header('Location: absensi.php');
    exit;
}

// =====================================================
// PROSES FORM ABSENSI
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $siswa = (int) ($_POST['siswa_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $status = $_POST['status'] ?? 'Hadir';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $jamMasuk = !empty($_POST['jam_masuk']) ? $_POST['jam_masuk'] : null;
    $jamPulang = !empty($_POST['jam_pulang']) ? $_POST['jam_pulang'] : null;

    if ($id) {
        // UPDATE DATA ABSENSI (EDIT)
        $stmt = $pdo->prepare("
            UPDATE absensi
            SET siswa_id = ?, tanggal = ?, jam_masuk = ?, jam_pulang = ?, status = ?, keterangan = ?
            WHERE id = ?
        ");
        $stmt->execute([$siswa, $tanggal, $jamMasuk, $jamPulang, $status, $keterangan, $id]);
    } else {
        // INPUT ABSENSI BARU / OVERWRITE
        $stmt = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$siswa, $tanggal]);
        $old = $stmt->fetchColumn();

        if ($old) {
            $stmt = $pdo->prepare("
                UPDATE absensi
                SET jam_masuk = ?, jam_pulang = ?, status = ?, keterangan = ?
                WHERE id = ?
            ");
            $stmt->execute([$jamMasuk, $jamPulang, $status, $keterangan, $old]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO absensi (siswa_id, tanggal, jam_masuk, jam_pulang, status, keterangan)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$siswa, $tanggal, $jamMasuk, $jamPulang, $status, $keterangan]);
        }
    }

    flash('success', 'Absensi berhasil disimpan.');
    header('Location: absensi.php');
    exit;
}

// =====================================================
// AMBIL DATA ABSENSI TERPILIH (EDIT MODE)
// =====================================================
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE id = ?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch();
}

// =====================================================
// AMBIL DATA KELOLA TABEL ABSENSI & SISWA
// =====================================================
$stmt = $pdo->query("
    SELECT a.*, s.nis, s.nama, s.kelas
    FROM absensi a
    INNER JOIN siswa s ON s.id = a.siswa_id
    ORDER BY a.tanggal DESC, a.id DESC
    LIMIT 100
");
$rows = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM siswa ORDER BY kelas, nama");
$siswa = $stmt->fetchAll();

include 'partials/header.php';
?>

<!-- JUDUL HALAMAN -->
<div class="section-head">
    <h3><?= $edit ? 'Edit Absensi' : 'Input / Koreksi Absensi'; ?></h3>
    <a class="btn" href="absensi.php">+ Input Baru</a>
</div>

<!-- FORM ABSENSI -->
<div class="form-card">
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0); ?>">

        <div class="grid2">
            <!-- SISWA -->
            <div class="field">
                <label>Siswa</label>
                <select name="siswa_id" required>
                    <option value="">Pilih siswa</option>
                    <?php foreach ($siswa as $x): ?>
                        <option value="<?= e($x['id']); ?>" <?= ((int) ($edit['siswa_id'] ?? 0) === (int) $x['id']) ? 'selected' : ''; ?>>
                            <?= e($x['nis'] . ' - ' . $x['nama'] . ' (' . $x['kelas'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- TANGGAL -->
            <div class="field">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= e($edit['tanggal'] ?? date('Y-m-d')); ?>" required>
            </div>

            <!-- JAM MASUK -->
            <div class="field">
                <label>Jam Masuk</label>
                <input type="time" step="1" name="jam_masuk" value="<?= e($edit['jam_masuk'] ?? ''); ?>">
            </div>

            <!-- JAM PULANG -->
            <div class="field">
                <label>Jam Pulang</label>
                <input type="time" step="1" name="jam_pulang" value="<?= e($edit['jam_pulang'] ?? ''); ?>">
            </div>

            <!-- STATUS -->
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <?php 
                    $statusList = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
                    foreach ($statusList as $statusItem): 
                    ?>
                        <option value="<?= e($statusItem); ?>" <?= (($edit['status'] ?? 'Hadir') === $statusItem) ? 'selected' : ''; ?>>
                            <?= e($statusItem); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- KETERANGAN -->
            <div class="field">
                <label>Keterangan</label>
                <input type="text" name="keterangan" value="<?= e($edit['keterangan'] ?? ''); ?>">
            </div>
        </div>

        <!-- TOMBOL AKSI -->
        <div class="actions">
            <button type="submit" class="btn">Simpan Absensi</button>
            <?php if ($edit): ?>
                <a class="btn secondary" href="absensi.php">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABEL DATA ABSENSI -->
<div class="table-wrap section">
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['tanggal']); ?></td>
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
                    <td>
                        <a href="?edit=<?= e($r['id']); ?>">Edit</a>
                        &nbsp;
                        <a href="?hapus=<?= e($r['id']); ?>" onclick="return confirmDelete()">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #888;">
                        Belum ada data absensi.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>