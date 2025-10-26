<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page, $currentPage) {
  return $page === $currentPage ? 'bg-blue-700' : 'hover:bg-blue-600';
}
?>

<aside class="sidebar w-64 bg-gradient-to-b from-blue-700 to-blue-900 text-white flex flex-col">
  <div class="p-6 border-b border-blue-500 text-center">
    <h2 class="text-2xl font-bold flex items-center justify-center gap-2">
      <i class="fa-solid fa-hospital-user"></i> Rekam Medis
    </h2>
    <p class="text-sm text-blue-200 mt-1">
      <?= $_SESSION['nama'] ?? 'User'; ?> (<?= $_SESSION['level'] ?? 'Guest'; ?>)
    </p>
  </div>

  <nav class="flex-1 p-4 space-y-2">
    <a href="#" class="flex items-center p-2 rounded-lg <?= isActive('index.php', $currentPage) ?> transition">
      <i class="fa-solid fa-gauge mr-3"></i> Dashboard
    </a>
    <a href="/patients/data_pasien.php" class="flex items-center p-2 rounded-lg <?= isActive('data_pasien.php', $currentPage) ?> transition">
      <i class="fa-solid fa-users mr-3"></i> Data Pasien
    </a>
    <a href="/data_dokter.php" class="flex items-center p-2 rounded-lg <?= isActive('data_dokter.php', $currentPage) ?> transition">
      <i class="fa-solid fa-user-doctor mr-3"></i> Data Dokter
    </a>
    <a href="/data_obat.php" class="flex items-center p-2 rounded-lg <?= isActive('data_obat.php', $currentPage) ?> transition">
      <i class="fa-solid fa-capsules mr-3"></i> Data Obat
    </a>
    <a href="/rekam_medis/" class="flex items-center p-2 rounded-lg hover:bg-blue-600 transition">
      <i class="fa-solid fa-file-medical mr-3"></i> Rekam Medis
    </a>
  </nav>

  <a href="/logout.php" class="block mt-auto p-4 text-center bg-blue-800 hover:bg-blue-950 transition">
    <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
  </a>
</aside>
