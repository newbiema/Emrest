<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Mailer.php';

use Emrest\Mailer;

$db = (new Database())->connect();
$pageTitle = "Kirim Ulang Verifikasi";

// CSRF
session_start();
if (empty($_SESSION['csrf_resend'])) {
  $_SESSION['csrf_resend'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf_resend'], $csrf)) {
    Alert::toast('error','Sesi tidak valid. Coba lagi.', Helper::baseUrl('portal/resend_verification.php'));
    exit;
  }

  $identifier = trim($_POST['identifier'] ?? ''); // username atau email
  if ($identifier === '') {
    Alert::toast('warning','Masukkan username atau email.', Helper::baseUrl('portal/resend_verification.php'));
    exit;
  }

  // Cari user by email atau username (hanya yang belum verified)
  if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
    $stmt = $db->prepare("SELECT id, nama, email FROM account WHERE email = ? AND verified = 0 LIMIT 1");
  } else {
    $stmt = $db->prepare("SELECT id, nama, email FROM account WHERE username = ? AND verified = 0 LIMIT 1");
  }
  $stmt->bind_param("s", $identifier);
  $stmt->execute();
  $res = $stmt->get_result();
  $acc = $res->fetch_assoc();
  $stmt->close();

  if (!$acc) {
    Alert::toast('warning','Akun tidak ditemukan atau sudah terverifikasi.', Helper::baseUrl('login.php'));
    exit;
  }

  if (empty($acc['email'])) {
    Alert::toast('warning','Akun belum memiliki email. Hubungi admin.', Helper::baseUrl('login.php'));
    exit;
  }

  // Buat token baru + expiry 60 menit
  $token = bin2hex(random_bytes(16));
  $exp   = (new DateTimeImmutable('now'))->modify('+60 minutes')->format('Y-m-d H:i:s');

  $upd = $db->prepare("UPDATE account SET verify_token=?, verify_expires=? WHERE id=?");
  $upd->bind_param("ssi", $token, $exp, $acc['id']);
  $ok = $upd->execute();
  $upd->close();

  if (!$ok) {
    Alert::toast('error','Gagal menyiapkan token. Coba lagi.', Helper::baseUrl('portal/resend_verification.php'));
    exit;
  }

  // Kirim email
  $mailer = new Mailer();
  if ($mailer->sendVerification($acc['email'], $acc['nama'] ?? 'Pengguna', $token)) {
    Alert::toast('success','Link verifikasi telah dikirim ke email Anda.', Helper::baseUrl('login.php'));
  } else {
    Alert::toast('error','Gagal mengirim email verifikasi. Coba lagi.', Helper::baseUrl('portal/resend_verification.php'));
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-1"><i class="fa-solid fa-envelope-circle-check text-blue-600"></i> Kirim Ulang Verifikasi</h1>
    <p class="text-gray-500 text-sm mb-4">Masukkan <b>email</b> atau <b>username</b> akun Anda.</p>

    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_resend']) ?>"/>

      <div>
        <label class="block text-gray-700 mb-1">Email atau Username</label>
        <input type="text" name="identifier" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400">
      </div>

      <div class="flex items-center justify-between">
        <a href="<?= Helper::baseUrl('login.php') ?>" class="text-gray-600 hover:underline">
          <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
          Kirim Link
        </button>
      </div>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
