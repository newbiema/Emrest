<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Mailer.php';
use Emrest\Mailer;

session_start();
$db = (new Database())->connect();

$email = mb_strtolower(trim($_GET['email'] ?? $_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Alert::toast('warning', 'Email tidak valid.', Helper::baseUrl('portal/resend_verification.php'));
    exit;
  }

  // Cari akun berdasarkan email
  $st = $db->prepare("SELECT id, nama, verified FROM account WHERE LOWER(email)=LOWER(?) LIMIT 1");
  $st->bind_param('s', $email);
  $st->execute();
  $acc = $st->get_result()->fetch_assoc();

  if (!$acc) {
    Alert::toast('error', 'Email tidak terdaftar.', Helper::baseUrl('portal/resend_verification.php'));
    exit;
  }
  if ((int)$acc['verified'] === 1) {
    Alert::toast('info', 'Akun sudah terverifikasi. Silakan login.', Helper::baseUrl('login.php'));
    exit;
  }

  // Generate token baru
  $token = bin2hex(random_bytes(16));
  $exp   = date('Y-m-d H:i:s', time() + 3600 * 24);
  $up = $db->prepare("UPDATE account SET verify_token=?, verify_expires=? WHERE id=?");
  $up->bind_param('ssi', $token, $exp, $acc['id']);
  $up->execute();

  // Kirim email
  $mailer = new Mailer();
  if ($mailer->sendVerification($email, $acc['nama'], $token)) {
    Alert::toast('success', 'Link verifikasi telah dikirim ulang. Silakan cek email.', Helper::baseUrl('login.php?check_email=1&email=' . urlencode($email)));
  } else {
    Alert::toast('error', 'Gagal mengirim ulang verifikasi. Coba beberapa saat lagi.', Helper::baseUrl('portal/resend_verification.php'));
  }
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kirim Ulang Verifikasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
  <form method="POST" class="bg-white rounded-xl shadow p-6 w-full max-w-md space-y-3">
    <h1 class="text-xl font-bold text-gray-800">Kirim Ulang Verifikasi</h1>
    <p class="text-sm text-gray-500">Masukkan email yang kamu daftarkan.</p>
    <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>" class="w-full border rounded-lg px-3 py-2" placeholder="nama@contoh.com">
    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">Kirim Ulang</button>
    <p class="text-center text-sm text-gray-500">Kembali ke <a class="text-blue-600 hover:underline" href="<?= Helper::baseUrl('login.php')?>">Login</a></p>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
