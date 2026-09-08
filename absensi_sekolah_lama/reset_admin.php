<?php
require_once 'config/database.php';
$newHash = password_hash('admin123', PASSWORD_DEFAULT);
$st = $pdo->prepare("UPDATE users SET password=? WHERE username='admin'");
$st->execute([$newHash]);
echo '<h2>Password admin berhasil direset.</h2>';
echo '<p>Username: <b>admin</b><br>Password: <b>admin123</b></p>';
echo '<p><b>Penting:</b> hapus file <code>reset_admin.php</code> setelah berhasil.</p>';
