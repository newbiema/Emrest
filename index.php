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

<div class="flex justify-between items-center mb-8">
  <h1 class="text-3xl font-bold text-gray-800">
    <i class="fa-solid fa-gauge-high text-blue-600 mr-2"></i>Dashboard
  </h1>
  <div class="text-right">
    <p class="text-gray-600 text-sm">Selamat datang,</p>
    <p class="font-semibold text-blue-700"><?= $_SESSION['nama']; ?></p>
  </div>
</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
  <div class="bg-white shadow-md rounded-xl p-6 flex items-center space-x-4 border-l-4 border-blue-600">
    <i class="fa-solid fa-users text-blue-600 text-3xl"></i>
    <div>
      <h3 class="text-gray-500 text-sm">Total Pasien</h3>
      <p class="text-2xl font-semibold text-gray-800"><?= $pasien; ?></p>
    </div>
  </div>

  <div class="bg-white shadow-md rounded-xl p-6 flex items-center space-x-4 border-l-4 border-green-600">
    <i class="fa-solid fa-user-doctor text-green-600 text-3xl"></i>
    <div>
      <h3 class="text-gray-500 text-sm">Total Dokter</h3>
      <p class="text-2xl font-semibold text-gray-800"><?= $dokter; ?></p>
    </div>
  </div>

  <div class="bg-white shadow-md rounded-xl p-6 flex items-center space-x-4 border-l-4 border-yellow-500">
    <i class="fa-solid fa-capsules text-yellow-500 text-3xl"></i>
    <div>
      <h3 class="text-gray-500 text-sm">Total Obat</h3>
      <p class="text-2xl font-semibold text-gray-800"><?= $obat; ?></p>
    </div>
  </div>

  <div class="bg-white shadow-md rounded-xl p-6 flex items-center space-x-4 border-l-4 border-red-600">
    <i class="fa-solid fa-notes-medical text-red-600 text-3xl"></i>
    <div>
      <h3 class="text-gray-500 text-sm">Total Rekam Medis</h3>
      <p class="text-2xl font-semibold text-gray-800"><?= $rekam; ?></p>
    </div>
  </div>
</div>

<!-- Welcome Message -->
<div class="mt-12 bg-white rounded-xl shadow-md p-8 text-center">
  <i class="fa-solid fa-heart-pulse text-4xl text-blue-600 mb-4"></i>
  <h2 class="text-2xl font-bold text-gray-800 mb-2">
    Selamat Datang di Sistem Rekam Medis Rumah Sakit
  </h2>
  <p class="text-gray-500">
    Kelola data pasien, dokter, obat, dan rekam medis dengan mudah dan aman.
  </p>
</div>

<?php
// Simpan konten ke file sementara
$contentFile = tempnam(sys_get_temp_dir(), 'content');
file_put_contents($contentFile, ob_get_clean());

// Gunakan layout utama
include_once __DIR__ . '/components/layout.php';
