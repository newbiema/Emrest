<?php
require_once __DIR__ . '/services/Auth.php';
require_once __DIR__ . '/services/Helper.php';

$auth = new Auth();

// kalau belum login langsung ke login page
if (empty($_SESSION['logged_in'])) {
  header('Location: ' . Helper::baseUrl('login.php'));
  exit;
}

// siapkan CSRF token untuk aksi logout
if (empty($_SESSION['csrf_logout'])) {
  $_SESSION['csrf_logout'] = bin2hex(random_bytes(16));
}

$token = $_SESSION['csrf_logout'];

// jika diminta eksekusi logout (via query ?do=token)
if (isset($_GET['do']) && hash_equals($_SESSION['csrf_logout'], $_GET['do'])) {
  unset($_SESSION['csrf_logout']);
  $auth->logout(); // ini akan destroy session dan redirect ke login.php
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keluar | RS Emrest</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center min-h-screen">

  <script>
    // SweetAlert konfirmasi logout
    Swal.fire({
      title: 'Yakin ingin keluar?',
      text: "Sesi Anda akan diakhiri.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, keluar',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        // Arahkan ke endpoint yang benar-benar memproses logout server-side
        window.location.href = '<?= Helper::baseUrl('logout.php?do=' . $token) ?>';
      } else {
        window.location.href = '<?= Helper::baseUrl('index.php') ?>';
      }
    });
  </script>

  <!-- Fallback kalau JS mati -->
  <noscript>
    <div class="bg-white rounded-xl p-6 text-center">
      <p class="mb-4">JavaScript nonaktif. Klik tombol di bawah untuk logout.</p>
      <a class="px-4 py-2 bg-blue-600 text-white rounded"
         href="<?= Helper::baseUrl('logout.php?do=' . $token) ?>">Logout</a>
      <a class="px-4 py-2 bg-gray-300 text-gray-800 rounded ml-2"
         href="<?= Helper::baseUrl('index.php') ?>">Batal</a>
    </div>
  </noscript>

</body>
</html>
