<?php
namespace Emrest;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
  private PHPMailer $mail;

  public function __construct(array $cfg = []) {
    // load config
    $configFile = __DIR__ . '/../config/mail.php';
    $conf = file_exists($configFile) ? require $configFile : [];
    $conf = array_merge($conf, $cfg);

    $this->mail = new PHPMailer(true);
    try {
      $this->mail->isSMTP();
      $this->mail->SMTPAuth   = true;
      $this->mail->Host       = $conf['host'] ?? 'smtp.gmail.com';
      $this->mail->Port       = (int)($conf['port'] ?? 587);
      $this->mail->Username   = $conf['username'] ?? '';
      $this->mail->Password   = $conf['password'] ?? '';
      $this->mail->SMTPSecure = ($conf['encryption'] ?? 'tls') === 'ssl'
                                ? PHPMailer::ENCRYPTION_SMTPS
                                : PHPMailer::ENCRYPTION_STARTTLS;

      $this->mail->SMTPDebug  = (int)($conf['smtp_debug'] ?? 0);

      $fromEmail = $conf['from_email'] ?? 'noreply@example.com';
      $fromName  = $conf['from_name']  ?? 'No-Reply';
      $this->mail->setFrom($fromEmail, $fromName);

      if (!empty($conf['reply_to'])) {
        $this->mail->addReplyTo($conf['reply_to'], $conf['reply_name'] ?? $conf['reply_to']);
      }

      $this->mail->isHTML(true);
      $this->mail->CharSet = 'UTF-8';
    } catch (Exception $e) {
      error_log('Mailer init error: ' . $e->getMessage());
    }
  }

  public function sendVerification(string $to, string $nama, string $token): bool {
    $base = $this->baseUrl();
    $link = rtrim($base, '/') . '/portal/verify.php?token=' . urlencode($token);

    $subject = 'Verifikasi Akun RS Emrest';
    $body = '
      <div style="font-family:Arial,sans-serif;color:#333;line-height:1.5">
        <h2 style="color:#2563eb;margin:0 0 12px">Verifikasi Akun</h2>
        <p>Halo <b>'.htmlspecialchars($nama).'</b>,</p>
        <p>Terima kasih telah mendaftar di <b>RS Emrest</b>. Klik tombol berikut untuk memverifikasi akun Anda:</p>
        <p style="margin:18px 0">
          <a href="'.$link.'" style="background:#2563eb;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;display:inline-block">Verifikasi Sekarang</a>
        </p>
        <p>Atau salin link ini:</p>
        <p><a href="'.$link.'">'.$link.'</a></p>
        <p style="font-size:12px;color:#666">Link ini berlaku 24 jam.</p>
      </div>
    ';

    try {
      $this->mail->clearAddresses();
      $this->mail->Subject = $subject;
      $this->mail->Body    = $body;
      $this->mail->AltBody = "Halo $nama,\n\nBuka link verifikasi berikut:\n$link\n\nLink berlaku 24 jam.";
      $this->mail->addAddress($to, $nama);

      return $this->mail->send();
    } catch (Exception $e) {
      error_log('Mailer send error: ' . $e->getMessage());
      return false;
    }
  }

  // Helper kecil kalau kamu belum punya Helper::baseUrl()
  private function baseUrl(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    $scheme = $https ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Jika proyek di subfolder, ganti '/emrest' sesuai
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    return $scheme . $host . ($base === '/' ? '' : $base);
  }
}
