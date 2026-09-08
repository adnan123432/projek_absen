<?php
require_once 'config/database.php';
require_once 'config/auth.php';

require_login();

$title = 'Data Siswa';

// Hapus Data Siswa
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $pdo->prepare("DELETE FROM siswa WHERE id = ?")->execute([$id]);
    flash('success', 'Data siswa berhasil dihapus.');
    header('Location: siswa.php');
    exit;
}

// Simpan / Edit Data Siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis   = trim($_POST['nis']);
    $nama  = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);
    $jk    = $_POST['jk'];
    $id    = (int) ($_POST['id'] ?? 0);

    if ($id) {
        $st = $pdo->prepare("UPDATE siswa SET nis = ?, nama = ?, kelas = ?, jenis_kelamin = ? WHERE id = ?");
        $st->execute([$nis, $nama, $kelas, $jk, $id]);
        flash('success', 'Data siswa diperbarui.');
    } else {
        $st = $pdo->prepare("INSERT INTO siswa (nis, nama, kelas, jenis_kelamin) VALUES (?, ?, ?, ?)");
        $st->execute([$nis, $nama, $kelas, $jk]);
        flash('success', 'Siswa berhasil ditambahkan.');
    }

    header('Location: siswa.php');
    exit;
}

// Ambil Data untuk Edit
$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $st->execute([(int) $_GET['edit']]);
    $edit = $st->fetch();
}

// Pencarian dan Ambil Semua Data
$q    = trim($_GET['q'] ?? '');
$st   = $pdo->prepare("SELECT * FROM siswa WHERE nis LIKE ? OR nama LIKE ? OR kelas LIKE ? ORDER BY kelas, nama");
$like = "%$q%";
$st->execute([$like, $like, $like]);
$rows = $st->fetchAll();

include 'partials/header.php';
?>

<div class="section-head">
    <h3><?= $edit ? 'Edit Siswa' : 'Daftar Siswa' ?></h3>
    <a class="btn" href="siswa.php?tambah=1">+ Tambah Siswa</a>
</div>

<!-- FORM TAMBAH / EDIT -->
<?php if ($edit || isset($_GET['tambah'])): ?>
    <div class="form-card section">
        <form method="post">
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">

            <div class="grid2">
                <div class="field">
                    <label>NIS</label>
                    <input name="nis" value="<?= e($edit['nis'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Nama Lengkap</label>
                    <input name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Kelas</label>
                    <input name="kelas" placeholder="X IPA 1" value="<?= e($edit['kelas'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Jenis Kelamin</label>
                    <select name="jk">
                        <option value="L" <?= ($edit['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($edit['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Simpan</button>
                <a class="btn secondary" href="siswa.php">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- FORM PENCARIAN -->
<div class="filters section">
    <form method="get">
        <input name="q" value="<?= e($q) ?>" placeholder="Cari NIS, nama, kelas...">
        <button type="submit" class="btn">Cari</button>
    </form>
</div>

<!-- TABEL DATA SISWA -->
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>JK</th>
                <th>QR</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['kelas']) ?></td>
                    <td><?= e($r['jenis_kelamin']) ?></td>
                    <td>
                        <a class="btn secondary" href="kartu_qr.php?id=<?= $r['id'] ?>">Lihat QR</a>
                    </td>
                    <td>
                        <a href="?edit=<?= $r['id'] ?>">Edit</a> &nbsp; 
                        <a class="btn danger" onclick="return confirmDelete()" href="?hapus=<?= $r['id'] ?>">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #888;">
                        Data siswa tidak ditemukan.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>