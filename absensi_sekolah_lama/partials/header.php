<?php 
require_once __DIR__ . '/../config/auth.php'; 
require_login(); 

$current = basename($_SERVER['PHP_SELF']);
function nav_active($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'E-ABSENSI') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <img src="picture/logo_tb.jpg" alt="Logo">
            </div>
            <div>
                <b>E-ABSENSI</b>
                <small>SCHOOL SYSTEM</small>
            </div>
        </div>

        <div class="profile">
            <div class="avatar">A</div>
            <div>
                <b><?= e($_SESSION['admin_name'] ?? 'Administrator') ?></b>
                <small>ADMIN</small>
            </div>
        </div>

        <nav>
            <div class="nav-group">
                <p class="nav-group-title">Utama</p>
                <a href="index.php" class="<?= nav_active('index.php', $current) ?>">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="scan.php" class="<?= nav_active('scan.php', $current) ?>">
                    <span class="nav-icon">📷</span>
                    <span>Scan Absensi</span>
                </a>
            </div>

            <div class="nav-group">
                <p class="nav-group-title">Data Master</p>
                <a href="siswa.php" class="<?= nav_active('siswa.php', $current) ?>">
                    <span class="nav-icon">🎓</span>
                    <span>Data Siswa</span>
                </a>
                <a href="guru.php" class="<?= nav_active('guru.php', $current) ?>">
                    <span class="nav-icon">🧑‍🏫</span>
                    <span>Data Guru</span>
                </a>
            </div>

            <div class="nav-group">
                <p class="nav-group-title">Absensi &amp; Laporan</p>
                <a href="absensi.php" class="<?= nav_active('absensi.php', $current) ?>">
                    <span class="nav-icon">🗂️</span>
                    <span>Kelola Absen</span>
                </a>
                <a href="jadwal.php" class="<?= nav_active('jadwal.php', $current) ?>">
                    <span class="nav-icon">📅</span>
                    <span>Jadwal</span>
                </a>
                <a href="rekap_bulanan.php" class="<?= nav_active('rekap_bulanan.php', $current) ?>">
                    <span class="nav-icon">📊</span>
                    <span>Rekap Bulanan</span>
                </a>
                <a href="laporan.php" class="<?= nav_active('laporan.php', $current) ?>">
                    <span class="nav-icon">📄</span>
                    <span>Laporan</span>
                </a>
            </div>

            <div class="nav-group">
                <p class="nav-group-title">Sistem</p>
                <a href="pengaturan.php" class="<?= nav_active('pengaturan.php', $current) ?>">
                    <span class="nav-icon">⚙️</span>
                    <span>Pengaturan</span>
                </a>
            </div>
        </nav>

        <a class="logout" href="logout.php">
            <span class="nav-icon">↪</span>
            <span>Keluar Aplikasi</span>
        </a>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="page-title"><?= e($title ?? 'Dashboard') ?></div>
            <div class="today"><?= date('l, d F Y') ?> <span>◔</span></div>
        </header>

        <div class="content">
        <?php show_flash(); ?>