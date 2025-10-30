<?php
require_once __DIR__ . '/../services/Database.php';
require_once __DIR__ . '/../services/Helper.php';
require_once __DIR__ . '/../services/Alert.php';
require_once __DIR__ . '/../services/Mailer.php';
use Emrest\Mailer;

session_start();
$db = (new Database())->connect();

$pageTitle = "Registrasi Pasien";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Normalisasi input
  $username = trim($_POST['username'] ?? '');
  $email    = mb_strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';
  $nama     = trim($_POST['nama'] ?? '');
  $telp     = trim($_POST['telp'] ?? '');
  $alamat   = trim($_POST['alamat'] ?? '');

  // Validasi dasar
  if ($username === '' || $email === '' || $password === '' || $nama === '') {
    Alert::toast('warning', 'Semua field bertanda * wajib diisi.', Helper::baseUrl('portal/register.php'));
    exit;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Alert::toast('warning', 'Format email tidak valid.', Helper::baseUrl('portal/register.php'));
    exit;
  }
  if (strlen($password) < 6) {
    Alert::toast('warning', 'Password minimal 6 karakter.', Helper::baseUrl('portal/register.php'));
    exit;
  }
  // (opsional) batasi karakter username
  if (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
    Alert::toast('warning', 'Username hanya huruf/angka/titik/garis bawah (3-30).', Helper::baseUrl('portal/register.php'));
    exit;
  }

  // Pastikan username/email unik (case-insensitive)
  $q = $db->prepare("SELECT 1 FROM account WHERE LOWER(username)=LOWER(?) OR LOWER(email)=LOWER(?) LIMIT 1");
  $q->bind_param("ss", $username, $email);
  $q->execute();
  if ($q->get_result()->num_rows) {
    Alert::toast('error', 'Username atau email sudah terpakai.', Helper::baseUrl('portal/register.php'));
    exit;
  }
  $q->close();

  // Siapkan data
  $hash  = password_hash($password, PASSWORD_BCRYPT);
  $level = 'user';
  $ver   = 0;
  $token = bin2hex(random_bytes(16));
  $exp   = date('Y-m-d H:i:s', time() + 3600 * 24); // 24 jam

  // Transaksi: insert account → insert pasien → update account(pasien_id?) [kita simpan link via pasien.account_id]
  $db->begin_transaction();
  try {
    // 1) Insert account user
    $stmt = $db->prepare("INSERT INTO account (username,password,email,nama,level,verified,verify_token,verify_expires,created_at)
                          VALUES (?,?,?,?,?,?,?,?,NOW())");
    $stmt->bind_param("ssssisss", $username, $hash, $email, $nama, $level, $ver, $token, $exp);
    if (!$stmt->execute()) throw new Exception('Gagal membuat akun.');
    $accountId = $db->insert_id;
    $stmt->close();

    // 2) Insert pasien minimal (link account_id)
    $pasienIdGen = 'PS' . date('ymd') . sprintf("%04d", $accountId);
    $insP = $db->prepare("INSERT INTO pasien (pasien_id, pasien_nama, alamat, telp, tanggal_daftar, account_id) 
                          VALUES (?,?,?,?, CURDATE(), ?)");
    $insP->bind_param("ssssi", $pasienIdGen, $nama, $alamat, $telp, $accountId);
    if (!$insP->execute()) throw new Exception('Gagal membuat data pasien.');
    $insP->close();

    // Commit transaksi DB
    $db->commit();

    // 3) Kirim email verifikasi
    $mailer = new Mailer();
    $okMail = $mailer->sendVerification($email, $nama, $token);

    if ($okMail) {
      Alert::toast('success', 'Registrasi berhasil. Cek email untuk verifikasi, lalu login.', Helper::baseUrl('login.php'));
    } else {
      // akun dibuat + token tersimpan, tapi email gagal dikirim → arahkan ke resend
      Alert::toast('warning', 'Akun dibuat, tapi pengiriman email verifikasi gagal. Kirim ulang verifikasi.', Helper::baseUrl('portal/resend_verification.php'));
    }
    exit;

  } catch (Throwable $e) {
    $db->rollback();
    error_log('Register error: '.$e->getMessage());
    Alert::toast('error', 'Registrasi gagal. Coba lagi.', Helper::baseUrl('portal/register.php'));
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style> body{font-family:'Poppins',sans-serif;} </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white border rounded-2xl shadow-sm w-full max-w-lg p-6">
    <div class="mb-4 text-center">
      <i class="fa-solid fa-user-plus text-blue-600 text-3xl"></i>
      <h1 class="text-xl font-bold mt-2">Registrasi Pasien</h1>
      <p class="text-gray-500 text-sm">Buat akun portal untuk pendaftaran & pemantauan</p>
    </div>

    <form method="POST" class="space-y-4" novalidate>
      <div>
        <label class="block text-sm font-medium text-gray-700">Nama Lengkap *</label>
        <input type="text" name="nama" required class="w-full border rounded-lg px-3 py-2">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700">Username *</label>
          <input type="text" name="username" required class="w-full border rounded-lg px-3 py-2" placeholder="min. 3 karakter">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email *</label>
          <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2" placeholder="nama@contoh.com">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Password *</label>
        <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2" placeholder="min. 6 karakter">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700">Telepon</label>
          <input type="text" name="telp" class="w-full border rounded-lg px-3 py-2" placeholder="opsional">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Alamat</label>
          <input type="text" name="alamat" class="w-full border rounded-lg px-3 py-2" placeholder="opsional">
        </div>
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-semibold">
        Daftar
      </button>

      <p class="text-center text-sm text-gray-500 mt-2">
        Sudah punya akun? <a class="text-blue-600 hover:underline" href="<?= Helper::baseUrl('login.php')?>">Masuk</a>
      </p>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
