<?php
namespace Emrest;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  private function newMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    // DEBUG: tulis ke error_log (JANGAN di production lama-lama)
    $mail->SMTPDebug  = 0; // ubah 2 kalau mau lihat detail; 0 kalau sudah stabil
    $mail->Debugoutput = function($str, $level) {
      error_log("PHPMailer[$level]: $str");
    };

    // === KONFIGURASI SMTP ===
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // atau smtp.domainmu.com
    $mail->SMTPAuth   = true;
    $mail->Username   = 'evanjamaq123@gmail.com';
    $mail->Password   = 'paot hjfv kdbm piub'; // pakai App Password jika Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // atau PHPMailer::ENCRYPTION_SMTPS
    $mail->Port       = 587; // 465 untuk SMTPS

    // Pengirim
    $mail->setFrom('no-reply@domainmu.com', 'RS Emrest');

    // Format
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    return $mail;
  }

  /** Kirim email verifikasi */
  public function sendVerification(string $toEmail, string $toName, string $token, string $verifyUrlAbs = ''): bool {
    // Sanitasi email
    $toEmail = trim($toEmail);
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
      error_log("sendVerification(): Email tidak valid: $toEmail");
      return false;
    }

    // Pastikan URL absolut
    if ($verifyUrlAbs === '') {
      $verifyUrlAbs = $this->buildAbsoluteUrl('/emrest/portal/verify.php?token=' . urlencode($token));
    }

    try {
      $mail = $this->newMailer();

      // Tujuan
      $mail->clearAllRecipients();
      $mail->addAddress($toEmail, $toName ?: $toEmail);

      $mail->Subject = 'Verifikasi Akun RS Emrest';
      $mail->Body    = '
        <p>Halo <b>' . htmlspecialchars($toName ?: $toEmail) . '</b>,</p>
        <p>Terima kasih telah mendaftar di <b>RS Emrest</b>. Klik tombol di bawah untuk verifikasi akun:</p>
        <p><a href="' . htmlspecialchars($verifyUrlAbs) . '" 
              style="display:inline-block;padding:10px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px">
              Verifikasi Akun
            </a></p>
        <p>Atau buka tautan berikut:<br><a href="' . htmlspecialchars($verifyUrlAbs) . '">' . htmlspecialchars($verifyUrlAbs) . '</a></p>
        <p>Link berlaku 24 jam.</p>
        <hr>
        <p>Salam,<br>RS Emrest</p>
      ';

      $alt  = "Halo " . ($toName ?: $toEmail) . "\n\n";
      $alt .= "Klik link berikut untuk verifikasi akun:\n" . $verifyUrlAbs . "\n\n";
      $alt .= "Link berlaku 24 jam.\n\nRS Emrest\n";
      $mail->AltBody = $alt;

      return $mail->send();
    } catch (Exception $e) {
      error_log('sendVerification() error: ' . $e->getMessage());
      return false;
    }
  }

  /** Bangun absolute URL dari path relatif (mis. '/emrest/portal/verify.php?...') */
  private function buildAbsoluteUrl(string $path): string {
    // Kalau sudah absolute, kembalikan apa adanya
    if (preg_match('#^https?://#i', $path)) return $path;

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Pastikan path diawali slash
    if ($path === '' || $path[0] !== '/') $path = '/' . $path;

    return $scheme . '://' . $host . $path;
  }
}
