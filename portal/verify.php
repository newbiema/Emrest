<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';

session_start();
$db = (new Database())->connect();

$token = $_GET['token'] ?? '';
if ($token === '') {
  Alert::toast('error', 'Token tidak ditemukan.', Helper::baseUrl('login.php'));
  exit;
}

// Cari akun berdasarkan token & belum kadaluarsa
$sql = "SELECT id, verified, verify_expires FROM account WHERE verify_token=? LIMIT 1";
$st  = $db->prepare($sql);
$st->bind_param('s', $token);
$st->execute();
$res = $st->get_result();
$acc = $res->fetch_assoc();

if (!$acc) {
  Alert::toast('error', 'Token verifikasi tidak valid.', Helper::baseUrl('login.php'));
  exit;
}
if (strtotime($acc['verify_expires']) < time()) {
  Alert::toast('warning', 'Link verifikasi kadaluarsa. Kirim ulang verifikasi.', Helper::baseUrl('portal/resend_verification.php'));
  exit;
}
if ((int)$acc['verified'] === 1) {
  Alert::toast('info', 'Akun sudah terverifikasi. Silakan login.', Helper::baseUrl('login.php'));
  exit;
}

// Update: verified=1 dan hapus token
$upd = $db->prepare("UPDATE account SET verified=1, verify_token=NULL, verify_expires=NULL WHERE id=?");
$upd->bind_param('i', $acc['id']);
$upd->execute();

Alert::toast('success', 'Verifikasi berhasil! Silakan login.', Helper::baseUrl('login.php'));
exit;
