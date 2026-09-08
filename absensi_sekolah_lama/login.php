<?php
require_once 'config/database.php';
require_once 'config/auth.php';

if (!empty($_SESSION['admin_id'])) { 
    header('Location: index.php'); 
    exit; 
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $st->execute([$username]); 
    $u = $st->fetch();

    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['admin_id'] = $u['id']; 
        $_SESSION['admin_name'] = $u['nama'];
        
        header('Location: index.php'); 
        exit;
    }

    $error = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login E-Absensi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <!-- SEKSI INFORMASI SISI KIRI -->
        <section class="login-info">
            <div class="brand">
                <div class="brand-icon">A</div>
                <div>
                    <b>E-ABSENSI</b>
                    <small>SCHOOL SYSTEM</small>
                </div>
            </div>
            <h1>Sistem Absensi Digital</h1>
            <p>Kelola data siswa, guru, absensi harian, laporan dan scan QR Code dalam satu dashboard sekolah.</p>
        </section>

        <!-- SEKSI FORM LOGIN SISI KANAN -->
        <section class="login-form">
            <h2>Selamat Datang Kembali</h2>
            <p>Masuk untuk mengelola sistem absensi sekolah.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= e($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label>Username</label>
                    <input name="username" required>
                </div>

                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Masuk ke Dashboard</button>
            </form>

           
        </section>
    </div>
</body>
</html>