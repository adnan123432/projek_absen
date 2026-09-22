<?php
// =========================================
// KONFIGURASI SMTP UNTUK PENGIRIMAN EMAIL
// =========================================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'coganmasrid@gmail.com');
define('SMTP_PASS', 'kymrlkbxwyvjzbnv');
define('FROM_NAME', 'E-Absensi Sekolah');

/**
 * Membuat instance PHPMailer yang sudah dikonfigurasi SMTP-nya.
 * Butuh file PHPMailer di: lib/PHPMailer/PHPMailer.php, lib/PHPMailer/SMTP.php, lib/PHPMailer/Exception.php
 */
function buatMailer(): PHPMailer\PHPMailer\PHPMailer
{
    require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
    require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_USER, FROM_NAME);

    return $mail;
}