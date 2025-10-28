<?php
// login.php
require_once __DIR__ . '/services/Auth.php';
require_once __DIR__ . '/services/Alert.php';
require_once __DIR__ . '/services/Helper.php';

$auth = new Auth();

// Jika sudah login, langsung lempar ke dashboard/halaman role
if (!empty($_SESSION['logged_in'])) {
  $auth->redirectAfterLogin();
  exit;
}

// Siapkan CSRF token (one-time)
if (empty($_SESSION['csrf_login'])) {
  $_SESSION['csrf_login'] = bin2hex(random_bytes(16));
}

// Handle POST (submit form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $csrf     = $_POST['csrf'] ?? '';

  // Validasi CSRF
  if (!hash_equals($_SESSION['csrf_login'], $csrf)) {
    Alert::toast('error', 'Sesi tidak valid. Coba lagi.', Helper::baseUrl('login.php'));
    exit;
  }

  if ($auth->login($username, $password)) {
    unset($_SESSION['csrf_login']); // gunakan sekali
    $auth->redirectAfterLogin();
  } else {
    Alert::toast('error', 'Username atau password salah.', Helper::baseUrl('login.php'));
  }
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Sistem Rekam Medis</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root { color-scheme: light; }
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-600 to-indigo-700 min-h-screen flex items-center justify-center p-4">

  <div class="bg-white/95 backdrop-blur shadow-xl rounded-2xl w-full max-w-md p-8">
    <div class="text-center mb-6">
      <div class="mx-auto w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
        <i class="fa-solid fa-hospital text-blue-600 text-2xl"></i>
      </div>
      <h1 class="mt-4 text-2xl font-bold text-gray-800">Sistem Rekam Medis</h1>
      <p class="text-gray-500 text-sm">Masuk untuk melanjutkan</p>
    </div>

    <form method="POST" class="space-y-5">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_login']) ?>"/>

      <div>
        <label class="block text-gray-700 mb-1">Username</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-gray-400">
            <i class="fa-solid fa-user"></i>
          </span>
          <input type="text" name="username" required autocomplete="username"
                 class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                 placeholder="contoh: admin"/>
        </div>
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Password</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-gray-400">
            <i class="fa-solid fa-lock"></i>
          </span>
          <input type="password" name="password" required autocomplete="current-password"
                 class="w-full border border-gray-300 rounded-lg pl-9 pr-10 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                 placeholder="••••••••"/>
          <button type="button" aria-label="toggle"
                  class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                  onclick="const i=this.previousElementSibling; i.type=i.type==='password'?'text':'password'; this.firstElementChild.classList.toggle('fa-eye'); this.firstElementChild.classList.toggle('fa-eye-slash');">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-semibold transition">
        Masuk
      </button>
    </form>

    <p class="text-center text-gray-400 text-xs mt-6">© <?= date('Y') ?> RS Emrest</p>
  </div>

  <!-- SweetAlert untuk Alert::toast() -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
