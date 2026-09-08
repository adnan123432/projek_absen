<?php 
require_once 'config/database.php'; 
require_once 'config/auth.php'; 
require_login(); 

$title = 'Jadwal Kelas'; 

// ==========================================
// 1. PROSES HAPUS JADWAL (GET)
// ==========================================
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM jadwal WHERE id = ?")
        ->execute([(int)$_GET['hapus']]);
        
    flash('success', 'Jadwal dihapus.');
    header('Location: jadwal.php');
    exit;
} 

// ==========================================
// 2. PROSES TAMBAH JADWAL (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $kelas      = trim($_POST['kelas']);
    $hari       = $_POST['hari'];
    $mapel      = trim($_POST['mapel']);
    $jam_masuk  = $_POST['jam_masuk'];
    $jam_pulang = $_POST['jam_pulang']; 

    $pdo->prepare("INSERT INTO jadwal (kelas, hari, mapel, jam_masuk, jam_pulang) VALUES (?, ?, ?, ?, ?)")
        ->execute([$kelas, $hari, $mapel, $jam_masuk, $jam_pulang]); 

    flash('success', 'Jadwal berhasil ditambahkan.');
    header('Location: jadwal.php');
    exit; 
} 

// ==========================================
// 3. AMBIL DATA JADWAL
// ==========================================
$rows = $pdo->query("SELECT * FROM jadwal ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), kelas, jam_masuk")->fetchAll(); 

include 'partials/header.php'; 
?> 

<!-- ==========================================
     4. BAGIAN TAMPILAN (HTML)
     ========================================== -->
<div class="section-head">
    <h3>Jadwal Kelas</h3>
</div> 

<!-- Form Tambah Jadwal -->
<div class="form-card">
    <form method="post">
        <div class="grid2"> 
            <div class="field">
                <label>Kelas</label>
                <input name="kelas" placeholder="X IPA 1" required>
            </div> 
            
            <div class="field">
                <label>Hari</label>
                <select name="hari">
                    <option>Senin</option>
                    <option>Selasa</option>
                    <option>Rabu</option>
                    <option>Kamis</option>
                    <option>Jumat</option>
                    <option>Sabtu</option>
                </select>
            </div> 
            
            <div class="field">
                <label>Mata Pelajaran</label>
                <input name="mapel" required>
            </div> 
            
            <div class="field">
                <label>Jam Masuk</label>
                <input type="time" name="jam_masuk" value="07:00" required>
            </div> 
            
            <div class="field">
                <label>Jam Pulang</label>
                <input type="time" name="jam_pulang" value="15:00" required>
            </div> 
        </div>
        <button class="btn">Tambah Jadwal</button>
    </form>
</div> 

<!-- Tabel Data Jadwal -->
<div class="table-wrap section">
    <table class="table">
        <thead>
            <tr>
                <th>Hari</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody> 
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['hari']) ?></td>
                    <td><?= e($r['kelas']) ?></td>
                    <td><?= e($r['mapel']) ?></td>
                    <td><?= e($r['jam_masuk']) ?></td>
                    <td><?= e($r['jam_pulang']) ?></td>
                    <td>
                        <a href="?hapus=<?= $r['id'] ?>" onclick="return confirmDelete()">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?> 
        </tbody>
    </table>
</div> 

<?php include 'partials/footer.php'; ?>
