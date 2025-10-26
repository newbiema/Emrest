<?php
require_once __DIR__ . '/services/Auth.php';
require_once __DIR__ . '/services/Database.php';

$auth = new Auth();
$auth->checkLogin();

$db = (new Database())->connect();

// Ambil jumlah data dari tabel
$pasien = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM pasien"))['total'];
$dokter = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM dokter"))['total'];
$obat   = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM obat"))['total'];
$rekam  = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM rekam_medis"))['total'];

// Judul halaman
$pageTitle = "Dashboard Rekam Medis";

// Konten utama (akan dimasukkan ke layout)
ob_start();
?>

<!-- Header -->
<div class="flex justify-between items-center mb-10">
  <div>
    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-gauge-high text-blue-600"></i>
      Dashboard
    </h1>
    <p class="text-gray-500 text-sm mt-1">Selamat datang di sistem informasi rekam medis</p>
  </div>
  <div class="bg-blue-50 border border-blue-200 px-4 py-2 rounded-xl text-right">
    <p class="text-gray-600 text-sm">Login sebagai:</p>
    <p class="font-semibold text-blue-700"><?= htmlspecialchars($_SESSION['nama']); ?> (<?= htmlspecialchars($_SESSION['level']); ?>)</p>
  </div>
</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
  <div class="bg-white shadow-sm hover:shadow-md transition-all rounded-xl p-6 border-t-4 border-blue-600">
    <div class="flex items-center space-x-4">
      <div class="bg-blue-100 p-3 rounded-lg">
        <i class="fa-solid fa-users text-blue-600 text-2xl"></i>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Total Pasien</p>
        <p class="text-2xl font-bold text-gray-800"><?= $pasien; ?></p>
      </div>
    </div>
  </div>

  <div class="bg-white shadow-sm hover:shadow-md transition-all rounded-xl p-6 border-t-4 border-green-600">
    <div class="flex items-center space-x-4">
      <div class="bg-green-100 p-3 rounded-lg">
        <i class="fa-solid fa-user-doctor text-green-600 text-2xl"></i>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Total Dokter</p>
        <p class="text-2xl font-bold text-gray-800"><?= $dokter; ?></p>
      </div>
    </div>
  </div>

  <div class="bg-white shadow-sm hover:shadow-md transition-all rounded-xl p-6 border-t-4 border-yellow-500">
    <div class="flex items-center space-x-4">
      <div class="bg-yellow-100 p-3 rounded-lg">
        <i class="fa-solid fa-capsules text-yellow-500 text-2xl"></i>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Total Obat</p>
        <p class="text-2xl font-bold text-gray-800"><?= $obat; ?></p>
      </div>
    </div>
  </div>

  <div class="bg-white shadow-sm hover:shadow-md transition-all rounded-xl p-6 border-t-4 border-red-600">
    <div class="flex items-center space-x-4">
      <div class="bg-red-100 p-3 rounded-lg">
        <i class="fa-solid fa-file-medical text-red-600 text-2xl"></i>
      </div>
      <div>
        <p class="text-gray-500 text-sm">Total Rekam Medis</p>
        <p class="text-2xl font-bold text-gray-800"><?= $rekam; ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Welcome Section -->
<div class="mt-12 bg-white rounded-xl shadow-sm p-8 text-center border border-gray-100">
  <i class="fa-solid fa-heart-pulse text-4xl text-blue-600 mb-4 animate-pulse"></i>
  <h2 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang di Sistem Rekam Medis</h2>
  <p class="text-gray-500 leading-relaxed max-w-2xl mx-auto">
    Aplikasi ini membantu tenaga medis dan staf rumah sakit untuk mengelola data pasien, dokter, obat, serta riwayat rekam medis dengan lebih efisien dan aman.
  </p>
</div>

<?php
// Simpan konten ke file sementara
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());

// Gunakan layout utama
include_once __DIR__ . '/components/layout.php';
