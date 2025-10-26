<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Sistem Rekam Medis</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to bottom right, #2563eb, #1e40af);
    }
    .login-container {
      backdrop-filter: blur(20px);
      background-color: rgba(255, 255, 255, 0.9);
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">

  <div class="login-container rounded-2xl shadow-2xl w-full max-w-md p-8">
    <div class="flex flex-col items-center mb-6">
      <div class="bg-blue-100 p-3 rounded-full mb-3">
        <i class="fa-solid fa-hospital-user text-blue-600 text-3xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-800 text-center">Sistem Rekam Medis</h1>
      <p class="text-gray-500 text-sm text-center">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    <form id="loginForm" action="login.php" method="POST" class="space-y-5">
      <div>
        <label class="block text-gray-700 mb-1 font-medium">
          <i class="fa-solid fa-user text-blue-500 mr-1"></i> Username
        </label>
        <input type="text" name="username" required
          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400 focus:outline-none">
      </div>

      <div>
        <label class="block text-gray-700 mb-1 font-medium">
          <i class="fa-solid fa-lock text-blue-500 mr-1"></i> Password
        </label>
        <input type="password" name="password" required
          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-400 focus:outline-none">
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
      </button>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">© 2025 Rumah Sakit Sehat Sentosa</p>
  </div>

  <script>
    // Saat form disubmit, tampilkan animasi SweetAlert
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      Swal.fire({
        title: 'Memeriksa data...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Submit form setelah animasi
      setTimeout(() => {
        form.submit();
      }, 1000);
    });

    // Jika URL mengandung ?error=true dari login.php
    const params = new URLSearchParams(window.location.search);
    if (params.get('error') === 'true') {
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal!',
        text: 'Username atau password salah. Silakan coba lagi.',
        confirmButtonColor: '#2563eb'
      });
    }
  </script>

</body>
</html>
