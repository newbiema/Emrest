<?php
require_once __DIR__ . '/services/Auth.php';
require_once __DIR__ . '/services/Alert.php';
require_once __DIR__ . '/services/Helper.php';
require_once __DIR__ . '/services/Database.php'; // ⬅️ tambahkan

$auth = new Auth();
$db   = (new Database())->connect(); // ⬅️ koneksi untuk cek status akun

// Jika sudah login, langsung lempar ke halaman sesuai role
if (!empty($_SESSION['logged_in'])) {
  $auth->redirectAfterLogin();
  exit;
}

// CSRF untuk form login
if (empty($_SESSION['csrf_login'])) {
  $_SESSION['csrf_login'] = bin2hex(random_bytes(16));
}

// Flags dari querystring
$expired    = (!empty($_GET['expired']) && $_GET['expired'] == '1') || (isset($_GET['reason']) && $_GET['reason'] === 'expired');
$mismatch   = !empty($_GET['mismatch']);
$expectQS   = $_GET['expect'] ?? '';
$actualQS   = $_GET['actual'] ?? '';
$credError  = !empty($_GET['cred']);        // username/password salah
$unverified = !empty($_GET['unverified']);  // akun belum verifikasi

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $csrf     = $_POST['csrf'] ?? '';
  $expected = strtolower(trim($_POST['role'] ?? ''));

  if (!hash_equals($_SESSION['csrf_login'], $csrf)) {
    Alert::toast('error', 'Sesi tidak valid. Coba lagi.', Helper::baseUrl('login.php'));
    exit;
  }

  if ($auth->login($username, $password)) {
    $actual = strtolower($_SESSION['level'] ?? '');

    if ($expected !== '' && $expected !== $actual) {
      // role mismatch: reset session manual (jangan panggil logout langsung)
      $_SESSION = [];
      if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
      }
      session_destroy();

      session_start();
      $_SESSION['csrf_login'] = bin2hex(random_bytes(16));

      $url = Helper::baseUrl('login.php?mismatch=1&expect=' . urlencode($expected) . '&actual=' . urlencode($actual));
      header('Location: ' . $url);
      exit;
    }

    unset($_SESSION['csrf_login']);
    $auth->redirectAfterLogin();
  } else {
    // ⬇️ LOGIN GAGAL → cek apakah akun ada tapi belum verifikasi
    $norm = mb_strtolower($username);
    $stmt = $db->prepare("SELECT level, verified FROM account WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $norm);
    $stmt->execute();
    $acc = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($acc && strtolower($acc['level']) === 'user' && (int)$acc['verified'] !== 1) {
      // akun pasien ada tapi belum verifikasi
      header('Location: ' . Helper::baseUrl('login.php?unverified=1'));
      exit;
    }

    // selain itu, tampilkan kredensial salah
    header('Location: ' . Helper::baseUrl('login.php?cred=1'));
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Masuk | RS Emrest</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .card { backdrop-filter: blur(6px); }
    .field:focus { outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,.25); border-color: #2563eb; }
    .btn-primary { background:#2563eb; }
    .btn-primary:hover { background:#1e4fd8; }
    .muted { color:#6b7280; }
    .brand { letter-spacing:.2px }
  </style>
</head>
<body class="min-h-screen bg-[conic-gradient(at_top_right,_#e0ecff,_#eef5ff_30%,_#ffffff_60%)]">

  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Brand header -->
      <div class="text-center mb-5">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-600/10 flex items-center justify-center">
          <i class="fa-solid fa-hospital text-blue-600 text-2xl"></i>
        </div>
        <h1 class="mt-3 text-2xl font-bold text-gray-800 brand">RS Emrest</h1>
        <p class="muted text-sm">Sistem Informasi Rekam Medis</p>
      </div>

      <!-- Card -->
      <div class="card bg-white/90 shadow-xl rounded-2xl p-6">
        <form method="POST" class="space-y-4">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_login']) ?>"/>

          <!-- Role -->
          <div>
            <label class="block text-gray-800 font-semibold mb-1">Masuk sebagai</label>
            <div class="relative">
              <i class="fa-solid fa-user-shield absolute left-3 top-3 text-gray-400"></i>
              <?php
                $selectedRole = '';
                if ($mismatch && !empty($expectQS)) $selectedRole = strtolower($expectQS);
              ?>
              <select name="role" required
                class="field w-full appearance-none border border-gray-300 rounded-lg pl-10 pr-10 py-2.5 bg-white text-gray-800">
                <option value="" <?= $selectedRole===''?'selected':''; ?>>— Pilih peran —</option>
                <option value="admin"  <?= $selectedRole==='admin'?'selected':''; ?>>Admin</option>
                <option value="dokter" <?= $selectedRole==='dokter'?'selected':''; ?>>Dokter</option>
                <option value="perawat"<?= $selectedRole==='perawat'?'selected':''; ?>>Perawat</option>
                <option value="apotek" <?= $selectedRole==='apotek'?'selected':''; ?>>Apotek</option>
                <option value="loket"  <?= $selectedRole==='loket'?'selected':''; ?>>Loket</option>
                <option value="user"   <?= $selectedRole==='user'?'selected':''; ?>>Pasien</option>
              </select>
              <i class="fa-solid fa-chevron-down absolute right-3 top-3.5 text-gray-400 pointer-events-none"></i>
            </div>
            <p class="muted text-xs mt-1">Pilih peran sesuai akun Anda.</p>
          </div>

          <!-- Username -->
          <div>
            <label class="block text-gray-800 font-semibold mb-1">Username</label>
            <div class="relative">
              <i class="fa-solid fa-user absolute left-3 top-3 text-gray-400"></i>
              <input type="text" name="username" required autocomplete="username"
                     class="field w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2.5 bg-white text-gray-800"
                     placeholder="contoh: admin">
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-gray-800 font-semibold mb-1">Password</label>
            <div class="relative">
              <i class="fa-solid fa-lock absolute left-3 top-3 text-gray-400"></i>
              <input id="pass" type="password" name="password" required autocomplete="current-password"
                     class="field w-full border border-gray-300 rounded-lg pl-10 pr-10 py-2.5 bg-white text-gray-800"
                     placeholder="••••••••">
              <button type="button" aria-label="toggle password"
                      class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                      onclick="const p=document.getElementById('pass'); const i=this.firstElementChild; p.type=p.type==='password'?'text':'password'; i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <!-- Tombol -->
          <button type="submit" class="btn-primary w-full text-white py-2.5 rounded-lg font-semibold shadow-sm">
            Masuk
          </button>

          <!-- Tombol register -->
          <p class="text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="<?= Helper::baseUrl('portal/register.php') ?>" class="text-blue-600 hover:underline font-semibold">
              Daftar Sekarang
            </a>
          </p>

          <!-- Footer -->
          <div class="text-center mt-3">
            <p class="muted text-xs">© <?= date('Y') ?> RS Emrest</p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    <?php if ($expired): ?>
      Swal.fire({
        icon:'info',
        title:'Sesi berakhir',
        text:'Anda otomatis keluar karena tidak aktif.',
        timer:3600,
        showConfirmButton:false
      });
    <?php endif; ?>

    <?php if ($mismatch):
      $expect = htmlspecialchars($expectQS);
      $actual = htmlspecialchars($actualQS);
    ?>
      Swal.fire({
        icon:'warning',
        title:'Role tidak sesuai',
        html:'Akun Anda berlevel <b><?= $actual ?></b>, bukan <b><?= $expect ?></b>.',
        confirmButtonText:'OK',
        confirmButtonColor:'#2563eb'
      });
    <?php endif; ?>

    <?php if ($credError): ?>
      Swal.fire({
        icon:'error',
        title:'Gagal Masuk',
        text:'Username atau password salah.',
        confirmButtonColor:'#2563eb'
      });
    <?php endif; ?>

    <?php if ($unverified): ?>
      Swal.fire({
        icon:'warning',
        title:'Akun belum diverifikasi',
        html:'Silakan cek email Anda untuk verifikasi. Jika tidak menerima, minta kirim ulang verifikasi.',
        confirmButtonColor:'#2563eb'
      });
    <?php endif; ?>
  </script>
</body>
</html>
