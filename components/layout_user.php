<?php
require_once __DIR__.'/../services/Helper.php';
if (session_status()!==PHP_SESSION_ACTIVE) session_start();
$userNama = $_SESSION['nama'] ?? 'Pasien';
$cur = $_SERVER['REQUEST_URI'] ?? '';
function navActive($needle,$cur){ return str_contains($cur,$needle) ? 'text-blue-600 font-semibold' : 'text-gray-700'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle ?? 'Portal Pasien') ?></title>

  <!-- UI assets (seragam dengan dashboard) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background:#f9fafb; }
    .safe-tap { min-height: 44px; }         /* tap target nyaman di HP */
    .container { max-width: 72rem; }        /* ~max-w-6xl */
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-white shadow sticky top-0 z-30">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <!-- Brand -->
      <a href="<?= Helper::baseUrl('portal/index.php') ?>" class="flex items-center gap-2 text-blue-700">
        <i class="fa-solid fa-heart-pulse text-2xl"></i>
        <span class="font-semibold text-lg">Xmo</span>
      </a>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-4">
        <a class="safe-tap flex items-center gap-2 <?= navActive('/portal/index.php',$cur) ?>"
           href="<?= Helper::baseUrl('portal/index.php') ?>">
           <i class="fa-solid fa-house"></i><span>Beranda</span>
        </a>
        <a class="safe-tap flex items-center gap-2 <?= navActive('/portal/daftar.php',$cur) ?>"
           href="<?= Helper::baseUrl('portal/daftar.php') ?>">
           <i class="fa-solid fa-notes-medical"></i><span>Daftar</span>
        </a>
        <a class="safe-tap flex items-center gap-2 <?= navActive('/portal/kunjungan',$cur) ?>"
           href="<?= Helper::baseUrl('portal/kunjungan.php') ?>">
           <i class="fa-solid fa-list-ol"></i><span>Kunjungan</span>
        </a>
        <a class="safe-tap flex items-center gap-2 <?= navActive('/portal/resep.php',$cur) ?>"
           href="<?= Helper::baseUrl('portal/resep.php') ?>">
           <i class="fa-solid fa-capsules"></i><span>Resep</span>
        </a>
        <a class="safe-tap flex items-center gap-2 <?= navActive('/portal/tagihan.php',$cur) ?>"
           href="<?= Helper::baseUrl('portal/tagihan.php') ?>">
           <i class="fa-solid fa-receipt"></i><span>Tagihan</span>
        </a>
        <a class="safe-tap flex items-center gap-2 <?= navActive('/portal/profil.php',$cur) ?>"
           href="<?= Helper::baseUrl('portal/profil.php') ?>">
           <i class="fa-solid fa-user"></i><span>Profil</span>
        </a>
      </nav>

      <!-- User & Logout (desktop) -->
      <div class="hidden md:flex items-center gap-3">
        <div class="text-right leading-4">
          <div class="text-sm font-semibold text-blue-700 truncate max-w-[160px]"><?= htmlspecialchars($userNama) ?></div>
          <div class="text-[11px] text-gray-400">Pasien</div>
        </div>
        <a href="<?= Helper::baseUrl('logout.php') ?>" class="safe-tap text-red-600 hover:text-red-700" title="Keluar" aria-label="Keluar">
          <i class="fa-solid fa-right-from-bracket text-lg"></i>
        </a>
      </div>

      <!-- Mobile hamburger -->
      <button id="btnNav" class="md:hidden safe-tap p-2 rounded hover:bg-gray-100" aria-label="Menu">
        <i class="fa-solid fa-bars text-xl"></i>
      </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobileNav" class="md:hidden hidden border-t bg-white">
      <div class="px-4 py-3 flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-blue-700"><?= htmlspecialchars($userNama) ?></div>
          <div class="text-xs text-gray-400">Pasien</div>
        </div>
        <a href="<?= Helper::baseUrl('logout.php') ?>" class="safe-tap text-red-600 hover:text-red-700 flex items-center gap-2">
          <i class="fa-solid fa-right-from-bracket"></i><span>Keluar</span>
        </a>
      </div>
      <nav class="px-2 py-2 grid gap-1">
        <a class="safe-tap px-3 py-2 rounded flex items-center gap-2 <?= navActive('/portal/index.php',$cur) ?>" href="<?= Helper::baseUrl('portal/index.php') ?>">
          <i class="fa-solid fa-house w-5 text-center"></i><span>Beranda</span>
        </a>
        <a class="safe-tap px-3 py-2 rounded flex items-center gap-2 <?= navActive('/portal/daftar.php',$cur) ?>" href="<?= Helper::baseUrl('portal/daftar.php') ?>">
          <i class="fa-solid fa-notes-medical w-5 text-center"></i><span>Daftar Periksa</span>
        </a>
        <a class="safe-tap px-3 py-2 rounded flex items-center gap-2 <?= navActive('/portal/kunjungan',$cur) ?>" href="<?= Helper::baseUrl('portal/kunjungan.php') ?>">
          <i class="fa-solid fa-list-ol w-5 text-center"></i><span>Kunjungan</span>
        </a>
        <a class="safe-tap px-3 py-2 rounded flex items-center gap-2 <?= navActive('/portal/resep.php',$cur) ?>" href="<?= Helper::baseUrl('portal/resep.php') ?>">
          <i class="fa-solid fa-capsules w-5 text-center"></i><span>Resep</span>
        </a>
        <a class="safe-tap px-3 py-2 rounded flex items-center gap-2 <?= navActive('/portal/tagihan.php',$cur) ?>" href="<?= Helper::baseUrl('portal/tagihan.php') ?>">
          <i class="fa-solid fa-receipt w-5 text-center"></i><span>Tagihan</span>
        </a>
        <a class="safe-tap px-3 py-2 rounded flex items-center gap-2 <?= navActive('/portal/profil.php',$cur) ?>" href="<?= Helper::baseUrl('portal/profil.php') ?>">
          <i class="fa-solid fa-user w-5 text-center"></i><span>Profil</span>
        </a>
      </nav>
    </div>
  </header>

  <!-- Content -->
  <main class="flex-1 container mx-auto w-full px-4 sm:px-6 py-6">
    <?php include $contentFile; ?>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-t">
    <div class="container mx-auto px-4 py-4 text-center text-sm text-gray-500">
      © <?= date('Y') ?> Xmo. Semua hak dilindungi.
    </div>
  </footer>

  <!-- Mobile menu toggle -->
  <script>
    const btn = document.getElementById('btnNav');
    const nav = document.getElementById('mobileNav');
    btn?.addEventListener('click', () => nav.classList.toggle('hidden'));
    // optional: tutup menu saat pindah halaman (history)
    window.addEventListener('pageshow', ()=> nav.classList.add('hidden'));
  </script>
</body>
</html>
