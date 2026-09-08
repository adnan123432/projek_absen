<?php 
require_once __DIR__ . '/../config/auth.php'; 
require_login(); 
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
            <div class="brand-icon">A</div>
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
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"> <span>Dashboard</span></a>
            <a href="siswa.php" class="<?= basename($_SERVER['PHP_SELF']) === 'siswa.php' ? 'active' : '' ?>"> <span>Data Siswa</span></a>
            <a href="guru.php" class="<?= basename($_SERVER['PHP_SELF']) === 'guru.php' ? 'active' : '' ?>"> <span>Data Guru</span></a>
            <a href="laporan.php" class="<?= basename($_SERVER['PHP_SELF']) === 'laporan.php' ? 'active' : '' ?>"> <span>Laporan</span></a>
            <a href="absensi.php" class="<?= basename($_SERVER['PHP_SELF']) === 'absensi.php' ? 'active' : '' ?>"> <span>Kelola Absen</span></a>
            <a href="jadwal.php" class="<?= basename($_SERVER['PHP_SELF']) === 'jadwal.php' ? 'active' : '' ?>"> <span>Jadwal</span></a>
            <a href="rekap_bulanan.php" class="<?= basename($_SERVER['PHP_SELF']) === 'rekap_bulanan.php' ? 'active' : '' ?>"> <span>Rekap Bulanan</span></a>
            <a href="pengaturan.php" class="<?= basename($_SERVER['PHP_SELF']) === 'pengaturan.php' ? 'active' : '' ?>"> <span>Pengaturan</span></a>
            <a href="scan.php" class="<?= basename($_SERVER['PHP_SELF']) === 'scan.php' ? 'active' : '' ?>"> <span>Scan Absensi</span></a>
        </nav>

        <a class="logout" href="logout.php">↪ Keluar Aplikasi</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="page-title"><?= e($title ?? 'Dashboard') ?></div>
            <div class="today"><?= date('l, d F Y') ?> <span>◔</span></div>
        </header>

        <div class="content">
        <?php show_flash(); ?>