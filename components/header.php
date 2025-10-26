<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<header class="flex justify-between items-center px-6 py-4 bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
  <div class="flex items-center gap-3">
    <h1 class="text-xl font-semibold text-gray-700">
      <i class="fa-solid fa-stethoscope text-blue-600 mr-2"></i>
      <?= htmlspecialchars($pageTitle ?? 'Rekam Medis'); ?>
    </h1>
  </div>

  <div class="flex items-center gap-3">
    <span class="text-gray-600 text-sm"><?= $_SESSION['nama'] ?? 'User'; ?></span>
    <div class="bg-blue-600 text-white w-10 h-10 flex items-center justify-center rounded-full">
      <i class="fa-solid fa-user"></i>
    </div>
  </div>
</header>
