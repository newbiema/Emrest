<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$pageTitle = $pageTitle ?? "Sistem Rekam Medis";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle); ?></title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
    }
    .sidebar {
      background: linear-gradient(to bottom, #2563eb, #1e40af);
      transition: transform 0.3s ease-in-out;
    }
    .sidebar-hidden {
      transform: translateX(-100%);
    }
    .overlay {
      background-color: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(2px);
    }
  </style>
</head>
<body class="flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <div id="sidebar" class="sidebar fixed md:relative top-0 left-0 w-64 h-full text-white flex flex-col z-40">
    <div class="p-6 border-b border-blue-500 text-center">
      <h2 class="text-2xl font-bold flex items-center justify-center gap-2">
        <i class="fa-solid fa-hospital-user"></i> Rekam Medis
      </h2>
      <p class="text-sm text-blue-200 mt-1">
        <?= $_SESSION['nama'] ?? 'User'; ?> (<?= $_SESSION['level'] ?? 'Guest'; ?>)
      </p>
    </div>

    <nav class="flex-1 p-4 space-y-2">
      <a href="/index.php" class="flex items-center p-2 rounded-lg hover:bg-blue-600 transition">
        <i class="fa-solid fa-gauge mr-3"></i> Dashboard
      </a>
      <a href="../patients/data_pasien.php" class="flex items-center p-2 rounded-lg hover:bg-blue-600 transition">
        <i class="fa-solid fa-users mr-3"></i> Data Pasien
      </a>
      <a href="/data_dokter.php" class="flex items-center p-2 rounded-lg hover:bg-blue-600 transition">
        <i class="fa-solid fa-user-doctor mr-3"></i> Data Dokter
      </a>
      <a href="/data_obat.php" class="flex items-center p-2 rounded-lg hover:bg-blue-600 transition">
        <i class="fa-solid fa-capsules mr-3"></i> Data Obat
      </a>
      <a href="/rekam_medis/" class="flex items-center p-2 rounded-lg hover:bg-blue-600 transition">
        <i class="fa-solid fa-file-medical mr-3"></i> Rekam Medis
      </a>
    </nav>

    <a href="/logout.php" class="block mt-auto p-4 text-center bg-blue-800 hover:bg-blue-950 transition">
      <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
    </a>
  </div>

  <!-- Overlay (muncul hanya di mobile saat sidebar aktif) -->
  <div id="overlay" class="hidden fixed inset-0 z-30 overlay"></div>

  <!-- Main Section -->
  <div class="flex-1 flex flex-col min-h-screen overflow-y-auto">
    <!-- Header -->
    <header class="flex justify-between items-center px-6 py-4 bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <!-- Tombol Toggle Sidebar (mobile) -->
        <button id="menuButton" class="md:hidden text-blue-600 text-2xl focus:outline-none">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="text-xl font-semibold text-gray-700">
          <i class="fa-solid fa-stethoscope text-blue-600 mr-2"></i>
          <?= htmlspecialchars($pageTitle); ?>
        </h1>
      </div>

      <div class="flex items-center gap-3">
        <span class="text-gray-600 text-sm"><?= $_SESSION['nama'] ?? 'User'; ?></span>
        <div class="bg-blue-600 text-white w-10 h-10 flex items-center justify-center rounded-full">
          <i class="fa-solid fa-user"></i>
        </div>
      </div>
    </header>

    <!-- Konten -->
    <main class="flex-1 p-6">
      <?php
        if (isset($contentFile) && file_exists($contentFile)) {
          include $contentFile;
        } else {
          echo "<div class='text-gray-500 text-center mt-20'>
                  <i class='fa-solid fa-circle-exclamation text-3xl text-blue-500 mb-3'></i>
                  <p>Konten tidak ditemukan.</p>
                </div>";
        }
      ?>
    </main>

    <!-- Footer -->
    <footer class="text-center py-4 text-gray-500 text-sm border-t border-gray-200 mt-auto">
      © <?= date('Y'); ?> Rumah Sakit Sehat Sentosa. All Rights Reserved.
    </footer>
  </div>

  <!-- Script Toggle Sidebar -->
  <script>
    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    menuButton.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-hidden');
      overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.add('sidebar-hidden');
      overlay.classList.add('hidden');
    });

    // Secara default, sidebar disembunyikan di layar kecil
    if (window.innerWidth < 768) {
      sidebar.classList.add('sidebar-hidden');
    }
  </script>
</body>
</html>
