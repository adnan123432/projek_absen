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
        <!-- SEKSI IDENTITAS SEKOLAH SISI KIRI -->
        <section class="login-info">
            <div class="school-logo-wrap">
                <img src="picture/logo_tb.jpg" alt="Logo SMK Taruna Bangsa" class="school-logo">
            </div>

            <h1 class="school-name">SMK TARUNA BANGSA</h1>
            <p class="school-city">BEKASI</p>

            <div class="school-divider"></div>

            <p class="school-tagline">Disiplin&nbsp;&bull;&nbsp;Berakhlak Mulia&nbsp;&bull;&nbsp;Terampil</p>

            <p class="school-desc">
                Sistem Informasi Absensi Digital untuk mengelola data siswa, guru,
                kehadiran harian, laporan, dan pemindaian QR Code dalam satu platform terpadu.
            </p>
        </section>

        <!-- SEKSI FORM LOGIN SISI KANAN -->
        <section class="login-form">
            <h2>Selamat Datang Kembali</h2>
            <p>Silakan masuk untuk mengelola sistem absensi sekolah.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= e($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label>Username</label>
                    <input name="username" required autofocus>
                </div>

                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Masuk ke Dashboard</button>
            </form>

            <p class="login-footnote">&copy; <?= date('Y') ?> SMK Taruna Bangsa Bekasi &mdash; Seluruh hak dilindungi.</p>
        </section>
    </div>
</body>
</html>