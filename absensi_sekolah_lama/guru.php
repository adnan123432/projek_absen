<?php 
require_once 'config/database.php'; 
require_once 'config/auth.php'; 
require_login(); 

$title = 'Data Guru'; 

// ==========================================
// 1. PROSES HAPUS DATA (GET)
// ==========================================
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM guru WHERE id = ?")
        ->execute([(int)$_GET['hapus']]);
        
    flash('success', 'Data guru dihapus.');
    header('Location: guru.php');
    exit;
} 

// ==========================================
// 2. PROSES SIMPAN / UPDATE DATA (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id'] ?? 0);
    $nip   = trim($_POST['nip']);
    $nama  = trim($_POST['nama']);
    $mapel = trim($_POST['mapel']);

    if ($id) {
        // Mode Update
        $pdo->prepare("UPDATE guru SET nip = ?, nama = ?, mapel = ? WHERE id = ?")
            ->execute([$nip, $nama, $mapel, $id]);
    } else {
        // Mode Insert Data Baru
        $pdo->prepare("INSERT INTO guru (nip, nama, mapel) VALUES (?, ?, ?)")
            ->execute([$nip, $nama, $mapel]);
    }

    flash('success', 'Data guru tersimpan.');
    header('Location: guru.php');
    exit;
} 

// ==========================================
// 3. AMBIL DATA UNTUK FORM & TABEL
// ==========================================
$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM guru WHERE id = ?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}

$rows = $pdo->query("SELECT * FROM guru ORDER BY nama")->fetchAll();

include 'partials/header.php'; 
?> 

<!-- ==========================================
     4. BAGIAN TAMPILAN (HTML)
     ========================================== -->
<div class="section-head">
    <h3>Data Guru</h3>
    <a class="btn" href="?tambah=1">+ Tambah Guru</a>
</div> 

<!-- Form Tambah / Edit Guru -->
<?php if ($edit || isset($_GET['tambah'])): ?>
    <div class="form-card section">
        <form method="post">
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">
            
            <div class="grid2">
                <div class="field">
                    <label>NIP</label>
                    <input name="nip" value="<?= e($edit['nip'] ?? '') ?>">
                </div>
                
                <div class="field">
                    <label>Nama</label>
                    <input name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="field">
                <label>Mata Pelajaran</label>
                <input name="mapel" value="<?= e($edit['mapel'] ?? '') ?>">
            </div>
            
            <div class="actions">
                <button class="btn">Simpan</button>
                <a class="btn secondary" href="guru.php">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?> 

<!-- Tabel Data Guru -->
<div class="table-wrap section">
    <table class="table">
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama</th>
                <th>Mapel</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nip']) ?></td>
                    <td><?= e($r['nama']) ?></td>
                    <td><?= e($r['mapel']) ?></td>
                    <td>
                        <a href="?edit=<?= $r['id'] ?>">Edit</a> 
                        &nbsp;|&nbsp; 
                        <a href="?hapus=<?= $r['id'] ?>" onclick="return confirmDelete()">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 

<?php include 'partials/footer.php'; ?>
