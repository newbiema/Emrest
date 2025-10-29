<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';

$db = (new Database())->connect();

$token = $_GET['token'] ?? '';

if (!$token || strlen($token) < 16) {
  Alert::toast('error','Token verifikasi tidak valid.', Helper::baseUrl('login.php'));
  exit;
}

$stmt = $db->prepare("SELECT id, verified, verify_expires FROM account WHERE verify_token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
  Alert::toast('error', 'Token verifikasi tidak ditemukan.', Helper::baseUrl('login.php'));
  exit;
}

if ((int)$user['verified'] === 1) {
  // sudah terverifikasi
  Alert::toast('info', 'Akun sudah terverifikasi. Silakan masuk.', Helper::baseUrl('login.php'));
  exit;
}

// cek kedaluwarsa
$now = new DateTimeImmutable('now');
$exp = new DateTimeImmutable($user['verify_expires'] ?? '1970-01-01 00:00:00');
if ($now > $exp) {
  Alert::toast('warning','Token verifikasi sudah kedaluwarsa. Silakan kirim ulang verifikasi.', Helper::baseUrl('portal/resend_verification.php'));
  exit;
}

// set verified
$upd = $db->prepare("UPDATE account SET verified=1, verify_token=NULL, verify_expires=NULL WHERE id=?");
$upd->bind_param("i", $user['id']);
$ok = $upd->execute();
$upd->close();

if ($ok) {
  Alert::toast('success','Verifikasi berhasil. Silakan masuk.', Helper::baseUrl('login.php'));
} else {
  Alert::toast('error','Gagal memverifikasi akun. Coba lagi.', Helper::baseUrl('portal/resend_verification.php'));
}
